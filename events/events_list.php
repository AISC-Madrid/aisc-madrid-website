<?php
session_start(); // Start the session
// Check if the user is logged in
$allowed_roles = ['admin', 'events', 'web', 'finance', 'marketing'];
if (!isset($_SESSION['activated']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: /");
    exit();
}

include("../assets/head.php");
include("../assets/db.php");
include("../assets/nav_dashboard.php");

// Handle delete action
if (isset($_GET['delete'])) {
    $event_id = intval($_GET['delete']);
    $conn->query("DELETE FROM events WHERE id = $event_id");
    header("Location: events_list.php");
    exit();
}

// Retrieve events with registration count
$result = $conn->query("
    SELECT 
        e.*,
        COUNT(er.id) AS registration_count
    FROM events e
    LEFT JOIN event_registrations er ON e.id = er.event_id
    GROUP BY e.id
    ORDER BY e.start_datetime DESC
");
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Lista de Eventos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container my-5 scroll-margin">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="m-0">Lista de Eventos</h2>
            <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] == 'events'): ?>
                <a href="/events/create_event.php" class="btn btn-primary">+ Crear Nuevo Evento</a>
            <?php endif; ?>
        </div>

        <table class="table table-striped table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Título (ES/EN)</th>
                    <th>Tipo (ES/EN)</th>
                    <th>Ponente</th>
                    <th>Fecha Inicio - Fin</th>
                    <th>Ubicación</th>
                    <th>Registrados</th>
                    <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'events'): ?>
                        <th>Acciones</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td>
                                <?= htmlspecialchars($row['title_es']) ?><br>
                                <small class="text-muted"><?= htmlspecialchars($row['title_en']) ?></small>
                            </td>
                            <td>
                                <?= htmlspecialchars($row['type_es']) ?><br>
                                <small class="text-muted"><?= htmlspecialchars($row['type_en']) ?></small>
                            </td>
                            <td>
                                <?= htmlspecialchars($row['speaker']) ?><br>
                            </td>
                            <td>
                                <?= date("d/m/Y H:i", strtotime($row['start_datetime'])) ?>
                                -
                                <?= date("d/m/Y H:i", strtotime($row['end_datetime'])) ?>
                            </td>
                            <td><?= htmlspecialchars($row['location']) ?></td>
                            <td class="text-center">
                                <a href="/events/view_event_registrations.php?event_id=<?= $row['id'] ?>"
                                    class="badge bg-primary text-decoration-none" title="Ver registros">
                                    <?= (int) $row['registration_count'] ?>
                                </a>
                                <?php if ($row['requires_registration']): ?>
                                    <small class="d-block text-muted">Requiere registro</small>
                                <?php endif; ?>
                            </td>
                            <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'events'): ?>
                                <td>
                                    <a class="btn btn-sm btn-success mb-1"
                                        href="/events/create_event.php?id=<?= $row['id'] ?>">Editar</a>
                                    <a class="btn btn-sm btn-secondary mb-1" target="_blank"
                                        href="/processing/export_attendees_pdf.php?event_id=<?= $row['id'] ?>"
                                        title="Descargar lista de asistentes en PDF">
                                        <i class="bi bi-file-earmark-pdf"></i> PDF
                                    </a>
                                    <a class="btn btn-sm btn-danger mb-1" href="/events/events_list.php?delete=<?= $row['id'] ?>"
                                        onclick="return confirm('¿Seguro que quieres eliminar este evento?')">Eliminar</a>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No se encontraron eventos.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php include('../assets/footer.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
$conn->close();
?>