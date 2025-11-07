<!-- Dashboard Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white px-4 py-2 shadow-sm fixed-top" role="navigation" aria-label="Dashboard navigation">
  <div class="container-fluid">

    <!-- Brand / Logo -->
    <a class="navbar-brand" href="dashboard/dashboard.php" title="AISC Madrid - Dashboard">
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

        <!-- Events -->
        <li class="nav-item">
          <a class="nav-link" href="../events/events_list.php">
            <i class="bi bi-calendar-event me-1"></i> Eventos
          </a>
        </li>

        <!-- Escanear asistencia de eventos -->
        <li class="nav-item">
          <a class="nav-link" href="/dashboard/scan_attendance.php">
            <i class="bi bi-qr-code me-1"></i> Escanear Asistencia
          </a>
        </li>

        <!-- Projects -->
        <!-- <li class="nav-item">
          <a class="nav-link" href="../projects/projects_list.php">
            <i class="bi bi-diagram-3 me-1"></i> Proyectos
          </a>
        </li> -->

        <!-- Recruiting 
        <li class="nav-item">
          <a class="nav-link" href="recruiting/recruiting_list.php">
            <i class="bi bi-briefcase me-1"></i> Recruiting
          </a>
        </li>
        -->
      </ul>
    </div>
  </div>
</nav>