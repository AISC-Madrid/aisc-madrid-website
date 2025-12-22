<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start(); // Start the session

// Check if the user is logged in
$allowed_roles = ['admin', 'finance'];
if (!isset($_SESSION['activated']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: /");
    exit();
}
include(__DIR__ . "/../../assets/head.php");
include(__DIR__ . "/../../assets/db.php");
include(__DIR__ . "/../../assets/nav_dashboard.php");

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    $stmt = $conn->prepare("DELETE FROM members WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Redirect to clear the ?delete parameter from the URL
        header("Location: team_members_list.php");
        exit();
    } else {
        echo "<p style='color:red;'>❌ Error al eliminar: " . htmlspecialchars($stmt->error) . "</p>";
    }

    $stmt->close();
}

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
    <div class="container my-5 scroll-margin">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="m-0">Lista de Miembros</h2>
            <a href="dashboard/team_members/create_team_member.php" class="btn btn-primary">+ Añadir nuevo miembro</a>
        </div>

        <table class="table table-striped table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nombre y Apellidos</th>
                    <th>Posición (ES/EN)</th>
                    <th>Mail</th>
                    <th>Tfno.</th>
                    <th>Redes</th>
                    <th>Board</th>
                    <th>Activo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td>
                                <?= htmlspecialchars($row['full_name']) ?><br>
                            </td>
                            <td>
                                <?= htmlspecialchars($row['position_es']) ?><br>
                                <small class="text-muted"><?= htmlspecialchars($row['position_en']) ?></small>
                            </td>
                            <td>
                                <?= htmlspecialchars($row['mail']) ?><br>
                            </td>
                            <td>
                                <?= htmlspecialchars($row['phone']) ?><br>
                            </td>

                            <td>
                                <?= htmlspecialchars($row['socials']) ?><br>
                            </td>
                            <td>
                                <?= htmlspecialchars($row['board']) ?><br>
                            </td>
                            <td>
                                <?= htmlspecialchars($row['active']) ?><br>
                            </td>
                            <?php if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'finance'): ?>
                                <td>
                                    <a class="btn btn-sm btn-success mb-1"
                                        href="dashboard/team_members/create_team_member.php?id=<?= $row['id'] ?>">Editar</a>
                                    <a class="btn btn-sm btn-danger mb-1"
                                        href="dashboard/team_members/team_members_list.php/?delete=<?= $row['id'] ?>"
                                        onclick="return confirm('¿Seguro que quieres eliminar este miembro?')">Eliminar</a>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No se encontraron miembros.</td>
                    </tr>
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