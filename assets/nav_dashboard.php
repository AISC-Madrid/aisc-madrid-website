<?php
// 4 roles are defined, each with different permissions.
$permisos = [
  'admin' => ['resumen', 'usuarios', 'eventos', 'guests', 'scan', 'email', 'proyectos', 'equipo', 'hashing', 'create-email'],
  'events' => ['resumen', 'eventos', 'guests', 'scan', 'proyectos'],
  'finance' => ['resumen', 'eventos', 'scan', 'proyectos', 'equipo'],
  'marketing' => ['resumen', 'eventos', 'scan', 'proyectos'],
  'web' => ['resumen', 'eventos', 'scan', 'proyectos', 'create-email'],
  'guest' => ['scan'],
];

$rol_actual = $_SESSION['role'];
$opciones = $permisos[$rol_actual] ?? [];

// Helper function para verificar permisos
if (!function_exists('isAllowed')) {
  function isAllowed($opcion)
  {
    global $opciones;
    return in_array($opcion, $opciones);
  }
}
?>
<!-- Dashboard Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white px-4 py-2 shadow-sm fixed-top" role="navigation"
  aria-label="Dashboard navigation">
  <div class="container-fluid">

    <!-- Brand / Logo -->
    <a class="navbar-brand" href="<?php echo ($rol_actual === 'guest') ? '/dashboard/guests/guest_dashboard.php' : '/dashboard/dashboard.php'; ?>" title="AISC Madrid - Dashboard">
      <img src="../images/logos/PNG/AISC Logo Color.png" alt="Logo de AISC Madrid" style="height:70px;">
      <span class="fw-bold">Dashboard</span>
    </a>

    <!-- Toggler for mobile -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#dashboardNav"
      aria-controls="dashboardNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Nav Items -->
    <div class="collapse navbar-collapse justify-content-end" id="dashboardNav">
      <ul class="navbar-nav align-items-center">

        <?php if (isAllowed('resumen')): ?>
          <!-- Dashboard link -->
          <li class="nav-item">
            <a class="nav-link active" href="<?php echo ($rol_actual === 'guest') ? '/dashboard/guests/guest_dashboard.php' : '/dashboard/dashboard.php'; ?>">
              <i class="bi bi-house-door me-1"></i> Resumen
            </a>
          </li>
        <?php endif; ?>

        <?php if (isAllowed('usuarios')): ?>
          <!-- Users -->
          <li class="nav-item">
            <a class="nav-link" href="dashboard/users.php">
              <i class="bi bi-people me-1"></i> Usuarios
            </a>
          </li>
        <?php endif; ?>

        <?php if (isAllowed('eventos')): ?>
          <!-- Events -->
          <li class="nav-item">
            <a class="nav-link" href="/events/events_list.php">
              <i class="bi bi-calendar-event me-1"></i> Eventos
            </a>
          </li>
        <?php endif; ?>

        <?php if (isAllowed('scan')): ?>
          <!-- Escanear asistencia de eventos -->
          <li class="nav-item">
            <a class="nav-link" href="<?php echo ($rol_actual === 'guest') ? '/dashboard/guests/guest_scan.php' : '/dashboard/scan_attendance.php'; ?>">
              <i class="bi bi-qr-code me-1"></i> Escanear Asistencia
            </a>
          </li>
        <?php endif; ?>

        <?php if (isAllowed('guests')): ?>
          <!-- Gestión de Guests -->
          <li class="nav-item">
            <a class="nav-link" href="/dashboard/guests/guests_list.php">
              <i class="bi bi-person-badge me-1"></i> Guests
            </a>
          </li>
        <?php endif; ?>

        <?php if (isAllowed('create-email')): ?>
          <!-- Crear correo -->
          <li class="nav-item">
            <a class="nav-link" href="/dashboard/mail_creation.php">
              <i class="bi bi-envelope-plus me-1"></i> Crear Email
            </a>
          </li>
        <?php endif; ?>

        <?php if (isAllowed('email')): ?>
          <!-- Probar envío de correo -->
          <li class="nav-item">
            <a class="nav-link" href="/dashboard/send_test_email.php">
              <i class="bi bi-envelope me-1"></i> Probar Envío de Correo
            </a>
          </li>
        <?php endif; ?>

        <?php if (isAllowed('proyectos')): ?>
          <!-- Projects -->
          <li class="nav-item">
            <a class="nav-link" href="../projects/projects_list.php">
              <i class="bi bi-diagram-3 me-1"></i> Proyectos
            </a>
          </li>
        <?php endif; ?>

        <?php if (isAllowed('equipo')): ?>
          <!-- Equipo -->
          <li class="nav-item">
            <a class="nav-link" href="dashboard/team_members/team_members_list.php">
              <i class="bi bi-cup-hot me-1"></i> Equipo
            </a>
          </li>
        <?php endif; ?>

      </ul>
    </div>
  </div>
</nav>