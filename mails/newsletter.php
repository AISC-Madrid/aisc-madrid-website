<?php
// Turn off output buffering
while (ob_get_level()) ob_end_flush();
ini_set('output_buffering', 'off');
ini_set('zlib.output_compression', 0);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;

session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: events/login.php");
    exit();
}

include("../assets/db.php");

// Función para generar el HTML de la newsletter
function generarNewsletterHTML($full_name, $token) {
    return "
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
                    <h1 style='margin:0; font-size:24px;'>" . explode(' ', $full_name)[0] . ", así fue el Evento de Presentación </h1>
                </td>
            </tr>

            <!-- Image -->
            <tr>
                <td align='center' style='padding:20px;'>
                    <img src='https://aiscmadrid.com/images/events/event5/ConoceAISC.png'
                        alt='AISC Madrid - Evento de Presentación' width='100%'
                        style='max-width:560px; border-radius:6px; display:block;'>
                </td>
            </tr>

            <!-- Main text -->
            <tr>
                <td style='padding:20px; color:#333333; font-size:16px; line-height:1.5;'>
                    <p align='center'><strong>Estamos muy contentos con vuestro interés por la asociación.</strong></p>
                        <p>En el Evento de Presentación pudimos conoceros a todos y contaros más sobre AISC.</p>
                        <p>Gracias por darnos vuestro feedback sobre lo qué esperáis de AISC
                            y por hacernos sentir vuestro interés e ilusión por la asociación.</p>
                        <br>
                        <p>
                        ¡Os esperamos a todos en los próximos eventos y talleres! Y recordad rellenar el 
                        <a href='https://aiscmadrid.com/join.php'
                            target='_blank' rel='noopener noreferrer'>
                            formulario de interés
                        </a> si queréis
                        uniros al equipo de AISC Madrid.</p>    
                </td>                
            </tr>
            
            <tr>
                <td align='center' style='padding:20px; color:#EB178E;'> 
                    <h1 style='margin:0; font-size:24px;'><strong>Próximamente en AISC</strong></h1>
                </td>
            </tr>
            
            <!-- Workshops -->
            <tr>
                <td style='padding:20px; color:#333333; font-size:16px; line-height:1.5;'>
                    <p align='center'><strong>Workshops</strong></p>
                        <p><strong>Introducción a la IA & ML: Data Preprocessing</strong></p>
                        <p style='margin:8px 0;'>📅 <strong>Fecha:</strong> 15 de septiembre de 2025</p>
                        <p style='margin:8px 0;'>⏰ <strong>Hora:</strong> 14:00h - 14:45h</p>
                        <p style='margin:8px 0;'>📍 <strong>Lugar:</strong> Aula 2.3.A03B, Edificio Sabatini EPS Universidad Carlos III</p>

                        <p><strong>Introducción a la IA & ML: Regression</strong></p>
                        <p style='margin:8px 0;'>📅 <strong>Fecha:</strong> 2 de octubre de 2025</p>
                        <p style='margin:8px 0;'>⏰ <strong>Hora:</strong> 19:00h - 19:45h</p>
                        <p style='margin:8px 0;'>📍 <strong>Lugar:</strong> Por confirmar</p>
                </td>                
            </tr>

            <!-- Events -->
            <tr>
                <td style='padding:20px; color:#333333; font-size:16px; line-height:1.5;'>
                    <p align='center'><strong>Events</strong></p>
                        <p><strong>AISC x Hugging Face 🤗 | Sergio Paniego Blanco ML Engineer</strong></p>
                        <p style='margin:8px 0;'>📅 <strong>Fecha:</strong> 22 de octubre de 2025</p>
                        <p style='margin:8px 0;'>⏰ <strong>Hora:</strong> Por confirmar</p>
                        <p style='margin:8px 0;'>📍 <strong>Lugar:</strong> Por confirmar</p>
                        <p>Pincha el botón para más información!</p>
                </td>                
            </tr>

            <tr>
                <td style='padding:20px; color:#333333; font-size:16px; line-height:1.5;'>
                        <p><strong>AISC x Spotify | Silvia Hernández Fernández Data Engineer</strong></p>
                        <p style='margin:8px 0;'>📅 <strong>Fecha:</strong> 19 de noviembre de 2025</p>
                        <p style='margin:8px 0;'>⏰ <strong>Hora:</strong> Por confirmar</p>
                        <p style='margin:8px 0;'>📍 <strong>Lugar:</strong> Por confirmar</p>
                </td>                
            </tr>

            <tr>
                <td>
                    <p>
                        <a href='https://aiscmadrid.com/#events'
                            target='_blank' rel='noopener noreferrer'>
                            Pincha para más información
                        </a>
                    </p>
                </td>
            </tr>

            <tr>
                <td align='center' style='padding:20px; color:#EB178E;'> 
                    <h1 style='margin:0; font-size:24px;'><strong>Únete al equipo de AISC Madrid</strong></h1>
                </td>
            </tr>

            <tr>
                <td style='padding:20px; color:#333333; font-size:16px; text-align:left; line-height:1.6;'>
                    <p>Si además de asistir a los talleres y los eventos quieres formar parte activamente de la asociación, estamos buscando nuevos miembros para nuestro equipo.
                        Buscamos todo tipo de perfiles, tanto técnicos como no técnicos, con más y menos experiencia.
                    
                        <p style='margin:8px 0;'>📣 <strong>Redes Sociales, Diseño y Marketing</strong></p>
                        <p style='margin:8px 0;'>💻 <strong>Desarrollo Web</strong></p>
                        <p style='margin:8px 0;'>👥 <strong>Eventos y Talleres</strong></p>
                        <br>
                        <p>
                            <a href='https://aiscmadrid.com/join.php'
                                target='_blank' rel='noopener noreferrer'>
                                Aplica aquí para unirte al equipo.
                            </a>
                        </p>
                </td>
            </tr>

        <!-- Botones -->
        <tr>
        <td align='center' style='padding:20px;'>
            <table border='0' cellspacing='0' cellpadding='0'>
            <tr>
                <td style='padding:0 5px;'>
                <a href='https://chat.whatsapp.com/BpdXitZhwGCCpErwBoj3hv?mode=ac_t' target='_blank' rel='noopener noreferrer'
                    style='background-color:#25d366; color:#ffffff; text-decoration:none; padding:12px 20px; border-radius:5px; display:inline-block; font-size:16px;'>
                    Únete a la comunidad de Whatsapp
                </a>
                </td>
                <td style='padding:0 5px;'>
                <a href='https://www.instagram.com/aisc_madrid/' target='_blank' rel='noopener noreferrer'
                    style='background-color:#c13584; color:#ffffff; text-decoration:none; padding:12px 20px; border-radius:5px; display:inline-block; font-size:16px;'>
                    Instagram
                </a>
                </td>
                <td style='padding:0 5px;'>
                <a href='https://www.linkedin.com/company/ai-student-collective-madrid/' target='_blank' rel='noopener noreferrer'
                    style='background-color:#0077B5; color:#ffffff; text-decoration:none; padding:12px 20px; border-radius:5px; display:inline-block; font-size:16px;'>
                    LinkedIn
                </a>
                </td>
            </tr>
            </table>
        </td>
        </tr>

        <!-- Footer -->
        <tr> 
        <td style='padding:20px; font-size:12px; color:#777777;' align='center'> 
            <a href='https://aiscmadrid.com/processing/unsubscribe.php?token=" . urlencode($token) . "' style='color: gray; text-decoration: none; font-family: Arial, sans-serif; font-size: 12px;'>
                Cancelar suscripción Newsletter</a>
         </td> 
        </tr>

    </body>
</html>";
}
?>
<!DOCTYPE html>
<html lang="es">
<?php include("../assets/head.php"); ?>
<body>
<?php include("../dashboard/dashboard_nav.php"); ?>

