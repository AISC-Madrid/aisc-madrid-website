<?php
// Show all PHP errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include("../assets/db.php");

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<?php include("../assets/head.php"); ?>

<body>
    <!-- Navbar -->
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white px-4 py-2 shadow-sm fixed-top" role="navigation" aria-label="Dashboard navigation">
        <div class="container-fluid">

            <!-- Brand / Logo -->
            <a class="navbar-brand" href="/" title="AISC Madrid - Dashboard">
                <img src="images/logos/PNG/AISC Logo Color.png" alt="Logo de AISC Madrid" style="height:70px;">
                <span class="fw-bold">Users Review</span>
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

    <div class="container-fluid" style="margin-top:90px;">
        <div class="row">


            <!-- Main content -->
            <main class="col-md-12 ms-sm-auto col-lg-12 px-md-4 py-4">
                <h2 class="mb-4 text-dark">Usuarios Registrados</h2>

                <div class="card">
                    <div class="card-body">
                        <?php
                        $sql = "SELECT * FROM form_submissions"; // Replace 'users' with your table name
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            echo '<div class="table-responsive">';
                            echo '<table class="table table-striped table-bordered">';
                            echo '<thead class="table-dark">';
                            echo '<tr>';
                            // Dynamically get all column names
                            while ($field = $result->fetch_field()) {
                                echo '<th>' . htmlspecialchars($field->name) . '</th>';
                            }
                            echo '</tr>';
                            echo '</thead>';
                            echo '<tbody>';

                            // Reset pointer and fetch data
                            $result->data_seek(0);
                            while ($row = $result->fetch_assoc()) {
                                echo '<tr>';
                                foreach ($row as $value) {
                                    echo '<td>' . htmlspecialchars($value ?? '—') . '</td>';
                                }
                                echo '</tr>';
                            }

                            echo '</tbody>';
                            echo '</table>';
                            echo '</div>';
                        } else {
                            echo '<p>No hay usuarios registrados.</p>';
                        }

                        $conn->close();
                        ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <?php include("../assets/footer.php"); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>