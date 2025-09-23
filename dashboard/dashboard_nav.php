<!-- Dashboard Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white px-4 py-2 shadow-sm fixed-top" role="navigation" aria-label="Dashboard navigation">
  <div class="container-fluid">

    <!-- Brand / Logo -->
    <a class="navbar-brand" href="/" title="AISC Madrid - Dashboard">
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

        <!-- Newsletter -->
        <li class="nav-item">
          <a class="nav-link" href="../mails/newsletter.php">
            <i class="bi bi-envelope me-1"></i> Newsletter
          </a>
        </li>

        <!-- Events -->
        <li class="nav-item">
          <a class="nav-link" href="../events/events_list.php">
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