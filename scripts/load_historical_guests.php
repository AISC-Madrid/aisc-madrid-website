<?php
/**
 * Carga los guests históricos (personas externas de eventos pasados) como inactivos (#337). Re-ejecutable.
 *
 * Los datos de contacto NO están en el repo (es público): van en un CSV aparte, p. ej.
 * scripts/historical_guests.csv (ignorado por git). Formato en scripts/historical_guests.example.csv:
 *   username,name,email,phone,linkedin_url,event_ids,previous_username
 * - event_ids: ids separados por ';'. Los que no existan en la BD se avisan y se ignoran.
 * - previous_username: cuenta existente que pasa a ser de esta persona (separar una cuenta compartida
 *   o renombrar). Conserva su id y sus eventos, y recibe contraseña nueva.
 *
 * Un guest nuevo o renombrado queda inactivo y con una contraseña aleatoria que no se muestra.
 * En re-ejecuciones no se tocan la contraseña ni is_active, y un campo vacío no borra el dato guardado.
 * Todo va en una transacción: si una fila es inválida no se escribe nada.
 *
 * Uso (dentro del contenedor de la app, con config.php apuntando a la BD deseada):
 *   php scripts/load_historical_guests.php /ruta/historical_guests.csv
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("CLI only.\n");
}

require_once __DIR__ . '/../assets/guest_contact.php';

const HISTORICAL_GUEST_COLUMNS = ['username', 'name', 'email', 'phone', 'linkedin_url', 'event_ids', 'previous_username'];

/** Lee y valida el CSV. Devuelve [filas, errores]. */
function historical_guests_read(string $path): array
{
    $handle = @fopen($path, 'r');
    if ($handle === false) {
        return [[], ["No puedo abrir $path"]];
    }
    $header = fgetcsv($handle, null, ',', '"', '');
    if ($header !== HISTORICAL_GUEST_COLUMNS) {
        fclose($handle);
        return [[], ["Cabecera inesperada; debe ser: " . implode(',', HISTORICAL_GUEST_COLUMNS)]];
    }

    $rows = [];
    $errors = [];
    $line = 1;
    while (($fields = fgetcsv($handle, null, ',', '"', '')) !== false) {
        $line++;
        if ($fields === [null] || $fields === ['']) {
            continue;
        }
        if (count($fields) !== count(HISTORICAL_GUEST_COLUMNS)) {
            $errors[] = "Línea $line: número de columnas incorrecto";
            continue;
        }
        $row = array_combine(HISTORICAL_GUEST_COLUMNS, array_map('trim', $fields));
        $contact = guest_contact_from_input($row);

        if (!preg_match('/^[a-z0-9_]+$/', $row['username'])) {
            $errors[] = "Línea $line: username inválido ({$row['username']})";
        } elseif ($row['name'] === '') {
            $errors[] = "Línea $line: falta el nombre";
        } elseif ($contact['error'] !== null) {
            $errors[] = "Línea $line ({$row['username']}): {$contact['error']}";
        } elseif (!preg_match('/^\d+(;\d+)*$/', $row['event_ids'])) {
            $errors[] = "Línea $line ({$row['username']}): event_ids debe ser una lista de ids separados por ';'";
        } else {
            $rows[] = [
                'username' => $row['username'],
                'name' => $row['name'],
                'email' => $contact['email'],
                'phone' => $contact['phone'],
                'linkedin_url' => $contact['linkedin_url'],
                'event_ids' => array_map('intval', explode(';', $row['event_ids'])),
                'previous_username' => $row['previous_username'] === '' ? null : $row['previous_username'],
            ];
        }
    }
    fclose($handle);

    $usernames = array_column($rows, 'username');
    foreach (array_unique(array_diff_assoc($usernames, array_unique($usernames))) as $dup) {
        $errors[] = "Username repetido en el CSV: $dup";
    }
    return [$rows, $errors];
}

function guest_id_by_username(mysqli $conn, string $username): ?int
{
    $stmt = $conn->prepare("SELECT id FROM guests WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ? (int) $row['id'] : null;
}

function random_password_hash(): string
{
    return password_hash(bin2hex(random_bytes(24)), PASSWORD_DEFAULT);
}

/** Crea, renombra o actualiza el guest de una fila y lo asocia a sus eventos. Devuelve líneas de log. */
function historical_guest_upsert(mysqli $conn, array $row): array
{
    $log = [];
    $id = guest_id_by_username($conn, $row['username']);
    $previous_id = $row['previous_username'] !== null ? guest_id_by_username($conn, $row['previous_username']) : null;

    if ($id === null && $previous_id !== null) {
        $hash = random_password_hash();
        $stmt = $conn->prepare("UPDATE guests SET username = ?, password_hash = ?, is_active = 0 WHERE id = ?");
        $stmt->bind_param("ssi", $row['username'], $hash, $previous_id);
        $stmt->execute();
        $stmt->close();
        $id = $previous_id;
        $log[] = "renombrado {$row['previous_username']} -> {$row['username']}";
    } elseif ($id === null) {
        $hash = random_password_hash();
        $stmt = $conn->prepare("INSERT INTO guests (username, password_hash, name, is_active) VALUES (?, ?, ?, 0)");
        $stmt->bind_param("sss", $row['username'], $hash, $row['name']);
        $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();
        $log[] = "creado {$row['username']}";
    } else {
        $log[] = "actualizado {$row['username']}";
        if ($previous_id !== null) {
            $log[] = "  aviso: {$row['previous_username']} sigue existiendo y no se toca, porque {$row['username']} ya existe";
        }
    }

    // Un campo vacío en el CSV no borra lo que ya haya (p. ej. editado a mano desde el dashboard)
    $stmt = $conn->prepare(
        "UPDATE guests SET name = ?, email = COALESCE(?, email), phone = COALESCE(?, phone),
         linkedin_url = COALESCE(?, linkedin_url) WHERE id = ?"
    );
    $stmt->bind_param("ssssi", $row['name'], $row['email'], $row['phone'], $row['linkedin_url'], $id);
    $stmt->execute();
    $stmt->close();

    $link = $conn->prepare("INSERT IGNORE INTO event_guest_access (guest_id, event_id) SELECT ?, id FROM events WHERE id = ?");
    foreach ($row['event_ids'] as $event_id) {
        $link->bind_param("ii", $id, $event_id);
        $link->execute();
        // INSERT IGNORE ... SELECT no inserta nada si el evento no existe; distinguirlo de "ya asociado"
        if ($link->affected_rows === 0 && $conn->query("SELECT 1 FROM events WHERE id = " . (int) $event_id)->num_rows === 0) {
            $log[] = "  aviso: el evento $event_id no existe en esta BD; no se asocia";
        }
    }
    $link->close();
    return $log;
}

$path = $argv[1] ?? null;
if ($path === null) {
    fwrite(STDERR, "Uso: php scripts/load_historical_guests.php <csv>\n");
    exit(2);
}

[$rows, $errors] = historical_guests_read($path);
if ($errors) {
    fwrite(STDERR, implode("\n", $errors) . "\nNo se ha escrito nada.\n");
    exit(1);
}

require_once __DIR__ . '/../assets/db.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$conn->begin_transaction();
try {
    foreach ($rows as $row) {
        echo implode("\n", historical_guest_upsert($conn, $row)), "\n";
    }
    $conn->commit();
} catch (Throwable $e) {
    $conn->rollback();
    fwrite(STDERR, "Error: {$e->getMessage()}\nNo se ha escrito nada.\n");
    exit(1);
}
echo count($rows) . " guests procesados.\n";
