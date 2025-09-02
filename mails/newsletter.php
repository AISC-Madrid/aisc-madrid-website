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
    header("Location: /index.php?error=validation#newsletter");
    exit;
}

include("../assets/db.php");


if ($stmt->execute()) {
    // Enviar correo
    $mail = new PHPMailer;
    $mail->CharSet = 'UTF-8';
    $mail->isSMTP();
    $mail->SMTPDebug = 0;
    $mail->Host = 'smtp.hostinger.com';
    $mail->Port = 587;
    $mail->SMTPAuth = true;

    $config = include('../config.php');
    $mail->Username = $config['smtp_user'];
    $mail->Password = $config['smtp_pass'];
    $mail->setFrom('info@aiscmadrid.com', 'AISC Madrid');
    $mail->addReplyTo('aisc.asoc@uc3m.es', 'AISC Madrid');
    $mail->addAddress('100498982@alumnos.uc3m.es');
    $mail->Subject = '¡Bienvenid@ a la comunidad AISC Madrid!';

$firstName = "Hugo";

$htmlContent = <<<HTML
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Newsletter AISC Madrid</title>
    </head>
    <body style="margin:0; padding:0; font-family: Arial, sans-serif; background-color:#f4f4f4;">
    <table align="center" width="600" style="border-collapse: collapse; background-color:#ffffff; margin-top:20px; border-radius:8px; overflow:hidden;">

    <!-- Cabecera -->
    <tr>
        <td align="center" style="padding:20px; background-color:#EB178E; color:#ffffff;">
            <h1 style="margin:0; font-size:24px;">¡Gracias por pasaros por nuestro stand!</h1>
        </td>
    </tr>

    <!-- Imagen -->
    <tr>
        <td align="center" style="padding:20px;">
            <img src="https://aiscmadrid.com/images/events/event2/presentation.png"
                alt="AISC Madrid - Jornada de Bienvenida" width="100%"
                style="max-width:560px; border-radius:6px; display:block;">
        </td>
    </tr>

    <!-- Texto principal -->
    <tr>
        <td style="padding:20px; color:#333333; font-size:16px; line-height:1.5;">
            <p align="center"><strong>¡Fue un placer conoceros en la Jornada de Bienvenida!</strong></p>
            <p>Desde AISC Madrid, la primera asociación de inteligencia artificial de la EPS,
            estamos muy ilusionados de contar con vuestra energía y entusiasmo.</p>
        </td>
    </tr>

    <tr>
        <td align="center" style="padding:20px; color:#EB178E;"> 
            <h1 style="margin:0; font-size:24px;"><strong>Invitación Evento Presentación AISC Madrid</strong></h1>
        </td>
    </tr>

    <tr>
        <td style="padding:20px; color:#333333; font-size:16px; text-align:left; line-height:1.6;">
            <p>
            Nos gustaría invitarte a la <strong>presentación oficial de AISC Madrid</strong>:
            <br>
            Hablaremos de la proyección de la asociación, de los talleres y charlas que organizaremos, y de todas las oportunidades para que puedas involucrarte desde el primer día.
            </p>
            <p style="margin:8px 0;">📍 <strong>Lugar:</strong> Aula 2.3.C02B, Edificio Sabatini EPS Universidad Carlos III</p>
            <p style="margin:8px 0;">📅 <strong>Fecha:</strong> 11 de septiembre de 2025</p>
            <p style="margin:8px 0;">⏰ <strong>Hora:</strong> 13:30h</p>
        </td>
    </tr>

    <tr>
        <td align="center" style="padding:20px; color:#EB178E;"> 
            <h1 style="margin:0; font-size:24px;"><strong>Próximos Eventos</strong></h1>
        </td>
    </tr>

    <tr>
        <td style="padding:20px; color:#333333; font-size:16px; line-height:1.5;">
            <p align="center"><strong>Workshops</strong></p>
            <p><strong>Introduction to AI and ML</strong></p>
            <p style="margin:8px 0;">📍 <strong>Lugar:</strong> Aula 2.0C04, Edificio Sabatini EPS Universidad Carlos III</p>
            <p style="margin:8px 0;">📅 <strong>Fecha:</strong> 15 de septiembre de 2025</p>
            <p style="margin:8px 0;">⏰ <strong>Hora:</strong> 14:00h</p>
            <p align="center"><strong>Eventos</strong></p>
            <p>Introduction to AI and ML</p>
            <br>
            <p>Pincha el botón para más información!</p>
        </td>                
    </tr>

    <!-- Botón -->
    <tr>
        <td align="center" style="padding:20px;">
            <a href="https://aiscmadrid.com/#events"
                style="background-color:#20CCF1; color:#ffffff; text-decoration:none; padding:12px 24px; border-radius:5px; display:inline-block; font-size:16px;">
                Ver próximos eventos
            </a>
        </td>
    </tr>

    <!-- Pie -->
    <tr>
        <td style="padding:20px; font-size:12px; color:#777777;" align="center"> 
            Síguenos en <a href="https://instagram.com/aisc_madrid" style="color:#007BFF; text-decoration:none;">Instagram</a>
            <a href="https://www.linkedin.com/company/ai-student-collective-madrid/" style="color:#007BFF; text-decoration:none;">LinkedIn</a>
            <br><br>
            <a href="https://aiscmadrid.com/processing/unsubscribe.php?token={$token}" style="color: gray; text-decoration: none; font-family: Arial, sans-serif; font-size: 12px;">Cancelar suscripción Newsletter</a>
        </td>
    </tr>

    </table>
    </body>
    </html>
    HTML;


    $mail->isHTML(true);
    $mail->Body = $htmlContent;
    $mail->AltBody = "Hola " . explode(' ', $name)[0] . ", gracias por unirte a la comunidad AISC Madrid.";

    if (!$mail->send()) {
        error_log('Mailer Error: ' . $mail->ErrorInfo);
    }

    // Mostrar HTML de confirmación
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <?php include("../assets/head.php"); ?>
    <style>
        footer{
            width: 100%;
        }
    </style>
    <body style="height:100vh">
        <div class="bg-light d-flex flex-column align-items-center justify-content-center h-100" >      
        
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
        </div>
        
        
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
