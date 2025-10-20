<?php
session_start(); // Start the session
// Check if the user is logged in
if (!isset($_SESSION['activated']) || $_SESSION['role'] !== 'admin') {
    header("Location: projects/login.php");
    exit();
}

include("../assets/head.php");
include("../assets/db.php");

try {
    // Handle delete action
    if (isset($_GET['delete'])) {
        $project_id = intval($_GET['delete']);
        $check = $conn->query("SELECT id FROM projects WHERE id = $project_id");
        if ($check && $check->num_rows > 0) {
            $conn->query("DELETE FROM projects WHERE id = $project_id");
        }
        header("Location: projects_list.php");
        exit();
    }

    // Retrieve projects
    $result = $conn->query("SELECT * FROM projects ORDER BY start_date DESC");
} finally {
    $conn->close();
}


// Function to format date
function fmt_date(?string $s): string {
    if (empty($s) || $s === '0000-00-00') return '—';
    try {
        return (new DateTime($s))->format('d/m/Y');
    } catch (Exception $e) {
        return '—';
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Proyectos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Navbar -->
<?php include("../dashboard/dashboard_nav.php"); ?>

<div class="container my-5 scroll-margin">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="m-0">Lista de Proyectos</h2>
        <a href="projects/create_project.php" class="btn btn-primary">+ Crear Nuevo Proyecto</a>
    </div>

    <table class="table table-striped table-bordered align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Título (ES/EN)</th>
                <th>Fecha Inicio - Fin</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= (int)$row['id'] ?></td>
                        <td>
                            <?= htmlspecialchars($row['title_es'] ?? '') ?><br>
                            <small class="text-muted"><?= htmlspecialchars($row['title_en'] ?? '') ?></small>
                        </td>
                        <td><?= fmt_date($row['start_date'] ?? null) ?> - <?= fmt_date($row['end_date'] ?? null) ?></td>
                        <td><?= htmlspecialchars($row['status'] ?? '') ?></td>
                        <td>
                            <a class="btn btn-sm btn-success mb-1" href="projects/create_project.php?id=<?= (int)$row['id'] ?>">Editar</a>
                            <a class="btn btn-sm btn-danger mb-1"
                            href="projects/projects_list.php?delete=<?= (int)$row['id'] ?>"
                            onclick="return confirm('¿Seguro que quieres eliminar este proyecto?')">Eliminar</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5" class="text-center">No se encontraron proyectos.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include('../assets/footer.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
