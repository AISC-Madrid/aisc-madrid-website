<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Not logged in, redirect to login page
    header("Location: login.php");
    exit();
}
include("../assets/db.php");

// Handle delete action
if (isset($_GET['delete'])) {
    $event_id = intval($_GET['delete']);
    $conn->query("DELETE FROM events WHERE id = $event_id");
    header("Location: events_list.php");
    exit();
}

// Retrieve events
$result = $conn->query("SELECT * FROM events ORDER BY start_datetime DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Eventos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="m-0">Lista de Eventos</h2>
        <a href="create_event.php" class="btn btn-primary">+ Crear Nuevo Evento</a>
    </div>

    <table class="table table-striped table-bordered align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Título (ES/EN)</th>
                <th>Tipo (ES/EN)</th>
                <th>Fecha Inicio - Fin</th>
                <th>Ubicación</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
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
                            <?= date("d/m/Y H:i", strtotime($row['start_datetime'])) ?>
                            -
                            <?= date("d/m/Y H:i", strtotime($row['end_datetime'])) ?>
                        </td>
                        <td><?= htmlspecialchars($row['location']) ?></td>
                        <td>
                            <a class="btn btn-sm btn-success mb-1" href="create_event.php?id=<?= $row['id'] ?>">Editar</a>
                            <a class="btn btn-sm btn-danger mb-1" href="?delete=<?= $row['id'] ?>" onclick="return confirm('¿Seguro que quieres eliminar este evento?')">Eliminar</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" class="text-center">No se encontraron eventos.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
