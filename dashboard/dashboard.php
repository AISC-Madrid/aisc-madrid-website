<!DOCTYPE html>
<html lang="es">
<?php
session_start(); // Start the session

// Check if the user is logged in
$allowed_roles = ['admin', 'events', 'viewer'];
if (!isset($_SESSION['activated']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: /");
    exit();
}
    include("../assets/head.php");
    include("../assets/db.php");
    // Check if the user is logged in

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

    // ---------- PROJECTS ----------
    //$upcomingProjectsCount = $conn->query("SELECT COUNT(*) AS total FROM projects WHERE start_date > NOW()")->fetch_assoc()['total'];

    // ---------- RECRUITING ----------
    $totalApplicants = $conn->query("SELECT COUNT(*) AS total FROM recruiting_2025")->fetch_assoc()['total'];
?>
<body>
<?php
if($_SESSION['role'] === 'admin'){
  include("dashboard_nav.php"); 
}else{
  include("dashboard_nav_noadmin.php");
}
?>
<div class="container-fluid scroll-margin">
  <div class="row">

    <!-- Main content -->
    <main class="col-md-12 ms-sm-auto col-lg-12 px-md-4 py-4">
      <h2 style="color:black">Hola, <?= isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'invitado' ?></h2>
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
                <th>Newsletter</th>
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
<?php include("../assets/footer.php"); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>
