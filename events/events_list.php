<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Not logged in, redirect to login page
    header("Location: login.php");
    exit();
}
include("../assets/head.php");
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
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white px-4 py-2 shadow-sm fixed-top" role="navigation" aria-label="Dashboard navigation">
        <div class="container-fluid">

            <!-- Brand / Logo -->
            <a class="navbar-brand" href="/" title="AISC Madrid - Dashboard">
                <img src="images/logos/PNG/AISC Logo Color.png" alt="Logo de AISC Madrid" style="height:70px;">
                <span class="fw-bold">Newsletter</span>
            </a>

            <!-- Toggler for mobile -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#dashboardNav"
                aria-controls="dashboardNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Nav Items -->
            <div class="collapse navbar-collapse justify-content-end" id="dashboardNav">
                <ul class="navbar-nav align-items-center">

                    <!-- Dashboard link -->
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard/dashboard.php">
                            <i class="bi bi-house-door me-1"></i> Resumen
                        </a>
                    </li>

                    <!-- Users -->
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard/users.php">
                            <i class="bi bi-people me-1"></i> Usuarios
                        </a>
                    </li>

                    <!-- Newsletter -->
                    <li class="nav-item">
                        <a class="nav-link" href="mails/newsletter.php">
                            <i class="bi bi-envelope me-1"></i> Newsletter
                        </a>
                    </li>

                    <!-- Events -->
                    <li class="nav-item">
                        <a class="nav-link" href="events/events_list.php">
                            <i class="bi bi-calendar-event me-1"></i> Eventos
                        </a>
                    </li>

                    <!-- Recruiting -->
                    <li class="nav-item">
                        <a class="nav-link" href="recruiting/recruiting_list.php">
                            <i class="bi bi-briefcase me-1"></i> Recruiting
                        </a>
                    </li>

                    <!-- Divider -->

                    <!-- <li class="nav-item mx-2 d-none d-lg-block">
          <span class="text-light">|</span>
        </li> -->

                    <!-- Profile Dropdown -->

                    <!-- <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="profileDropdown" role="button"
             data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle me-1"></i> Admin
          </a>
          <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="profileDropdown">
            <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i> Configuración</a></li>
            <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i> Perfil</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Cerrar sesión</a></li>
          </ul>
        </li> -->

                </ul>
            </div>
        </div>
    </nav>

<div class="container my-5 scroll-margin">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="m-0">Lista de Eventos</h2>
        <a href="events/create_event.php" class="btn btn-primary">+ Crear Nuevo Evento</a>
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
                            <?= htmlspecialchars($row['speaker']) ?><br>
                            <small class="text-muted"><?= htmlspecialchars($row['speaker']) ?></small>
                        </td>
                        <td>
                            <?= date("d/m/Y H:i", strtotime($row['start_datetime'])) ?>
                            -
                            <?= date("d/m/Y H:i", strtotime($row['end_datetime'])) ?>
                        </td>
                        <td><?= htmlspecialchars($row['location']) ?></td>
                        <td>
                            <a class="btn btn-sm btn-success mb-1" href="events/create_event.php?id=<?= $row['id'] ?>">Editar</a>
                            <a class="btn btn-sm btn-danger mb-1" href="events/events_list.php?delete=<?= $row['id'] ?>" onclick="return confirm('¿Seguro que quieres eliminar este evento?')">Eliminar</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" class="text-center">No se encontraron eventos.</td></tr>
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
