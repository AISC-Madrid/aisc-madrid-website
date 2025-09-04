<?php
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;

include("../assets/db.php");

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
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Newsletter AISC Madrid</title>
        </head>
        <body style='margin:0; padding:0; font-family: Arial, sans-serif; background-color:#f4f4f4;'>
            <table align='center' width='600'
                style='border-collapse: collapse; background-color:#ffffff; margin-top:20px; border-radius:8px; overflow:hidden;'>

                <!-- Cabecera -->
                <tr>
                    <td align='center' style='padding:20px; background-color:#EB178E; color:#ffffff;'>
                        <h1 style='margin:0; font-size:24px;'>¡Gracias por pasar por nuestro stand! </h1>
                    </td>
                </tr>

                <!-- Imagen -->
                <tr>
                    <td align='center' style='padding:20px;'>
                        <img src='https://aiscmadrid.com/images/events/event2/presentation.png'
                            alt='AISC Madrid - Jornada de Bienvenida' width='100%'
                            style='max-width:560px; border-radius:6px; display:block;'>
                    </td>
                </tr>

                <!-- Texto principal -->
                <tr>
                    <td style='padding:20px; color:#333333; font-size:16px; line-height:1.5;'>
                        <p align='center'><strong>¡Fue un placer conocerte en la Jornada de Bienvenida!</strong></p>
                            <p>Desde AISC Madrid, la primera asociación de inteligencia artificial de la EPS,
                            estamos muy ilusionados de contar con tu energía y entusiasmo.</p>
                    </td>                
                </tr>

                <tr>
                    <td align='center' style='padding:20px; color:#EB178E;'> 
                        <h1 style='margin:0; font-size:24px;'><strong>Invitación Evento Presentación AISC Madrid</strong></h1>
                    </td>
                </tr>

                <tr>
                    <td style='padding:20px; color:#333333; font-size:16px; text-align:left; line-height:1.6;'>
                        <p>
                        Nos gustaría invitarte a la <strong>presentación oficial de AISC Madrid</strong>:
                        <br>
                        Hablaremos de la proyección de la asociación, de los talleres y charlas que organizaremos, y de todas las oportunidades para que puedas involucrarte desde el primer día.
                        </p>
                        <p style='margin:8px 0;'>📍 <strong>Lugar:</strong> Aula 2.3.C02B, Edificio Sabatini EPS Universidad Carlos III</p>
                        <p style='margin:8px 0;'>📅 <strong>Fecha:</strong> 11 de septiembre de 2025</p>
                        <p style='margin:8px 0;'>⏰ <strong>Hora:</strong> 13:30h</p>
                    </td>
                </tr>
                
                <tr>
                    <td align='center' style='padding:20px; color:#EB178E;'> 
                        <h1 style='margin:0; font-size:24px;'><strong>Próximos Eventos</strong></h1>
                    </td>
                </tr>

                <tr>
                    <td style='padding:20px; color:#333333; font-size:16px; line-height:1.5;'>
                        <p align='center'><strong>Workshops</strong></p>
                            <p><strong>Introducción a la IA & ML: Data Preprocessing</strong></p>
                            <p style='margin:8px 0;'>📍 <strong>Lugar:</strong> Aula 2.3.A03B, Edificio Sabatini EPS Universidad Carlos III</p>
                            <p style='margin:8px 0;'>📅 <strong>Fecha:</strong> 15 de septiembre de 2025</p>
                            <p style='margin:8px 0;'>⏰ <strong>Hora:</strong> 14:00h</p>
                        <p>Pincha el botón para más información!</p>
                    </td>                
                </tr>


                <!-- Botón -->
                <tr>
                    <td align='center' style='padding:20px;'>
                        <a href='https://aiscmadrid.com/#events'
                            style='background-color:#20CCF1; color:#ffffff; text-decoration:none; padding:12px 24px; border-radius:5px; display:inline-block; font-size:16px;'>
                            Ver próximos eventos
                        </a>
                    </td>
                </tr>

                <!-- Pie -->
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
            error_log("Error enviando a $email: " . $mail->ErrorInfo);
        } else {
            echo "Correo enviado a $email<br>";
        }
    }
} else {
    echo "No hay usuarios suscritos a la newsletter.";
}

$conn->close();
