<?php
session_start();

// Check if the user is logged in with admin or events role
$allowed_roles = ['admin', 'events'];
if (!isset($_SESSION['activated']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: /");
    exit();
}

include("../../assets/db.php");

$error = '';
$success = false;

// Get all events (future and past) for the dropdown
$allEvents = $conn->query("
    SELECT id, title_es, title_en, start_datetime,
           CASE WHEN start_datetime >= NOW() THEN 1 ELSE 0 END AS is_future
    FROM events 
    ORDER BY start_datetime DESC
");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $selected_events = $_POST['events'] ?? [];

    // Validation
    if (empty($username)) {
        $error = "El username es obligatorio.";
    } elseif (empty($password)) {
        $error = "La contraseña es obligatoria.";
    } elseif (strlen($password) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres.";
    } else {
        // Check if username already exists
        $checkStmt = $conn->prepare("SELECT id FROM guests WHERE username = ?");
        $checkStmt->bind_param("s", $username);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            $error = "Este username ya está en uso. Por favor, elige otro.";
        } else {
            // Create the guest
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $insertStmt = $conn->prepare("INSERT INTO guests (username, password_hash, name) VALUES (?, ?, ?)");
            $insertStmt->bind_param("sss", $username, $password_hash, $name);

            if ($insertStmt->execute()) {
                $guestId = $conn->insert_id;

                // Link to selected events
                if (!empty($selected_events)) {
                    $linkStmt = $conn->prepare("INSERT INTO event_guest_access (guest_id, event_id) VALUES (?, ?)");
                    foreach ($selected_events as $event_id) {
                        $event_id = (int) $event_id;
                        $linkStmt->bind_param("ii", $guestId, $event_id);
                        $linkStmt->execute();
                    }
                    $linkStmt->close();
                }

                header("Location: guests_list.php?created=1");
                exit();
            } else {
                $error = "Error al crear el guest: " . $insertStmt->error;
            }
            $insertStmt->close();
        }
        $checkStmt->close();
    }
}

include("../../assets/head.php");
include("../../assets/nav_dashboard.php");
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Crear Guest - AISC Madrid</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>

<body class="bg-light">
    <div class="container my-5 scroll-margin">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="mb-4">
                    <a href="dashboard/guests/guests_list.php" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Volver a la lista
                    </a>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="bi bi-person-plus me-2"></i>Crear Nuevo Guest
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Username *</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                                        <input type="text" name="username" class="form-control"
                                            placeholder="ej: guest_evento20"
                                            value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                                    </div>
                                    <small class="text-muted">Este será el nombre de usuario para iniciar sesión</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Contraseña *</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-key"></i></span>
                                        <input type="text" name="password" class="form-control" id="password"
                                            placeholder="Mínimo 6 caracteres" required>
                                        <button type="button" class="btn btn-outline-secondary"
                                            onclick="generatePassword()">
                                            <i class="bi bi-shuffle"></i> Generar
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nombre Completo</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                    <input type="text" name="name" class="form-control" placeholder="ej: Juan García"
                                        value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                                </div>
                                <small class="text-muted">Nombre que se mostrará en el portal del guest</small>
                            </div>

                            <hr class="my-4">

                            <div class="mb-4">
                                <label class="form-label">
                                    <i class="bi bi-calendar-event me-1"></i> Asociar a Eventos
                                </label>

                                <?php if ($allEvents->num_rows > 0): ?>
                                    <div class="mb-2">
                                        <button type="button" class="btn btn-sm btn-outline-secondary me-2" onclick="togglePastEvents()">
                                            <i class="bi bi-eye me-1"></i><span id="toggle-text">Mostrar pasados</span>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearSelection()">
                                            Limpiar selección
                                        </button>
                                    </div>
                                    <div class="row" id="events-container" style="max-height: 300px; overflow-y: auto;">
                                        <?php while ($event = $allEvents->fetch_assoc()): ?>
                                            <div class="col-md-6 mb-2 event-item" data-future="<?= $event['is_future'] ?>" style="<?= !$event['is_future'] ? 'display:none;' : '' ?>">
                                                <div class="form-check">
                                                    <input type="checkbox" name="events[]" value="<?= $event['id'] ?>"
                                                        class="form-check-input event-checkbox" id="event_<?= $event['id'] ?>">
                                                    <label class="form-check-label <?= !$event['is_future'] ? 'text-muted' : '' ?>" for="event_<?= $event['id'] ?>">
                                                        <?= htmlspecialchars($event['title_es']) ?>
                                                        <br>
                                                        <small class="text-muted">
                                                            <?= date("d/m/Y H:i", strtotime($event['start_datetime'])) ?>
                                                            <?php if (!$event['is_future']): ?>
                                                                <span class="badge bg-secondary ms-1">Pasado</span>
                                                            <?php endif; ?>
                                                        </small>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle me-2"></i>
                                        No hay eventos disponibles. Puedes crear el guest y asociarlo más tarde.
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-1"></i> Crear Guest
                                </button>
                                <a href="guests_list.php" class="btn btn-outline-secondary">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('../../assets/footer.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function generatePassword() {
            const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
            let password = '';
            for (let i = 0; i < 10; i++) {
                password += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            document.getElementById('password').value = password;
        }

        let showingPast = false;
        function togglePastEvents() {
            showingPast = !showingPast;
            document.querySelectorAll('.event-item').forEach(item => {
                if (item.dataset.future === '0') {
                    item.style.display = showingPast ? '' : 'none';
                }
            });
            document.getElementById('toggle-text').textContent = showingPast ? 'Ocultar pasados' : 'Mostrar pasados';
        }

        function clearSelection() {
            document.querySelectorAll('.event-checkbox').forEach(cb => cb.checked = false);
        }
    </script>
</body>

</html>

<?php $conn->close(); ?>