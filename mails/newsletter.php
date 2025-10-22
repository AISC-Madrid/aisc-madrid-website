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
    <table align='center' width='600' style='border-collapse: collapse; background-color:#ffffff; margin-top:20px; border-radius:8px; overflow:hidden;'>

      <tr>
        <td align='center' style='padding:20px; background-color:#EB178E; color:#ffffff;'>
          <h1 style='margin:0; font-size:24px;'>Milfshakes x AISC Madrid</h1>
        </td>
      </tr>

      <tr>
          <td align='center' style='padding:20px;'>
              <img src='https://aiscmadrid.com/images/events/event8/EnriqueAlcocerMilfshakes.png'
                  alt='AISC Madrid - Milfshakes' width='80%'
                  style='max-width:560px; border-radius:6px; display:block;'>
          </td>
      </tr>

      <tr>
          <tr>
              <td style='padding:20px; color:#333333; font-size:16px; line-height:1.5;'>
                  <p align='center'><strong>Hola " . explode(' ', $full_name)[0] . ", la semana que viene es nuestro segundo evento, y ¡tenemos muchas ganas!
                      <br>
                      Conoce a Enrique Alcocer, la persona detrás del desarrollo web del e-commerce de moda: <span style = 'color:#EB178E'>Milfshakes</span> 🥤                      
                  </p>
              </td>                    
          </tr>                
    </tr>
            
      <tr>
        <td align='center' style='padding:20px; color:#EB178E;'>
          <h2 style='margin:0; font-size:22px;'><strong>Enrique Alcocer, Software Developer en Milfshakes 🥤</strong></h2>
          <div style='margin-top:10px; width:80px; height:4px; background-color:#EB178E; border-radius:2px;'></div>
        </td>
      </tr>
      <tr>
        <td style='padding:20px; color:#333333; font-size:16px; line-height:1.6;'>
          <p align='center'>
              ¡Comenzamos el curso con una visita muy especial! 🎉
              <br>  
              En nuestro primer evento del año recibimos a 
              <strong style='color:#EB178E;'>Sergio Paniego Blanco</strong>, 
              <em>Machine Learning Engineer en Hugging Face</em>.
          </p>
          <p align='center'>
              Enrique nos contará su experiencia al frente del desarrollo web de <strong>una de las empresas más de moda en España.</strong>
                          Cómo compaginó trabajar en una startup tan frenética con sus estudios de Ingeniería Informática, su papel a la hora
                          de transformar ideas creativas y locas en código y la importancia de la creatividad y adaptabilidad necesarias
                          para estar al frente de un proyecto como Milfshakes.
          </p>
          <p align='center'>
              Además, Enrique nos contará en detalle <strong>creativo y técnico</strong> cómo fue el paso a paso para el lanzamiento de unos de los <strong>drops</strong> de la marca que más impacto tuvo.
          </p>
          <p align='center'>
              Si te interesa el <strong>desarrollo web, los procesos creativos, el mundo de las startups o si eres fan de Milfshakes</strong>, ¡este evento es para ti!
                  </p>
        </td>
      </tr>

      <tr>
          <td style='padding:20px; color:#333333; font-size:16px; line-height:1.5;'>
                  <p><strong>🥤 Milfshakes AISC: Enrique Alcocer Software developer</strong></p>
                  <p style='margin:8px 0;'>📅 <strong>Fecha:</strong> 28 de octubre de 2025</p>
                  <p style='margin:8px 0;'>⏰ <strong>Hora:</strong> 13:30h - 14:30h</p>
                  <p style='margin:8px 0;'>📍 <strong>Lugar:</strong> Aula 4.1.E04, Edificio Torres Quevedo EPS Universidad Carlos III</p>
          </td>      
      </tr>

  <tr>
              <td align='center' style='padding:20px;'>
                  <a href='https://aiscmadrid.com/events/event_registration.php?id=13'
                      style='background-color:#EB178E; color:#000000; text-decoration:none; padding:12px 24px; border-radius:5px; display:inline-block; font-size:16px; font-weight: bold;'
                      target = '_blank'>
                      ¡Regístrate para el evento aquí!
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
                    <a href= 'https://aiscmadrid.com/processing/unsubscribe.php?token=" . urlencode($token) . "' style='color: gray; text-decoration: none; font-family: Arial, sans-serif; font-size: 12px;'>Cancelar suscripción Newsletter</a>
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
                            /* Skip emails that did not fail in the last newsletter
                            if (!in_array($email, $failedEmails)) {
                                continue;
                            }  */


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
