<?php
if (false) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$position = trim($_POST['position'] ?? '');
$reason = trim($_POST['reason'] ?? '');
$consent = isset($_POST['consent']) ? 1 : 0;

// Field validations
if ($name === '' || $email === '' || $position === '' || $reason === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $consent !== 1) {
    header("Location: /join.php?error=validation#get-involved");
    exit;
}

include("../assets/db.php");

// Check if email already exists in DB
$checkStmt = $conn->prepare("SELECT id FROM recruiting_2025 WHERE email = ?");
$checkStmt->bind_param("s", $email);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    header("Location: /join.php?error=duplicate#get-involved");
    $checkStmt->close();
    $conn->close();
    exit;
}
$checkStmt->close();

// Insert data in DB
$stmt = $conn->prepare("INSERT INTO recruiting_2025 (full_name, email, position, interest) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $name, $email, $position, $reason);
$stmt->execute();
$stmt->close();
$conn->close();

// Mostrar HTML de confirmación
?>
<!DOCTYPE html>
<html lang="es">
<?php include("../assets/head.php"); ?>
<body class="bg-light d-flex flex-column align-items-center justify-content-center vw-100 vh-100">
<?php include("../assets/nav.php"); ?>
<div class="text-center d-flex flex-column align-items-center justify-content-center h-100">
    <div class="alert shadow-lg" role="alert" style="background-color: var(--primary);">
        <h4 class="alert-heading">¡Gracias por tu interés!</h4>
        <p>Hemos recibido tus datos correctamente. Nos pondremos en contacto contigo pronto.</p>
        <hr>
        <a href="/" class="btn btn-form">Volver al inicio</a>
    </div>
</div>
<?php include('../assets/footer.php'); ?>
<script src="../js/scripts.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
