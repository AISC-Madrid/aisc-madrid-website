<?php
if (false) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
}

require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$consent = isset($_POST['consent']) ? 1 : 0;

if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: /index.php?error=validation#get-involved");
    exit;
}

include("../assets/db.php");

// Verificar si el correo ya existe
$checkStmt = $conn->prepare("SELECT id FROM form_submissions WHERE email = ?");
$checkStmt->bind_param("s", $email);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    header("Location: /index.php?error=duplicate#get-involved");
    $checkStmt->close();
    $conn->close();
    exit;
}
$checkStmt->close();

// Insertar en DB con token
$token = bin2hex(random_bytes(16));

$stmt = $conn->prepare("INSERT INTO form_submissions (full_name, email, newsletter, unsubscribe_token) VALUES (?, ?, 'yes', ?)");
$stmt->bind_param("sss", $name, $email, $token);

if ($stmt->execute()) {
    // Enviar correo
    $mail = new PHPMailer;
    $mail->CharSet = 'UTF-8';
    $mail->isSMTP();
    $mail->SMTPDebug = 2; // muestra toda la conversación SMTP
    $mail->Debugoutput = 'html';

    $mail->Host = 'smtp.hostinger.com';
    $mail->Port = 587;
    $mail->SMTPAuth = true;

    $config = include('../config.php');
    $mail->Username = $config['smtp_user'];
    $mail->Password = $config['smtp_pass'];
    $mail->setFrom('info@aiscmadrid.com', 'AISC Madrid');
    $mail->addReplyTo('aisc.asoc@uc3m.es', 'AISC Madrid');
    $mail->addAddress($email, explode(' ', $name)[0]);
    $mail->Subject = '¡Bienvenid@ a la comunidad AISC Madrid!';

    $htmlContent = "
    <html>
    <head><title>¡Bienvenid@ a la comunidad AISC Madrid!</title></head>
    <body>
      <h2>¡Hola " . explode(' ', $name)[0] . "!</h2>
      <p>Gracias por unirte a la comunidad de <strong>AISC Madrid</strong>.</p>
      <p>A partir de ahora, recibirás noticias sobre nuestros próximos eventos, talleres y actividades.</p>
      <p>Estamos encantados de tenerte con nosotros. Puedes unirte a la comunidad de WhatsApp aquí:</p>

      <a href='https://chat.whatsapp.com/BpdXitZhwGCCpErwBoj3hv?mode=r_c'
      target='_blank'
      style='display: inline-flex; align-items: center; gap: 10px; padding: 10px 20px; background-color: #25D366; color: white !important; text-decoration: none; border-radius: 5px; font-weight: bold; font-family: Arial, sans-serif; margin-top: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'>Únete a la comunidad AISC Madrid en WhatsApp</a>

      <br><br>

      <p>Síguenos también en redes sociales:</p>

      <a href='https://instagram.com/aisc_madrid' target='_blank' style='display: inline-flex; align-items: center; gap: 10px; padding: 10px 20px; background-color: #D43089; color: white !important; text-decoration: none; border-radius: 5px; font-weight: bold; font-family: Arial, sans-serif; margin-top: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'>Instagram</a>

      <a href='https://www.linkedin.com/company/ai-student-collective-madrid/' target='_blank' style='display: inline-flex; align-items: center; gap: 10px; padding: 10px 20px; background-color: #0B66C3; color: white !important; text-decoration: none; border-radius: 5px; font-weight: bold; font-family: Arial, sans-serif; margin-top: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'>LinkedIn</a>

      <p>Nos vemos pronto,<br>Equipo de AISC UC3M</p>

      <div style='text-align:right; margin-top:30px;'>
          <a href='https://orange-shark-883400.hostingersite.com/processing/unsubscribe.php?token=" . urlencode($token) . "' style='color: black; text-decoration: underline; font-family: Arial, sans-serif; font-size: 12px;'>Cancelar suscripción Newsletter</a>
      </div>
    </body>
    </html>";

    $mail->isHTML(true);
    $mail->Body = $htmlContent;
    $mail->AltBody = "Hola " . explode(' ', $name)[0] . ", gracias por unirte a la comunidad AISC Madrid.";

    if (!$mail->send()) {
        error_log('Mailer Error: ' . $mail->ErrorInfo);
    }

    // Página de confirmación
    ?>
<!DOCTYPE html>
    <html lang="es">
    <?php include("../assets/head.php"); ?>
    <body class="bg-light d-flex flex-column align-items-center justify-content-center vw-100 vh-100">
        <?php include("../assets/nav.php") ?>
        <div class="text-center d-flex flex-column align-items-center justify-content-center h-100">
            <div class="alert shadow-lg" role="alert" style="background-color: var(--primary);">
                <h4 class="alert-heading">¡Gracias por unirte!</h4>
                <p>Hemos recibido tus datos correctamente. Revisa tu bandeja de entrada o spam.</p>
                <hr>
                <a href="/" class="btn btn-form">Volver al inicio</a>
            </div>
            <a href="https://chat.whatsapp.com/BpdXitZhwGCCpErwBoj3hv?mode=r_c"
                target="_blank"
                class="btn btn-success d-inline-flex align-items-center gap-2 px-4 py-2 mt-3 shadow-lg join-whatsapp-button">
                <i class="bi bi-whatsapp fs-4"></i>
                <span>Únete a la comunidad AISC Madrid en WhatsApp</span>
            </a>
        </div>
        <?php include('../assets/footer.php'); ?>
        <script src="../js/scripts.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>

    <?php
} else {
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