<div class="container-fluid" style="margin-top:90px;">
    <div class="row">
        <main class="col-12 px-md-4 py-4">
            <h2 class="mb-4 text-dark">Enviar Newsletter</h2>
            <div class="border rounded bg-white p-3">
                <?php

                // Newsletter Preview
                if (isset($_POST['preview'])) {
                    $htmlPreview = generarNewsletterHTML("Miembro AISC", "previewtoken123");
                    echo "<h4 class='text-success'>Vista previa de la Newsletter:</h4>";
                   echo '<iframe srcdoc="' . htmlspecialchars($htmlPreview, ENT_QUOTES) . '" 
                        style="width:100%; height:600px; border:1px solid #ccc;">
                    </iframe>';

                    ?>
                    <form method="post">
                        <input type="hidden" name="confirm_send" value="1">
                        <button type="submit" class="btn btn-danger">
                            Confirmar y Enviar a todos los suscriptores
                        </button>
                    </form>
                    <?php
                }

                // Send newsletter
                if (isset($_POST['confirm_send'])) {
                    $sql = "SELECT full_name, email, unsubscribe_token FROM form_submissions WHERE newsletter = 'yes'";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $full_name = $row['full_name'];
                            $email = $row['email'];
                            $token = $row['unsubscribe_token'];

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
                            $mail->addAddress($email);
                            $mail->Subject = '¡Así fue el Evento de Presentación!';

                            $mail->isHTML(true);
                            $mail->Body = generarNewsletterHTML($full_name, $token);

                            if (!$mail->send()) {
                                echo "<p class='text-danger'>Error enviando a $email: {$mail->ErrorInfo}</p>";
                            } else {
                                echo "<p class='text-success'>Correo enviado a $email</p>";
                            }
                            if (ob_get_level()) ob_flush();
                            flush();
                        }
                    } else {
                        echo "<p>No hay usuarios suscritos a la newsletter.</p>";
                    }
                    $conn->close();
                }
                ?>
            </div>
        </main>
    </div>
</div>

<?php include('../assets/footer.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
