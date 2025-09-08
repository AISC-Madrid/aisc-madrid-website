<!DOCTYPE html>
<html lang="es">
<?php
    include("assets/head.php");
    include("assets/db.php");

    // ---------- USERS ----------
    $totalUsers = $conn->query("SELECT COUNT(*) AS total FROM form_submissions")->fetch_assoc()['total'];
    $activeSubs = $conn->query("SELECT COUNT(*) AS total FROM form_submissions WHERE newsletter = 'yes'")->fetch_assoc()['total'];
    $unsubscribed = $conn->query("SELECT COUNT(*) AS total FROM form_submissions WHERE newsletter = 'no'")->fetch_assoc()['total'];
    $latestSubscribers = $conn->query("SELECT full_name, email, submitted_at, newsletter
                                       FROM form_submissions 
                                       ORDER BY submitted_at DESC 
                                       LIMIT 5");

    // ---------- EVENTS ----------
    $upcomingEventsCount = $conn->query("SELECT COUNT(*) AS total FROM events WHERE start_datetime > NOW()")->fetch_assoc()['total'];

    // ---------- RECRUITING ----------
    $totalApplicants = $conn->query("SELECT COUNT(*) AS total FROM recruiting_2025")->fetch_assoc()['total'];
?>
<body>

<!-- Navbar -->
<!-- Dashboard Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white px-4 py-3 shadow-sm fixed-top" role="navigation" aria-label="Dashboard navigation">
  <div class="container-fluid">

    <!-- Brand / Logo -->
    <a class="navbar-brand" href="/" title="AISC Madrid - Dashboard">
      <img src="images/logos/PNG/AISC Logo Color.png" alt="Logo de AISC Madrid" style="width: 80px;">
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
          <a class="nav-link active" href="dashboard.php">
            <i class="bi bi-house-door me-1"></i> Resumen
          </a>
        </li>

        <!-- Users -->
        <li class="nav-item">
          <a class="nav-link" href="users.php">
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


<div class="container-fluid scroll-margin">
  <div class="row">
    <!-- Sidebar -->
    <nav class="col-md-2 d-none d-md-block bg-light sidebar py-4" style="min-height:100vh;">
      <div class="nav flex-column">
        <a class="nav-link active" href="#"><i class="bi bi-speedometer2"></i> Resumen</a>
        <a class="nav-link" href="#"><i class="bi bi-people"></i> Usuarios</a>
        <a class="nav-link" href="mails/newsletter.php"><i class="bi bi-envelope"></i> Newsletter</a>
        <a class="nav-link" href="events/events_list.php"><i class="bi bi-calendar-event"></i> Eventos</a>
      </div>
    </nav>

    <!-- Main content -->
    <main class="col-md-10 ms-sm-auto col-lg-10 px-md-4 py-4">
      <h2 class="mb-4 text-dark">Panel de Control</h2>

      <!-- Cards Row -->
      <div class="row mb-4">
        <div class="col-md-4">
          <div class="card p-3">
            <div class="card-body">
              <h5 class="card-title text-muted">Usuarios Registrados</h5>
              <h2 class="fw-bold"><?= $totalUsers ?></h2>
              <p class="text-success mb-0"><i class="bi bi-people"></i> Activos: <?= $activeSubs ?> | Inactivos: <?= $unsubscribed ?></p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card p-3">
            <div class="card-body">
              <h5 class="card-title text-muted">Aplicantes Recruiting</h5>
              <h2 class="fw-bold"><?= $totalApplicants ?></h2>
              <p class="text-primary mb-0"><i class="bi bi-person-workspace"></i> Proceso 2025</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card p-3">
            <div class="card-body">
              <h5 class="card-title text-muted">Eventos Programados</h5>
              <h2 class="fw-bold"><?= $upcomingEventsCount ?></h2>
              <p class="text-info mb-0"><i class="bi bi-calendar-week"></i> Próximos meses</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Table: Últimos Suscriptores -->
      <div class="card mb-4">
        <div class="card-header bg-white">
          <h5 class="mb-0">Últimos Suscriptores</h5>
        </div>
        <div class="card-body">
          <table class="table align-middle">
            <thead>
              <tr>
                <th>Nombre</th>
                <th>Email</th>
                <th>Fecha</th>
                <th>Estado</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = $latestSubscribers->fetch_assoc()): ?>
                <tr>
                  <td><?= htmlspecialchars($row['full_name']) ?></td>
                  <td><?= htmlspecialchars($row['email']) ?></td>
                  <td><?= date("d/m/Y H:i", strtotime($row['submitted_at'])) ?></td>
                  <td>
                    <?php if ($row['newsletter'] === 'yes'): ?>
                      <span class="badge bg-success">Activo</span>
                    <?php else: ?>
                      <span class="badge bg-danger">Inactivo</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>

    </main>
  </div>
</div>

<!-- Footer -->
<?php include("assets/footer.php"); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>
