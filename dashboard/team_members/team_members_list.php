<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Not logged in, redirect to login page
    header("Location: login.php");
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
                    <!-- Equipo -->
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard/team_members/team_members_list.php">
                        <i class="bi bi-cup-hot me-1"></i> Equipo
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
        <h2 class="m-0">Lista de Miembros</h2>
        <a href="dashboard/team_members/add_team_member.php" class="btn btn-primary">+ Añadir nuevo miembro</a>
    </div>

    <table class="table table-striped table-bordered align-middle">
        <thead class="table-dark">
            <tr>
                <th>Nombre y Apellidos</th>
                <th>Mail</th>
                <th>Posición (ES/EN)</th>
                <th>Tfno.</th>
                <th>Redes</th>
                <th>Activo</th>
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
                            <?= htmlspecialchars($row['position_en']) ?><br>
                            <small class="text-muted"><?= htmlspecialchars($row['position_en']) ?></small>
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
                        <a class="btn btn-sm btn-success mb-1" href="team_members/update_team_member.php?id=<?= $row['id'] ?>">Editar</a>
                        <a class="btn btn-sm btn-danger mb-1" href="<?= $row['id'] ?>" onclick="return confirm('¿Seguro que quieres eliminar este evento?')">Eliminar</a>
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
