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
    <?php include("dashboard_nav.php"); ?>

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