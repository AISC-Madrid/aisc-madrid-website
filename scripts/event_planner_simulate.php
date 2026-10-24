<?php
/**
 * Simulación end-to-end del planificador (criterio de aceptación de la Fase 1).
 *
 * Crea un plan de prueba a 40 días vista con 5 miembros TEMPORALES (se borran al final,
 * junto con el plan), "avanza el reloj" día a día ejecutando recomputePlan + las
 * transiciones del cron, completando/saltando tareas según un guion, y comprueba:
 *   - qué tareas se activan cada día y qué correos se encolan
 *   - que no se duplica ningún correo (dedupe_key único)
 *   - que una tarea de fecha fija se activa con aviso si sus dependencias están pendientes
 *   - que el plan pasa por draft -> confirmed -> held -> closed
 *   - que cambiar la fecha a mitad recalcula tareas y avisa
 *
 * SOLO para una base de datos de desarrollo/pruebas: toca `members`, `event_plans` y
 * `email_outbox` de verdad (aunque limpia lo que crea). Por eso exige una confirmación
 * explícita.
 *
 * Uso (PowerShell, desde la raíz del proyecto, contra tu BD de desarrollo):
 *   $env:EVENT_PLANNER_SIMULATE_CONFIRM = "1"
 *   php scripts/event_planner_simulate.php
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("CLI only.\n");
}

if (getenv('EVENT_PLANNER_SIMULATE_CONFIRM') !== '1') {
    fwrite(STDERR, "Este script crea y borra datos reales (members, event_plans, email_outbox).\n");
    fwrite(STDERR, "Ejecútalo solo contra una base de datos de desarrollo/pruebas y confírmalo con:\n");
    fwrite(STDERR, "  EVENT_PLANNER_SIMULATE_CONFIRM=1 php scripts/event_planner_simulate.php\n");
    exit(1);
}

putenv('EVENT_PLANNER_ALLOW_CLOCK_OVERRIDE=1');

require_once __DIR__ . '/../event_planner/lib/clock.php';
require_once __DIR__ . '/../event_planner/lib/plan_service.php';
require_once __DIR__ . '/../event_planner/lib/mailer.php';
$config = include __DIR__ . '/../config.php';
require_once __DIR__ . '/../assets/db.php';

function sim_set_now(string $date): void
{
    putenv('EVENT_PLANNER_NOW=' . $date . ' 09:05:00');
}

function sim_create_member(mysqli $conn, string $label, string $role): int
{
    $mail = 'simulacion+' . $label . '-' . bin2hex(random_bytes(3)) . '@aiscmadrid.test';
    $hash = password_hash('simulacion', PASSWORD_DEFAULT);
    $fullName = 'Simulación ' . ucfirst($label);
    $stmt = $conn->prepare(
        "INSERT INTO members (full_name, mail, password_hash, position_es, position_en, phone, dni, socials, board, active, image_path, role, is_honor, graduation_year, honor_quote)
         VALUES (?, ?, ?, '', '', '', '', '', 'no', 'yes', '', ?, 'no', NULL, NULL)"
    );
    $stmt->bind_param('ssss', $fullName, $mail, $hash, $role);
    $stmt->execute();
    $id = (int) $conn->insert_id;
    $stmt->close();
    return $id;
}

function sim_task_id(mysqli $conn, int $planId, string $key): ?int
{
    $stmt = $conn->prepare("SELECT id FROM event_tasks WHERE event_plan_id = ? AND `key` = ?");
    $stmt->bind_param('is', $planId, $key);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ? (int) $row['id'] : null;
}

function sim_task_status(mysqli $conn, int $taskId): string
{
    $stmt = $conn->prepare("SELECT status FROM event_tasks WHERE id = ?");
    $stmt->bind_param('i', $taskId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['status'];
}

function sim_print_new_outbox(mysqli $conn, array $memberIds, int &$lastSeenId): void
{
    $placeholders = implode(',', array_fill(0, count($memberIds), '?'));
    $stmt = $conn->prepare("SELECT id, kind, subject, to_email, dedupe_key FROM email_outbox WHERE id > ? AND to_member_id IN ($placeholders) ORDER BY id");
    $stmt->bind_param('i' . str_repeat('i', count($memberIds)), $lastSeenId, ...$memberIds);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    foreach ($rows as $row) {
        echo "      [correo] {$row['kind']} -> {$row['to_email']}: {$row['subject']}\n";
        $lastSeenId = max($lastSeenId, (int) $row['id']);
    }
}

echo "== Simulación del planificador de eventos ==\n";

$conn->begin_transaction();
$conn->commit(); // sanity check de la conexión antes de crear datos reales

$members = [
    'EV_CONTACTO' => sim_create_member($conn, 'ev1', 'events'),
    'EV_APOYO' => sim_create_member($conn, 'ev2', 'events'),
    'REDES_1' => sim_create_member($conn, 'rd1', 'marketing'),
    'REDES_2' => sim_create_member($conn, 'rd2', 'marketing'),
    'WEB' => sim_create_member($conn, 'web', 'web'),
];
// El creador del plan simula ser de la junta (board='yes').
$boardMemberId = sim_create_member($conn, 'junta', 'admin');
$conn->query("UPDATE members SET board = 'yes' WHERE id = {$boardMemberId}");

$allMemberIds = array_merge(array_values($members), [$boardMemberId]);
$lastSeenOutboxId = 0;

try {
    $eventStart = (new DateTimeImmutable('+40 days', event_planner_timezone()))->setTime(17, 0, 0);
    $eventStartUtc = $eventStart->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');

    sim_set_now($eventStart->modify('-40 days')->format('Y-m-d'));

    $planId = event_planner_create_plan(
        $conn, $config,
        [
            'title' => 'Charla de simulación', 'description' => 'Plan de prueba generado por event_planner_simulate.php',
            'speaker_name' => 'Ponente de Prueba', 'speaker_company' => 'ACME', 'speaker_contact' => 'ponente@example.com',
            'starts_at' => $eventStartUtc, 'duration_minutes' => 90, 'location' => null, 'drive_folder_url' => null,
        ],
        $members,
        array_column(
            $conn->query("SELECT `key` FROM task_templates WHERE is_active = 1")->fetch_all(MYSQLI_ASSOC),
            'key'
        ),
        [],
        [],
        $boardMemberId
    );

    echo "Plan creado: id={$planId}, evento el {$eventStart->format('d/m/Y H:i')} (Madrid)\n";
    sim_print_new_outbox($conn, $allMemberIds, $lastSeenOutboxId);

    // Guion: offset en días respecto al evento -> acciones a ejecutar ese día.
    // 'done' completa (y recomputa); 'skip' marca No aplica; 'date_change' desplaza el evento.
    $schedule = [
        -35 => [['done', 'ev_contacto_ponente']],
        -30 => [['done', 'ev_videollamada']],
        -28 => [['done', 'ev_carpeta_drive']],
        -25 => [['done', 'ev_confirmar_fecha']], // -> dispara draft -> confirmed
        -20 => [['date_change', +2]], // cambia la fecha a mitad de camino: recalcula y avisa
        -16 => [['done', 'ev_elegir_sala']],
        // ev_foto_ponente se completa TARDE a propósito (día -14) para forzar que rd_cartel
        // (start_offset=-15) se active en regla B con esa dependencia aún pendiente.
        -14 => [['done', 'ev_foto_ponente'], ['done', 'ev_reservar_sala'], ['done', 'ev_figura_3d']],
        -13 => [['done', 'rd_cartel']],
        -11 => [['done', 'ev_crear_evento_web']],
        -10 => [['done', 'rd_qr_a4'], ['done', 'ev_pdf_reserva'], ['done', 'ev_difusion_asociaciones']],
        -9 => [['done', 'web_maquetar_mail']],
        -8 => [['done', 'web_newsletter'], ['done', 'ev_difusion_directores']],
        -7 => [['done', 'ev_pegar_carteles'], ['done', 'ev_parking']],
        -5 => [['done', 'rd_guion_entrevista']],
        -3 => [['done', 'web_credenciales']],
        -1 => [['done', 'ev_material'], ['done', 'web_material_link']],
        0 => [['done', 'rd_grabar_entrevista']],
    ];

    $cursor = $eventStart->modify('-40 days');
    $end = $eventStart->modify('+12 days');

    while ($cursor <= $end) {
        $offset = (int) $cursor->diff($eventStart)->format('%r%a') * -1; // días respecto al evento (negativo = antes)
        sim_set_now($cursor->format('Y-m-d'));

        event_planner_maybe_advance_to_held($conn, $planId);
        event_planner_maybe_close_plan($conn, $planId);
        event_planner_recompute_plan($conn, $config, $planId);

        $actions = $schedule[$offset] ?? [];
        foreach ($actions as [$type, $arg]) {
            if ($type === 'done') {
                $taskId = sim_task_id($conn, $planId, $arg);
                if ($taskId && sim_task_status($conn, $taskId) === 'active') {
                    event_planner_complete_task($conn, $config, $taskId, $boardMemberId, 'Completado por simulación.');
                    echo "  día {$cursor->format('Y-m-d')} (evento{$offset}): completada '{$arg}'\n";
                } else {
                    echo "  día {$cursor->format('Y-m-d')} (evento{$offset}): AVISO '{$arg}' no estaba activa (status actual: " . ($taskId ? sim_task_status($conn, $taskId) : 'no encontrada') . ")\n";
                }
            } elseif ($type === 'date_change') {
                $newStart = $eventStart->modify("{$arg} days");
                $newStartUtc = $newStart->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
                event_planner_change_event_datetime($conn, $config, $planId, $newStartUtc, 90, $boardMemberId);
                $eventStart = $newStart;
                $end = $eventStart->modify('+12 days');
                echo "  día {$cursor->format('Y-m-d')}: CAMBIO DE FECHA del evento a {$newStart->format('d/m/Y H:i')}\n";
            }
        }

        event_planner_maybe_advance_to_held($conn, $planId);
        event_planner_maybe_close_plan($conn, $planId);

        sim_print_new_outbox($conn, $allMemberIds, $lastSeenOutboxId);
        $cursor = $cursor->modify('+1 day');
    }

    // Lo que quede sin completar (tareas que no estaban en el guion) se marca "No aplica"
    // para poder demostrar el cierre automático held -> closed.
    $stmt = $conn->prepare("SELECT id, `key` FROM event_tasks WHERE event_plan_id = ? AND status IN ('blocked','active')");
    $stmt->bind_param('i', $planId);
    $stmt->execute();
    $leftover = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    foreach ($leftover as $t) {
        event_planner_skip_task($conn, $config, (int) $t['id'], $boardMemberId);
        echo "  (limpieza) '{$t['key']}' marcada como No aplica\n";
    }
    event_planner_maybe_close_plan($conn, $planId);
    sim_print_new_outbox($conn, $allMemberIds, $lastSeenOutboxId);

    $finalPlan = ep_fetch_plan($conn, $planId);
    echo "\nEstado final del plan: {$finalPlan['status']}\n";

    $placeholders = implode(',', array_fill(0, count($allMemberIds), '?'));
    $stmt = $conn->prepare("SELECT COUNT(*) AS total, COUNT(DISTINCT dedupe_key) AS distintos FROM email_outbox WHERE to_member_id IN ($placeholders)");
    $stmt->bind_param(str_repeat('i', count($allMemberIds)), ...$allMemberIds);
    $stmt->execute();
    $dedupe = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    echo "Correos encolados: {$dedupe['total']} (dedupe_key distintos: {$dedupe['distintos']})\n";
    echo $dedupe['total'] === $dedupe['distintos'] ? "OK: ningún correo duplicado.\n" : "FALLO: hay dedupe_key duplicados.\n";

    echo $finalPlan['status'] === 'closed' ? "OK: el plan llegó a 'closed'.\n" : "AVISO: el plan no llegó a 'closed' (revisa el guion).\n";
} finally {
    echo "\nLimpiando datos de la simulación...\n";
    $placeholders = implode(',', array_fill(0, count($allMemberIds), '?'));
    $del = $conn->prepare("DELETE FROM email_outbox WHERE to_member_id IN ($placeholders)");
    $del->bind_param(str_repeat('i', count($allMemberIds)), ...$allMemberIds);
    $del->execute();
    $del->close();

    if (!empty($planId)) {
        $del = $conn->prepare("DELETE FROM event_plans WHERE id = ?");
        $del->bind_param('i', $planId);
        $del->execute();
        $del->close();
    }

    $del = $conn->prepare("DELETE FROM members WHERE id IN ($placeholders)");
    $del->bind_param(str_repeat('i', count($allMemberIds)), ...$allMemberIds);
    $del->execute();
    $del->close();
    echo "Limpieza completa.\n";
}

$conn->close();
