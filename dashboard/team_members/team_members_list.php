<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['activated']) || $_SESSION['role'] !== 'admin') {
    header("Location: events/login.php");
    exit();
}
include(__DIR__ . "/../../assets/head.php");
include(__DIR__ . "/../../assets/db.php");

// Retrieve events
$result = $conn->query("SELECT * FROM members ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Miembros</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Navbar -->
<?php include("../dashboard_nav.php"); ?>

<div class="container my-5 scroll-margin">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="m-0">Lista de Miembros</h2>
        <a href="dashboard/team_members/create_team_member.php" class="btn btn-primary">+ Añadir nuevo miembro</a>
    </div>

    <table class="table table-striped table-bordered align-middle">
        <thead class="table-dark">
            <tr>
                <th>Nombre y Apellidos</th>
                <th>Mail</th>
                <th>Posición (ES/EN)</th>
                <th>Position (ES/EN)</th>
                <th>Tfno.</th>
                <th>Redes</th>
                <th>Activo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td>
                            <?= htmlspecialchars($row['full_name']) ?><br>
                            <small class="text-muted"><?= htmlspecialchars($row['full_name']) ?></small>
                        </td>
                        <td>
                            <?= htmlspecialchars($row['position_es']) ?><br>
                            <small class="text-muted"><?= htmlspecialchars($row['position_es']) ?></small>
                        </td>
                        <td>
                            <?= htmlspecialchars($row['mail']) ?><br>
                            <small class="text-muted"><?= htmlspecialchars($row['mail']) ?></small>
                        </td>
                        <td>
                            <?= htmlspecialchars($row['phone']) ?><br>
                            <small class="text-muted"><?= htmlspecialchars($row['phone']) ?></small>
                        </td>
    
                        <td>
                            <?= htmlspecialchars($row['socials']) ?><br>
                            <small class="text-muted"><?= htmlspecialchars($row['socials']) ?></small>
                        </td>
                        <td>
                            <?= htmlspecialchars($row['active']) ?><br>
                            <small class="text-muted"><?= htmlspecialchars($row['active']) ?></small>
                        </td>
                        <td>
                        <a class="btn btn-sm btn-success mb-1" href="dashboard/team_members/create_team_member.php?id=<?= $row['id'] ?>">Editar</a>
                        <a class="btn btn-sm btn-danger mb-1" href="?delete=<?= $row['id'] ?>" onclick="return confirm('¿Seguro que quieres eliminar este evento?')">Eliminar</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" class="text-center">No se encontraron miembros.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include(__DIR__ . "/../../assets/footer.php"); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
