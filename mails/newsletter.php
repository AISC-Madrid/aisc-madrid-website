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
    <title>Newsletter - AISC Madrid</title>
  </head>
  <body style='margin:0; padding:0; font-family: Arial, sans-serif; background-color:#f4f4f4;'>
    <table align='center' width='600' style='border-collapse: collapse; background-color:#ffffff; margin-top:20px; border-radius:8px; overflow:hidden;'>

      <!-- Head -->
      <tr>
        <td align='center' style='padding:20px; background-color:#EB178E; color:#ffffff;'>
          <h1 style='margin:0; font-size:24px;'>Newsletter AISC Madrid</h1>
        </td>
      </tr>

<!-- Images Row -->
<tr>
  <td align='center' style='padding:20px;'>
    <!-- Sub-table with two images side by side -->
    <table role='presentation' border='0' cellpadding='0' cellspacing='0' align='center'>
      <tr>
        <td align='center' style='padding:10px;'>
          <img src='https://www.aiscmadrid.com/images/events/event6/Sergio_speaking.png'
               alt='Sergio Paniego - Hugging Face'
               style='max-width:350px; border-radius:6px; display:block;'>
        </td>
        <td align='center' style='padding:10px;'>
          <img src='https://www.aiscmadrid.com/images/events/event8/Enrique_speaking.png'
               alt='Enrique Alcocer - MilfShakes'
               style='max-width:350px; border-radius:6px; display:block;'>
        </td>
      </tr>
    </table>
  </td>
