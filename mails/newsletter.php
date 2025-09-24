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
if (!isset($_SESSION['activated']) || $_SESSION['role'] !== 'admin') {
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
                    <h1 style='margin:0; font-size:24px;'> Hugging Face estará en el campus y Cambio de fecha en nuestro próximo taller
 </h1>
                </td>
            </tr>

            <!-- Image -->
            <tr>
                <td align='center' style='padding:20px;'>
                    <!-- Substitute by image path -->
                    <img src='https://aiscmadrid.com/images/events/event6/Sergio Paniego Hugging Face.png'
                        alt='AISC Madrid - Jornada de Bienvenida' width='80%'
                        style='max-width:560px; border-radius:6px; display:block;'>
                </td>
            </tr>

            <!-- Main text -->
            <tr>
                <td style='padding:20px; color:#333333; font-size:16px; line-height:1.5;'>
                    <p align='center'><strong>Hola " . explode(' ', $full_name)[0] . ", en esta newsletter te contamos los próximos eventos con empresas como Hugging Face o Milfshakes y un <span style = 'color:#EB178E'> cambio de día </span> en nuestro próximo taller: Introducción a la IA & ML II: Regresión. </strong></p>
                </td>                
            </tr>

            <tr>
                <td align='center' style='padding:20px; color:#EB178E;'> 
                    <h1 style='margin:0; font-size:24px;'><strong>Cambio de fecha en el taller Introducción a la IA & ML II: Regresión
                    </strong></h1>
                    <div style='margin-top:10px; width:80px; height:4px; background-color:#EB178E; border-radius:2px;'></div>
                </td>
            </tr>

            <tr>
                <td style='padding:20px; color:#333333; font-size:16px; text-align:left; line-height:1.6;'>
                    <p>
                        Debido a Foroempleo, hemos decidido cambiar la fecha del próximo taller.
                        <strong><span style = 'color:#EB178E'>Nueva fecha:</span></strong>
                        <p style='margin:8px 0;'>📅 <strong>Fecha:</strong> 30 de septiembre de 2025</p>
                        <p style='margin:8px 0;'>⏰ <strong>Hora:</strong> 13:30h - 14:15h</p>
                        <p style='margin:8px 0;'>📍 <strong>Lugar:</strong> Aula 4.0.E02, Edificio Torres Quevedo EPS Universidad Carlos III</p>
                </td>
            </tr>
            
            <tr>
                <td align='center' style='padding:20px; color:#EB178E;'> 
                    <h1 style='margin:0; font-size:24px;'><strong>Próximamente en AISC</strong></h1>
                    <div style='margin-top:10px; width:80px; height:4px; background-color:#EB178E; border-radius:2px;'></div>
                </td>
            </tr>

            <!-- Workshops section -->
            <tr>
                <td style='padding:20px; color:#333333; font-size:16px; line-height:1.5;'>
                    <p align='center'><strong>Workshops</strong></p>
                        <p><strong>Introducción a la IA & ML II: Regresión</strong>
                        <br><span style = 'color:#EB178E'> Cambio de fecha: </span>
                        </p>

                        <p style='margin:8px 0;'>📅 <strong>Fecha:</strong> 30 de septiembre de 2025</p>
                        <p style='margin:8px 0;'>⏰ <strong>Hora:</strong> 13:30h - 14:15h</p>
                        <p style='margin:8px 0;'>📍 <strong>Lugar:</strong> Aula 4.0.E02, Edificio Torres Quevedo EPS Universidad Carlos III</p>

                        <p><strong>Introducción a la IA & ML III: Redes Neuronales</strong></p>
                        <p style='margin:8px 0;'>📅 <strong>Fecha:</strong> 14 de octubre de 2025</p>
                        <p style='margin:8px 0;'>⏰ <strong>Hora:</strong> 13:30h - 14:15h</p>
                        <p style='margin:8px 0;'>📍 <strong>Lugar:</strong> Aula 2.3.A03B, Edificio Sabatini EPS Universidad Carlos III</p>
                </td>                
            </tr>

            <!-- Events section -->
            <tr>
                <td style='padding:20px; color:#333333; font-size:16px; line-height:1.5;'>
                    <p align='center'><strong>Eventos</strong></p>
                        <p><strong>🤗 Hugging Face AISC: Sergio Paniego ML Engineer</strong></p>
                        <p style='margin:8px 0;'>📅 <strong>Fecha:</strong> 22 de octubre de 2025</p>
                        <p style='margin:8px 0;'>⏰ <strong>Hora:</strong> 13:30h - 14:30h</p>
                        <p style='margin:8px 0;'>📍 <strong>Lugar:</strong> Aula 4.1.E04, Edificio Torres Quevedo EPS Universidad Carlos III</p>

                        <p><strong>🥤 Milfshakes AISC: Enrique Alcocer Software Developer </strong></p>
                        <p style='margin:8px 0;'>📅 <strong>Fecha:</strong> 28 de octubre de 2025</p>
                        <p style='margin:8px 0;'>⏰ <strong>Hora:</strong> 13:30 - 14:30h</p>
                        <p style='margin:8px 0;'>📍 <strong>Lugar:</strong> Aula por confimar</p>
                </td>                
            </tr>


            <!-- Button -->
            <tr>
                <td align='center' style='padding:20px;'>
                    <a href='https://aiscmadrid.com/#events'
                        style='background-color:#20CCF1; color:#ffffff; text-decoration:none; padding:12px 24px; border-radius:5px; display:inline-block; font-size:16px;'
                        target = '_blank'>
                        Ver próximos eventos
                    </a>
                </td>
            </tr>

                    <tr>
            <td style='padding:20px; color:#333333; font-size:16p; line-height:1.5;'> 
                <p>Para <strong>enterarte de todos los eventos, workshops y oportunidades</strong> te recomendamos que estés atento y nos sigas por:</p>
            </td>
        </tr>    

        <!-- Oportunities Section -->>
        <tr>
                <td align='center' style='padding:20px; color:#EB178E;'> 
                    <h1 style='margin:0; font-size:24px;'><strong> Oportunidades
                    </strong></h1>
                    <div style='margin-top:10px; width:80px; height:4px; background-color:#EB178E; border-radius:2px;'></div>
                </td>
            </tr>

            <tr>
                <td style='padding:20px; color:#333333; font-size:16px; line-height:1.5;'>
                        <p>
                        Os compartimos dos oportunidades muy interesantes que creemos os pueden interesar.
                        </p>
                        <p><strong>👨‍💻 Madrid AI, ML and Computer Vision Meetup</strong>
                        <br>Ponte al día con las últimas novedades en AI, ML y CV. Con ponencias de empresas como Hugging Face o Intel.
                        </p>

                        <p style='margin:8px 0;'>📅 <strong>Fecha:</strong> 26 de octubre de 2025</p>
                        <p style='margin:8px 0;'>⏰ <strong>Hora:</strong> 18:30h - 22:00h</p>
                        <p style='margin:8px 0;'>📍 <strong>Lugar:</strong> Google For Startups Campus</p>
                        <a href=' https://voxel51.com/events/madrid-ai-ml-and-computer-vision-meetup-september-26-2025'
                        style='background-color:#20CCF1; color:#ffffff; text-decoration:none; padding:12px 24px; border-radius:5px; display:inline-block; font-size:16px;'
                        target = '_blank'>
                        Más Información
                        </a>

                        <p><strong>🛰 Hackathon NASA Space Apps</strong>
                        <br>Participa en uno de los hackathones más grandes del mundo, organizado por la NASA.
                        </p>
                        <p style='margin:8px 0;'>📅 <strong>Fecha:</strong> 3, 4 y 5 de octubre 2025</p>
                        <p style='margin:8px 0;'>⏰ <strong>Hora:</strong> todo el día </p>
                        <p style='margin:8px 0;'>📍 <strong>Lugar:</strong> Escuela de Competencias Digitales - San Blas Digital</p>
                        <a href='https://www.spaceappschallenge.org/'
                        style='background-color:#20CCF1; color:#ffffff; text-decoration:none; padding:12px 24px; border-radius:5px; display:inline-block; font-size:16px;'
                        target = '_blank'>
                        Más Información
                        </a>
                </td> 
            </tr>

        <!-- Buttons Row -->
        <tr>
        <td align='center' style='padding:20px;'>
            <table border='0' cellspacing='0' cellpadding='0'>
            <tr>
                <!-- Web -->
                <td style='padding:0 5px;'>
                <a href='https://aiscmadrid.com/' target='_blank' rel='noopener noreferrer'
                    style='background-color:#333333; color:#ffffff; text-decoration:none; padding:12px 20px; border-radius:5px; display:inline-block; font-size:16px;'>
                    Nuestra Web
                </a>
                </td>
                <!-- Instagram -->
                <td style='padding:0 5px;'>
                <a href='https://www.instagram.com/aisc_madrid/' target='_blank' rel='noopener noreferrer'
                    style='background-color:#c13584; color:#ffffff; text-decoration:none; padding:12px 20px; border-radius:5px; display:inline-block; font-size:16px;'>
                    Instagram
                </a>
                </td>
                <!-- LinkedIn -->
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
        <tr>
            <td align='center' style='padding:20px;'>
                    <a href='https://aiscmadrid.com/processing/unsubscribe.php?token=' . urlencode($token) . '' style='color: gray; text-decoration: none; font-family: Arial, sans-serif; font-size: 12px;'>Cancelar suscripción Newsletter</a>
            </td>
        </tr>
        </table>
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

                <!-- Botón inicial de previsualizar -->
                <?php if (!isset($_POST['preview']) && !isset($_POST['confirm_send'])): ?>
                    <form method="post">
                        <button type="submit" name="preview" class="btn btn-primary">Previsualizar Newsletter</button>
                    </form>
                <?php endif; ?>

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
                            $mail->Subject = '¡Hugging Face viene al campus!';

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
