<?php
session_start();

// Check if the user is logged in with admin or events role
$allowed_roles = ['admin', 'events'];
if (!isset($_SESSION['activated']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: /");
    exit();
}

include("../../assets/db.php");

// Validate guest ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: guests_list.php");
    exit();
}

$guest_id = (int)$_GET['id'];

// Get guest data
$stmt = $conn->prepare("SELECT * FROM guests WHERE id = ?");
$stmt->bind_param("i", $guest_id);
$stmt->execute();
$result = $stmt->get_result();
$guest = $result->fetch_assoc();
$stmt->close();

if (!$guest) {
    header("Location: guests_list.php?error=notfound");
    exit();
}

// Get currently associated events
$associatedEvents = [];
$assocResult = $conn->query("SELECT event_id FROM event_guest_access WHERE guest_id = $guest_id");
while ($row = $assocResult->fetch_assoc()) {
    $associatedEvents[] = $row['event_id'];
}

// Get all events (future and past) for the dropdown
$allEvents = $conn->query("
    SELECT id, title_es, start_datetime,
           CASE WHEN start_datetime >= NOW() THEN 1 ELSE 0 END AS is_future
    FROM events 
    ORDER BY start_datetime DESC
");

$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $selected_events = $_POST['events'] ?? [];
    
    // Validation
    if (empty($username)) {
        $error = "El username es obligatorio.";
    } else {
        // Check if username already exists (excluding current guest)
        $checkStmt = $conn->prepare("SELECT id FROM guests WHERE username = ? AND id != ?");
        $checkStmt->bind_param("si", $username, $guest_id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            $error = "Este username ya está en uso por otro guest.";
        } else {
            // Update the guest
            if (!empty($password)) {
                // Update with new password
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $updateStmt = $conn->prepare("UPDATE guests SET username = ?, password_hash = ?, name = ?, is_active = ? WHERE id = ?");
                $updateStmt->bind_param("sssii", $username, $password_hash, $name, $is_active, $guest_id);
            } else {
                // Update without changing password
                $updateStmt = $conn->prepare("UPDATE guests SET username = ?, name = ?, is_active = ? WHERE id = ?");
                $updateStmt->bind_param("ssii", $username, $name, $is_active, $guest_id);
            }
            
            if ($updateStmt->execute()) {
                // Update event associations
                // First, remove all existing associations
                $conn->query("DELETE FROM event_guest_access WHERE guest_id = $guest_id");
                
                // Then add the selected ones
                if (!empty($selected_events)) {
                    $linkStmt = $conn->prepare("INSERT INTO event_guest_access (guest_id, event_id) VALUES (?, ?)");
                    foreach ($selected_events as $event_id) {
                        $event_id = (int)$event_id;
                        $linkStmt->bind_param("ii", $guest_id, $event_id);
                        $linkStmt->execute();
                    }
                    $linkStmt->close();
                }
                
                header("Location: /dashboard/guests/guests_list.php?updated=1");
                exit();
            } else {
                $error = "Error al actualizar el guest: " . $updateStmt->error;
            }
            $updateStmt->close();
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
    <title>Editar Guest - AISC Madrid</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>

<body class="bg-light">
    <div class="container my-5 scroll-margin">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="mb-4">
                    <a href="/dashboard/guests/guests_list.php" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Volver a la lista
                    </a>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="bi bi-pencil me-2"></i>Editar Guest
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Username *</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                                        <input type="text" name="username" class="form-control" 
                                               value="<?= htmlspecialchars($guest['username']) ?>" 
                                               required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nueva Contraseña</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-key"></i></span>
                                        <input type="text" name="password" class="form-control" 
                                               id="password" placeholder="Dejar vacío para mantener actual">
                                        <button type="button" class="btn btn-outline-secondary" onclick="generatePassword()">
                                            <i class="bi bi-shuffle"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">Dejar vacío si no deseas cambiar la contraseña</small>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nombre Completo</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                    <input type="text" name="name" class="form-control" 
                                           value="<?= htmlspecialchars($guest['name'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input type="checkbox" name="is_active" class="form-check-input" 
                                           id="is_active" value="1" <?= $guest['is_active'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_active">
                                        <strong>Guest Activo</strong>
                                        <br>
                                        <small class="text-muted">Si está desactivado, el guest no podrá iniciar sesión</small>
                                    </label>
                                </div>
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
                                                        class="form-check-input event-checkbox" id="event_<?= $event['id'] ?>"
                                                        <?= in_array($event['id'], $associatedEvents) ? 'checked' : '' ?>>
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
                                        No hay eventos disponibles.
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-1"></i> Guardar Cambios
                                </button>
                                <a href="guests_list.php" class="btn btn-outline-secondary">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Danger Zone -->
                <div class="card border-danger mt-4">
                    <div class="card-header bg-danger text-white">
                        <i class="bi bi-exclamation-triangle me-2"></i>Zona de Peligro
                    </div>
                    <div class="card-body">
                        <p class="mb-3">Eliminar este guest de forma permanente. Esta acción no se puede deshacer.</p>
                        <a href="/dashboard/guests/guests_list.php?delete=<?= $guest_id ?>" 
                           class="btn btn-outline-danger"
                           onclick="return confirm('¿Estás seguro de que quieres eliminar este guest? Esta acción no se puede deshacer.')">
                            <i class="bi bi-trash me-1"></i> Eliminar Guest
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('../../assets/footer.php'); ?>
    
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
