<?php
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;

session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Not logged in, redirect to login page
    header("Location: login.php");
    exit();
}
include("../assets/db.php");

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

// Check users subscribed to the newsletter
$sql = "SELECT email, unsubscribe_token FROM form_submissions WHERE newsletter = 'yes'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {

    while ($row = $result->fetch_assoc()) {
        $email = $row['email'];
        $token = $row['unsubscribe_token'];

        // New mail for each user
        $mail = new PHPMailer;
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        $mail->SMTPDebug = 0; // cambia a 2 para ver logs detallados
        $mail->Host = 'smtp.hostinger.com';
        $mail->Port = 587;
        $mail->SMTPAuth = true;

        $config = include('../config.php');
        $mail->Username = $config['smtp_user'];
        $mail->Password = $config['smtp_pass'];
        $mail->setFrom('info@aiscmadrid.com', 'AISC Madrid');
        $mail->addReplyTo('aisc.asoc@uc3m.es', 'AISC Madrid');
        $mail->addAddress($email);
        $mail->Subject = '¡Gracias por acercarte durante la Jornada de Bienvenida!';

        // HTML content
            $htmlContent = "
    <!DOCTYPE html>
    <html>
        <head>
            <meta charset='UTF-8'>
            <title>Newsletter AISC Madrid</title>
        </head>
        <body style='margin:0; padding:0; font-family: Arial, sans-serif; background-color:#f4f4f4;'>
            <table align='center' width='600'
                style='border-collapse: collapse; background-color:#ffffff; margin-top:20px; border-radius:8px; overflow:hidden;'>

                <!-- Head -->
                <tr>
                    <td align='center' style='padding:20px; background-color:#EB178E; color:#ffffff;'>
                        <h1 style='margin:0; font-size:24px;'>Título 1 </h1>
                    </td>
                </tr>

                <!-- Image -->
                <tr>
                    <td align='center' style='padding:20px;'>
                        <!-- Substitute by image path -->
                        <img src='https://aiscmadrid.com/images/events/event2/presentation.png'
                            alt='AISC Madrid - Jornada de Bienvenida' width='100%'
                            style='max-width:560px; border-radius:6px; display:block;'>
                    </td>
                </tr>

                <!-- Main text -->
                <tr>
                    <td style='padding:20px; color:#333333; font-size:16px; line-height:1.5;'>
                        <p align='center'><strong>Subtítulo 1</strong></p>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam consectetur lacus elit, vitae posuere sapien ultricies vel.
                                Nam congue ipsum vitae tincidunt luctus.
                                Nullam consequat laoreet nibh, nec sodales augue interdum sit amet</p>
                    </td>                
                </tr>

                <tr>
                    <td align='center' style='padding:20px; color:#EB178E;'> 
                        <h1 style='margin:0; font-size:24px;'><strong>Tema 1</strong></h1>
                    </td>
                </tr>

                <tr>
                    <td style='padding:20px; color:#333333; font-size:16px; text-align:left; line-height:1.6;'>
                        <p>
                            Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                            Nullam consectetur lacus elit, vitae posuere sapien ultricies vel.
                            Nam congue ipsum vitae tincidunt luctus. <strong>Aliquam faucibus pretium nunc,</strong> dapibus eleifend ipsum ullamcorper eget.
                            In hac habitasse platea dictumst. 
                            <p style='margin:8px 0;'>📍 <strong>Lugar:</strong> Aula ejemplo, Edificio nombre EPS Universidad Carlos III</p>
                            <p style='margin:8px 0;'>📅 <strong>Fecha:</strong> día de mes de año</p>
                            <p style='margin:8px 0;'>⏰ <strong>Hora:</strong> 00:00h</p>
                    </td>
                </tr>
                
                <tr>
                    <td align='center' style='padding:20px; color:#EB178E;'> 
                        <h1 style='margin:0; font-size:24px;'><strong>Próximamente en AISC</strong></h1>
                    </td>
                </tr>

                <!-- Events section -->
                <tr>
                    <td style='padding:20px; color:#333333; font-size:16px; line-height:1.5;'>
                        <p align='center'><strong>Eventos</strong></p>
                            <p><strong>Título Evento 1</strong></p>
                            <p style='margin:8px 0;'>📍 <strong>Lugar:</strong> Aula ejemplo, Edificio nombre EPS Universidad Carlos III</p>
                            <p style='margin:8px 0;'>📅 <strong>Fecha:</strong> día de mes de año</p>
                            <p style='margin:8px 0;'>⏰ <strong>Hora:</strong> 00:00h</p>

                            <p><strong>Título Evento 2</strong></p>
                            <p style='margin:8px 0;'>📍 <strong>Lugar:</strong> Aula ejemplo, Edificio nombre EPS Universidad Carlos III</p>
                            <p style='margin:8px 0;'>📅 <strong>Fecha:</strong> día de mes de año</p>
                            <p style='margin:8px 0;'>⏰ <strong>Hora:</strong> 00:00h</p>
                    </td>                
                </tr>
                
                <!-- Workshops section -->
                <tr>
                    <td style='padding:20px; color:#333333; font-size:16px; line-height:1.5;'>
                        <p align='center'><strong>Workshops</strong></p>
                            <p><strong>Título Workshop 1</strong></p>
                            <p style='margin:8px 0;'>📍 <strong>Lugar:</strong> Aula ejemplo, Edificio nombre EPS Universidad Carlos III</p>
                            <p style='margin:8px 0;'>📅 <strong>Fecha:</strong> día de mes de año</p>
                            <p style='margin:8px 0;'>⏰ <strong>Hora:</strong> 00:00h</p>

                            <p><strong>Título Workshop 2</strong></p>
                            <p style='margin:8px 0;'>📍 <strong>Lugar:</strong> Aula ejemplo, Edificio nombre EPS Universidad Carlos III</p>
                            <p style='margin:8px 0;'>📅 <strong>Fecha:</strong> día de mes de año</p>
                            <p style='margin:8px 0;'>⏰ <strong>Hora:</strong> 00:00h</p>
                        <p>Pincha el botón para más información!</p>
                    </td>                
                </tr>


                <!-- Button -->
                <tr>
                    <td align='center' style='padding:20px;'>
                        <a href='https://aiscmadrid.com/#events'
                            style='background-color:#20CCF1; color:#ffffff; text-decoration:none; padding:12px 24px; border-radius:5px; display:inline-block; font-size:16px;'>
                            Ver próximos eventos
                        </a>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style='padding:20px; font-size:12px; color:#777777;' align='center'> 
                            Síguenos en <a href='https://instagram.com/aisc_madrid'
                            style='color:#007BFF; text-decoration:none;'>Instagram</a>
                            <a href='https://www.linkedin.com/company/ai-student-collective-madrid/'
                            style='color:#007BFF; text-decoration:none;'>LinkedIn</a>
            <br><br>
                        <a href='https://aiscmadrid.com/processing/unsubscribe.php?token=' . urlencode($token) . '' style='color: gray; text-decoration: none; font-family: Arial, sans-serif; font-size: 12px;'>Cancelar suscripción Newsletter</a>
                    </td>
                </tr>
            </table>
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
