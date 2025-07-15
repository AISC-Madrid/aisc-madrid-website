<?php
// Habilitar errores solo true para testear
if(false){
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
}
// Datos de conexión
$host = 'localhost';
$db   = 'u803318305_aisc';
$user = 'u803318305_aisc';
$pass = 'Aisc_2025?';

// Conexión
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    header("Location: /#get-involved?error=connection");
    exit;
}

// Datos del formulario
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$consent = isset($_POST['consent']) ? 1 : 0;

// Validación
if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: /#get-involved?error=validation");
    exit;
}

// Comprobar duplicado
$checkStmt = $conn->prepare("SELECT id FROM form_submissions WHERE email = ?");
$checkStmt->bind_param("s", $email);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    header("Location: /#get-involved?error=duplicate");
    $checkStmt->close();
    $conn->close();
    exit;
}
$checkStmt->close();

// Insertar datos
$stmt = $conn->prepare("INSERT INTO form_submissions (full_name, email) VALUES (?, ?)");
$stmt->bind_param("ss", $name, $email);

if ($stmt->execute()) {
    // Éxito: mostrar HTML bonito
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Gracias por unirte</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light d-flex align-items-center justify-content-center vh-100">
        <div class="text-center">
            <div class="alert alert-success shadow-lg" role="alert">
                <h4 class="alert-heading">¡Gracias por unirte!</h4>
                <p>Hemos recibido tus datos correctamente. Nos pondremos en contacto contigo pronto.</p>
                <hr>
                <a href="/" class="btn btn-primary">Volver al inicio</a>
            </div>
        </div>
    </body>
    </html>
    <?php
} else {
    // Érror: mostrar HTML volver a intentar
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Gracias por unirte</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light d-flex align-items-center justify-content-center vh-100">
        <div class="text-center">
            <div class="alert alert-danger shadow-lg" role="alert">
                <h4 class="alert-heading">¡Error al unirte!</h4>
                <p>Tu correo ya está en nuestra base de datos!</p>
                <hr>
                <a href="/#get-involved" class="btn btn-primary">Volver al inicio</a>
            </div>
        </div>
    </body>
    </html>
    <?php
}

$stmt->close();
$conn->close();
?>