</tr>

        <tr>
            <tr>
                <td style='padding:20px; color:#333333; font-size:16px; line-height:1.5;'>

                    <p align='center'><strong>Hola " . explode(' ', $full_name)[0] . " , en esta newsletter te contamos cómo fueron los eventos con Hugging Face y Milfshakes.</strong></p>  
                    <p align='center'><strong>También informamos de que buscamos una persona para Redes Sociales, Diseño y Marketing.</strong></p>   
                </td>                
            </tr>              
      </tr>

      <!-- Recap 1 Hugging Face -->
        <tr>
        <td align='center' style='padding:20px; color:#EB178E;'>
            <h2 style='margin:0; font-size:22px;'>Sergio Paniego | Hugging Face🤗</h2>
            <div style='margin-top:10px; width:80px; height:4px; background-color:#EB178E; border-radius:2px;'></div>
        </td>
        </tr>
        <tr>
        <td style='padding:20px; color:#333333; font-size:16px; line-height:1.6;'>
            <p align='center'><strong>Sergio Paniego ML Engineer @Hugging Face</strong></p>
            <p align='left'>
            Como primer evento de la asociación tuvimos el placer de recibir a 
            <strong style='color:#EB178E;'>Sergio Paniego Blanco</strong>, 
            <em>Machine Learning Engineer en Hugging Face</em>, 
            quien nos compartió su experiencia profesional y nos mostró cómo la IA está transformando el desarrollo tecnológico actual.
            </p>
            <p align='left'>
              Dio su visión sobre el potencial de los modelos abiertos y nos contó su implicación en proyectos como <strong>TRL o Transformers.</strong>
            </p>

            <p align='left'>
                <a href = 'https://aiscmadrid.com/events/evento.php?id=11' style='color:#EB178E;' target='_blank'>
                ➡️¡Echa un vistazo al vídeo y a los recursos si te lo perdiste!
                </a>
            </p>
        </td>    
        </tr>

      <!-- Recap 2 Milfshakes -->
        <tr>
        <td align='center' style='padding:20px; color:#EB178E;'>
            <h2 style='margin:0; font-size:22px;'>Enrique Alcocer | Milfshakes 🥤</h2>
            <div style='margin-top:10px; width:80px; height:4px; background-color:#EB178E; border-radius:2px;'></div>
        </td>
        </tr>

        <tr>
        <td style='padding:20px; color:#333333; font-size:16px; line-height:1.6;'>
            <p align='center'><strong>Enrique Alcocer Software Developer @Milfshakes 🥤</strong></p>
            <strong style='color:#EB178E;'>Enrique Alcocer</strong>, 
            <em>Software Developer en Milfshakes</em>, nos acompañó para compartir su experiencia al frente del desarrollo web de 
            <strong>una de las startups más de moda en España</strong>.
            </p>
            <p align='left'>
            Enrique habló de su experiencia como desarrollador, su stack de tecnologías actuales y su visión sobre el futuro de la <strong>UX/UI.</strong> Haciendo hincapié en <strong>Three.js</strong>
            </p>
            <p align='left'>
            También nos explicó en detalle, desde un punto de vista <strong>creativo y técnico</strong>, el proceso de trabajo para los lanzamientos de drops como 
            <strong>Psycho Milfo o la subasta inversa del cuadro</strong>.
            </p>
            <p align='left'>
                <a href = 'https://aiscmadrid.com/events/evento.php?id=13' style='color:#EB178E;' target='_blank'>
                ➡️¡Echa un vistazo al vídeo y a los recursos si te lo perdiste!
                </a>
            </p>
        </td>
        </tr>

        <tr>
        <td align='center' style='padding:20px; color:#EB178E;'>
            <h2 style='margin:0; font-size:22px;'>¡Únete a nuestro equipo!</h2>
            <div style='margin-top:10px; width:80px; height:4px; background-color:#EB178E; border-radius:2px;'></div>
        </td>
        </tr>
        <tr>
            <td style='padding:20px; color:#333333; font-size:16px; line-height:1.6;'>
            <p align='center'><strong>Buscamos una persona para el rol de Redes Sociales, Diseño y Marketing</strong></p>
            <p align='left'>
            Impulsa nuestra presencia con contenido, branding y materiales para eventos.
            <em>Se valora positivamente:</em>
            <ul>
                <li>Creatividad e iniciativa</li>
                <li>Gestión de Instagram/LinkedIn/Comunidad de Whatsapp</li>
                <li>Edición foto/vídeo</li>
            </ul>
            <p align='left'>
                <a href = 'mailto:aisc.asoc@uc3m.es' style='color:#EB178E;' target='_blank'>
                ➡️Mándanos un mail si te interesa: aisc.asoc@uc3m.es
                </a>


            </p>
            </td>
        </tr>

      
      <!-- Newsletter Footer -->
            <tr>
                <td align='center' style='padding:0 20px;'>
                    <table role='presentation' width='550' cellpadding='0' cellspacing='0' align = 'center' border='0'
                        style='border-top:5px solid #EB178E; margin-top:20px; padding-top:20px; font-family:Arial, sans-serif; font-size:14px; color:#555555;'>
                        <tr>
                            <td style='padding:0px; color:#333333; font-size:16px; line-height:1.5;'>
                                <p>Para <strong>enterarte de todos los eventos, workshops y oportunidades</strong> te recomendamos que estés atento y nos sigas por:</p>
                            </td>
                        </tr>
                        <tr>
                            <td align='center' style='padding:10px 0;'>
                            <!-- Web -->
                            <a href='https://aiscmadrid.com/' target='_blank' style='margin:0 20px; display:inline-block;'>

                                <img src='https://aiscmadrid.com/images/logos/PNG/internet-rosa.png' alt='Button 1' width='32' height='32' border='0' style='display:block;'>
                            </a>
                            <!-- Instagram -->
                            <a href='https://www.instagram.com/aisc_madrid/' target='_blank' style='margin:0 20px; display:inline-block;'>
                                <img src='https://aiscmadrid.com/images/logos/PNG/instagram-rosa.png' alt='Button 2' width='32' height='32' border='0' style='display:block;'>
                            </a>
                            <!-- WhatsApp -->
                            <a href='https://chat.whatsapp.com/BpdXitZhwGCCpErwBoj3hv?mode=wwt' target='_blank' style='margin:0 20px; display:inline-block;'>
                                <img src='https://aiscmadrid.com/images/logos/PNG/whatsapp-rosa.png' width='32' height='32'>
                            </a>
                            <!-- LinkedIn -->
                            <a href='https://www.linkedin.com/company/ai-student-collective-madrid/' target='_blank' style='margin:0 20px; display:inline-block;'>
                                <img src='https://aiscmadrid.com/images/logos/PNG/linkedin-rosa.png' alt='Button 3' width='32' height='32' border='0' style='display:block;'>
                            </a>
                            </td>
                        </tr>
                        <!-- Logo footer-->
                        <tr>
                            <td align='center' style='padding:10px; padding-left:40px'>
                                <a href='https://aiscmadrid.com/' target='_blank' style='margin:0 20px; display:inline-block;'>
                                    <img src='https://aiscmadrid.com/images/logos/PNG/AISCMadridLogoAndLetters.png' alt='Logo Footer' width='300' border='0' style='display:block;'>
                                </a>
                            </td>
                        </tr>
                                    <tr>
              <td align='center' style='color: gray; text-decoration: none; font-family: Arial, sans-serif; font-size: 12px; padding:10px;'>
                <a href='https://aiscmadrid.com/index.php#newsletter' style='color: gray; text-decoration: none; font-family: Arial, sans-serif; font-size: 12px;'>¿Te han reenviado la Newsletter? Suscríbete aquí</a>
                |
                <a href='https://aiscmadrid.com/processing/unsubscribe.php?token=token=" . urlencode($token) . "' style='color: gray; text-decoration: none; font-family: Arial, sans-serif; font-size: 12px;'>Cancelar suscripción Newsletter</a>
              </td>
            </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>

";
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
                      /* Add mails to not sent the newsletter to */
                    $excludedEmails = [
                    ];
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $full_name = $row['full_name'];
                            $email = $row['email'];
                            $token = $row['unsubscribe_token'];
                            /* Skip emails that did not fail in the last newsletter*/
                            $excludedEmails = [
                              
                            ];
                            if (!in_array($email, $excludedEmails)) {
                                continue;
                            }


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
                            $mail->Subject = 'Esta semana en AISC Madrid';

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
