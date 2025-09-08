<?php
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;

include("../assets/db.php");

// Load SMTP config once
$config = include('../config.php');

// Step 1: Show button if page loaded without sending
if (!isset($_POST['send_emails'])) {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Enviar Newsletter</title>
    </head>
    <body style="font-family: Arial, sans-serif; text-align:center; margin-top:50px;">
        <h2>Enviar newsletter a suscriptores</h2>
        <form method="post">
            <button type="submit" name="send_emails" 
                    style="padding:10px 20px; font-size:16px; cursor:pointer;">
                Enviar Emails
            </button>
        </form>
    </body>
    </html>
    <?php
    exit;
}

// Step 2: Only runs when button clicked
$sql = "SELECT email, unsubscribe_token FROM form_submissions WHERE newsletter = 'yes'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<h3>Enviando emails...</h3>";
    ob_flush(); flush(); // show output progressively

    while ($row = $result->fetch_assoc()) {
        $email = $row['email'];
        $token = $row['unsubscribe_token'];

        // New mail for each user
        $mail = new PHPMailer;
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        $mail->SMTPDebug = 0;
        $mail->Host = 'smtp.hostinger.com';
        $mail->Port = 587;
        $mail->SMTPAuth = true;
        $mail->Username = $config['smtp_user'];
        $mail->Password = $config['smtp_pass'];
        $mail->setFrom('info@aiscmadrid.com', 'AISC Madrid');
        $mail->addReplyTo('aisc.asoc@uc3m.es', 'AISC Madrid');
        $mail->addAddress($email);
        $mail->Subject = '¡Gracias por acercarte durante la Jornada de Bienvenida!';

        // Add unsubscribe link correctly
        $unsubscribeLink = "https://aiscmadrid.com/processing/unsubscribe.php?token=" . urlencode($token);

        $htmlContent = "
        <!DOCTYPE html>
        <html>
        <body>
            <h2>Ejemplo de newsletter</h2>
            <p>Hola, gracias por participar en AISC Madrid!</p>
            <p><a href='$unsubscribeLink' 
                  style='color: gray; font-size: 12px;'>Cancelar suscripción Newsletter</a></p>
        </body>
        </html>";

        $mail->isHTML(true);
        $mail->Body = $htmlContent;

        if (!$mail->send()) {
            echo "<p style='color:red;'>❌ Error enviando a $email: " . $mail->ErrorInfo . "</p>";
        } else {
            echo "<p style='color:green;'>✅ Correo enviado a $email</p>";
        }

        ob_flush(); flush(); // forces output per email
    }
} else {
    echo "No hay usuarios suscritos a la newsletter.";
}

$conn->close();
