<?php
// Habilitar errores solo true para testear
if (false) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
}
// Datos del formulario
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$consent = isset($_POST['consent']) ? 1 : 0;

// Validación
if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: /?error=validation#get-involved");
    exit;
}

// Comprobar duplicado
$checkStmt = $conn->prepare("SELECT id FROM form_submissions WHERE email = ?");
$checkStmt->bind_param("s", $email);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    header("Location: /?error=duplicate#get-involved");
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

    <?php
    include("../assets/head.php");
    ?>


    <body class="bg-light d-flex flex-column align-items-center justify-content-center vw-100 vh-100">
        <?php include("../assets/nav.php") ?>

        <div class="text-center d-flex flex-column align-items-center justify-content-center h-100">
            <div class="alert shadow-lg" role="alert" style="background-color: var(--primary);">
                <h4 class="alert-heading"
                    data-es="¡Gracias por unirte!"
                    data-en="Thank you for joining!">¡Gracias por unirte!</h4>

                <p data-es="Hemos recibido tus datos correctamente. Revisa tu bandeja de entrada!"
                    data-en="We have received your data correctly. Check your inbox!">
                    Hemos recibido tus datos correctamente. Revisa tu bandeja de entrada!
                </p>

                <hr>

                <a href="/" class="btn btn-primary"
                    data-es="Volver al inicio"
                    data-en="Return to homepage">Volver al inicio</a>
            </div>

            <!-- WhatsApp Join Button -->
            <a href="https://chat.whatsapp.com/DHdetGyDbvnAMMKo0JaaqY?mode=r_c"
                target="_blank"
                class="btn btn-success d-inline-flex align-items-center gap-2 px-4 py-2 mt-3 shadow-lg join-whatsapp-button"
                data-es="Únete a la comunidad AISC Madrid en WhatsApp"
                data-en="Join the AISC Madrid community on WhatsApp">
                <i class="bi bi-whatsapp fs-4"></i>
                <span data-es="Únete a la comunidad AISC Madrid en WhatsApp"
                    data-en="Join the AISC Madrid community on WhatsApp">
                    Únete a la comunidad AISC Madrid en WhatsApp
                </span>
            </a>
        </div>

        <!-- Footer include -->
        <?php include('../assets/footer.php'); ?>

        <!-- Bootstrap validation script -->
        <script src="../js/scripts.js"></script>

        <!-- Bootstrap Bundle JS (includes Popper) -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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