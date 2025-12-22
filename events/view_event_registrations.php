<?php
session_start();
// Check if the user is logged in
$allowed_roles = ['admin', 'events', 'viewer'];
if (!isset($_SESSION['activated']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: /");
    exit();
}

include("../assets/head.php");
include("../assets/db.php");

// Get event ID
$event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;

if ($event_id <= 0) {
    die("Invalid event ID");
}

// Get event details
$event_stmt = $conn->prepare("SELECT id, title_es, title_en FROM events WHERE id = ?");
$event_stmt->bind_param("i", $event_id);
$event_stmt->execute();
$event_result = $event_stmt->get_result();
$event = $event_result->fetch_assoc();
$event_stmt->close();

if (!$event) {
    die("Event not found");
}

// Get registrations for this event
$registrations_stmt = $conn->prepare("
    SELECT 
        er.id,
        er.name,
        er.email,
        er.attendance_status,
        er.registration_date
    FROM event_registrations er
    WHERE er.event_id = ?
    ORDER BY er.registration_date DESC
");
$registrations_stmt->bind_param("i", $event_id);
$registrations_stmt->execute();
$registrations_result = $registrations_stmt->get_result();
$registrations = [];
while ($row = $registrations_result->fetch_assoc()) {
    $registrations[] = $row;
}
$registrations_stmt->close();

// Get registration count
$count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM event_registrations WHERE event_id = ?");
$count_stmt->bind_param("i", $event_id);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_registrations = $count_result->fetch_assoc()['total'];
$count_stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registros del Evento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Navbar -->
    <?php
    if($_SESSION['role'] === 'admin'){
        include("../dashboard/dashboard_nav.php"); 
    }else{
        include("../dashboard/dashboard_nav_noadmin.php");
    }
    ?>

    <div class="container my-5 scroll-margin">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="m-0">Registros del Evento</h2>
                <p class="text-muted mb-0">
                    <strong data-es="<?= htmlspecialchars($event['title_es']) ?>" data-en="<?= htmlspecialchars($event['title_en']) ?>">
                        <?= htmlspecialchars($event['title_es']) ?>
                    </strong>
                </p>
            </div>
            <div>
                <a href="/events/events_list.php" class="btn btn-secondary">← Volver a Lista de Eventos</a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    Total de registrados: <span class="badge bg-primary"><?= $total_registrations ?></span>
                </h5>
            </div>
            <div class="card-body">
                <?php if (count($registrations) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Estado de Asistencia</th>
                                    <th>Fecha de Registro</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($registrations as $index => $reg): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= htmlspecialchars($reg['name']) ?></td>
                                        <td><?= htmlspecialchars($reg['email']) ?></td>
                                        <td>
                                            <?php if ($reg['attendance_status'] === 'attended'): ?>
                                                <span class="badge bg-success">Asistió</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">No confirmado</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date("d/m/Y H:i", strtotime($reg['registration_date'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center text-muted">No hay registros para este evento.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include('../assets/footer.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>

