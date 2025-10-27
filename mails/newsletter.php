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

      <!-- Head -->
      <tr>
        <td align='center' style='padding:20px; background-color:#EB178E; color:#ffffff;'>
          <h1 style='margin:0; font-size:24px;'>...</h1>
        </td>
      </tr>

      <!-- Image -->
      <tr>
        <td align='center' style='padding:20px;'>
          <img src='https://aiscmadrid.com/images/events/event8/EnriqueAlcocerMilfshakes.png'
              alt='AISC Madrid - Milfshakes' width='80%'
              style='max-width:560px; border-radius:6px; display:block;'>
        </td>
      </tr>

      <tr>
          <td align='center' style='padding:20px;'>
          <a href='https://aiscmadrid.com/events/evento.php?id=13' target='_blank'>
              <img src='https://aiscmadrid.com/images/events/event8/EnriqueAlcocerMilfshakes.png'
                  alt='AISC Madrid - Milfshakes' width='80%'
                  style='max-width:560px; border-radius:6px; display:block;'>
        </a>
          </td>
      </tr>

      <tr>
          <tr>
              <td style='padding:20px; color:#333333; font-size:16px; line-height:1.5;'>
                  <p align='center'><strong>" . explode(' ', $full_name)[0] . ", ¿te gustaría conocer a la persona detrás del desarrollo web del e-commerce de moda: <span style = 'color:#EB178E'>Milfshakes</span> 🥤?</strong>                      
                  </p>
              </td>                    
          </tr>                
    </tr>
            
      <tr>
        <td align='center' style='padding:20px; color:#EB178E;'>
          <h2 style='margin:0; font-size:22px;'>Hugging Face</h2>
          <div style='margin-top:10px; width:80px; height:4px; background-color:#EB178E; border-radius:2px;'></div>
        </td>
      </tr>
      <tr>
        <td style='padding:20px; color:#333333; font-size:16px; line-height:1.6;'>
          <p align='center'><strong>Sergio Paniego ML Engineer | AISC x Hugging Face</strong></p>
          <p align='center'>
            ¡Así comenzó nuestro curso! 🎉<br>
            Tuvimos el placer de recibir a 
            <strong style='color:#EB178E;'>Sergio Paniego Blanco</strong>, 
            <em>Machine Learning Engineer en Hugging Face</em>, 
            quien nos compartió su experiencia profesional y nos mostró cómo la IA está transformando el desarrollo tecnológico actual.
          </p>
          <p align='center'>
            Durante la charla, descubrimos cómo se aplican los modelos de Machine Learning en proyectos reales y 
            qué competencias son esenciales para quienes quieren dedicarse a este campo.
          </p>
          <p align='center'>
            También exploramos el ecosistema de <strong style='color:#EB178E;'>Hugging Face</strong>, una plataforma abierta que reúne 
            <strong>modelos, datasets y herramientas</strong> de empresas como 
            <strong>OpenAI, NVIDIA o Microsoft</strong>.
          </p>
          <p align='center'>
            ¡Gracias a todos los asistentes por hacerlo posible!
          </p>
        </td>    
      </tr>

      <!-- Recap 2 Milfshakes -->
      <tr>
        <td align='center' style='padding:20px; color:#EB178E;'>
          <h2 style='margin:0; font-size:22px;'>Milfshakes 🥤</h2>
          <div style='margin-top:10px; width:80px; height:4px; background-color:#EB178E; border-radius:2px;'></div>
        </td>
      </tr>
      <tr>
        <td style='padding:20px; color:#333333; font-size:16px; line-height:1.6;'>
          <p align='center'><strong>...</strong></p>
          <p align='center'>
            ¡Qué gran visita tuvimos en el campus! 🎉<br>
            <strong style='color:#EB178E;'>Enrique Alcocer</strong>, 
            <em>Software Developer en Milfshakes</em>, nos acompañó para compartir su experiencia al frente del desarrollo web de 
            <strong>una de las startups más de moda en España</strong>.
          </p>
          <p align='center'>Durante la charla...</p>
          <p align='center'>
            También nos explicó en detalle, desde un punto de vista <strong>creativo y técnico</strong>, el proceso detrás de uno de los 
            <strong>drops</strong> más exitosos de la marca, mostrándonos todo lo que implica lanzar un proyecto de esa magnitud.
          </p>
          <p align='center'>
            Fue una sesión inspiradora para quienes se interesan por el <strong>desarrollo web</strong>, 
            los <strong>procesos creativos</strong> y el mundo de las <strong>startups</strong>. 
            ¡Gracias a todos los que asistieron y formaron parte de esta experiencia! 🚀
          </p>
        </td>
      </tr>

      <!-- Introducción a Git y Github -->
      <tr>
        <td align='center' style='padding:20px; color:#EB178E;'>
          <h2 style='margin:0; font-size:22px;'><strong>Introducción a Git y GitHub | AISC x GUL UC3M</strong></h2>
          <div style='margin-top:10px; width:80px; height:4px; background-color:#EB178E; border-radius:2px;'></div>
        </td>
      </tr>
      <tr>
        <td style='padding:20px; color:#333333; font-size:16px; line-height:1.6;'>
          <p align='center'>
              Enrique nos contará su experiencia al frente del desarrollo web de <strong>una de las empresas más de moda en España.</strong>
                          Cómo compaginó trabajar en una startup tan frenética con sus estudios de Ingeniería Informática, su papel a la hora
                          de transformar ideas creativas y locas en código y la importancia de la creatividad y adaptabilidad necesarias
                          para estar al frente de un proyecto como Milfshakes.
          </p>
          <p align='center'>
            Terminaremos con un <strong>ejercicio práctico</strong> en el que simularemos un trabajo en equipo: 
            crearás un repositorio, gestionarás ramas, resolverás conflictos y subirás tus aportes al remoto.  
            🔧 Todo lo necesario para desenvolverte como un profesional en cualquier empresa.
          </p>
        </td>
      </tr>
      <tr>
        <td style='padding:20px; color:#333333; font-size:16px; line-height:1.5;'>
          <p><strong>💡 Introducción a Git y GitHub | AISC x GUL UC3M</strong></p>
          <p style='margin:8px 0;'>👥 <strong>Ponentes:</strong> Hugo Centeno Sanz (AISC), Guillermo González (GUL), Albert Giurgiu (GUL)</p>
          <p style='margin:8px 0;'>📅 <strong>Fecha:</strong> 12 de noviembre de 2025</p>
          <p style='margin:8px 0;'>⏰ <strong>Hora:</strong> 13:00h - 14:30h</p>
          <p style='margin:8px 0;'>📍 <strong>Lugar:</strong> Aula 2.3.A03B, Edificio Sabatini EPS Universidad Carlos III</p>
        </td>
      </tr>

      <!-- Button -->
      <tr>
        <td align='center' style='padding:20px;'>
            <table border='0' cellspacing='0' cellpadding='0'>
            <tr>
              <td align='center' style='padding:10px 0;'>
                <a href='https://aiscmadrid.com/' target='_blank' style='margin:0 20px; display:inline-block;'>
                  <img src='https://aiscmadrid.com/images/logos/PNG/internet-rosa.png' width='32' height='32'>
                </a>
                <a href='https://www.instagram.com/aisc_madrid/' target='_blank' style='margin:0 20px; display:inline-block;'>
                  <img src='https://aiscmadrid.com/images/logos/PNG/instagram-rosa.png' width='32' height='32'>
                </a>
                <a href='https://chat.whatsapp.com/BpdXitZhwGCCpErwBoj3hv?mode=wwt' target='_blank' style='margin:0 20px; display:inline-block;'>
                  <img src='https://aiscmadrid.com/images/logos/PNG/whatsapp-rosa.png' width='32' height='32'>
                </a>
                <a href='https://www.linkedin.com/company/ai-student-collective-madrid/' target='_blank' style='margin:0 20px; display:inline-block;'>
                  <img src='https://aiscmadrid.com/images/logos/PNG/linkedin-rosa.png' width='32' height='32'>
                </a>
              </td>
            </tr>

            <tr>
              <td align='center' style='padding:10px; padding-left:40px'>
                <a href='https://aiscmadrid.com/' target='_blank'>
                  <img src='https://aiscmadrid.com/images/logos/PNG/AISCMadridLogoAndLetters.png' alt='Logo Footer' width='300'>
                </a>
              </td>
            </tr>

            <tr>
              <td align='center' style='padding:10px;'>
                <a href='https://aiscmadrid.com/processing/unsubscribe.php?token=" . urlencode($token) . "' style='color: gray; text-decoration: none; font-family: Arial, sans-serif; font-size: 12px;'>Cancelar suscripción Newsletter</a>
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
                            if (in_array($email, $excludedEmails)) {
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
                            $mail->Subject = '¡Milfshakes viene al campus!';

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
