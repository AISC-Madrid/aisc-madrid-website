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
          <h1 style='margin:0; font-size:24px;'>Hugging Face x AISC Madrid</h1>
        </td>
      </tr>

      <!-- Image -->
      <tr>
            <td align='center' style='padding:20px;'>
                <!-- Substitute by image path -->
                <img src='https://aiscmadrid.com/images/events/event6/SergioPaniegoHuggingFace.png'
                    alt='AISC Madrid - Hugging Face Visit' width='80%'
                    style='max-width:560px; border-radius:6px; display:block;'>
            </td>
        </tr>

        <tr>
            <tr>
                <td style='padding:20px; color:#333333; font-size:16px; line-height:1.5;'>
                    <p align='center'><strong>Hola " . explode(' ', $full_name)[0] . ", la semana que viene es nuestro primer evento, y ¡tenemos muchas ganas!
                      <br>
                      Descubre el trabajo de un <span style = 'color:#EB178E'>ML Engineer</span> y la alternativa abierta
                      para el mundo de la IA con la visita de Sergio Paniego Blanco, ML Engineer @Hugging Face
                    </p>
                </td>                
            </tr>              
      </tr>
            
      <!-- Sección 1: Promo Hugging Face -->
      <tr>
        <td align='center' style='padding:20px; color:#EB178E;'>
          <h2 style='margin:0; font-size:22px;'><strong>¡Eventazo con Hugging Face 🤗!</strong></h2>
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
          <p align='left'>
              Durante la charla, exploraremos el día a día de un ingeniero de Machine Learning:
              cómo trabaja, qué retos afronta y qué habilidades son esenciales para 
              desenvolverse en el sector de la inteligencia artificial.
          </p>
          <p align='left'>
              De la mano de Sergio descubriremos <strong style='color:#EB178E;'>Hugging Face</strong>,  un ecosistema con miles de modelos,
              datasets y herramientas accesibles de forma abierta. Entre su amplio catálogo se encuentran los mejores modelos del mundo, como los desarrollados por <strong>OpenAI, NVIDIA, Microsoft, IBM o DeepSeek</strong> y otros líderes del sector.
          </p>
          <p align='left'>
              Cerraremos la sesión con una <strong>demostración práctica</strong> en la que Sergio 
              mostrará lo sencillo que es empezar a crear con <strong>inteligencia artificial</strong> gracias al ecosistema de Hugging Face.
          </p>
        </td>    
      </tr>

      <!-- Sección 2: FIN SERIE INTRO TO AI & ML -->
      <tr>
        <td align='center' style='padding:20px; color:#EB178E;'>
          <h2 style='margin:0; font-size:22px;'><strong>Fin de la serie Intro to AI & ML</strong></h2>
          <div style='margin-top:10px; width:80px; height:4px; background-color:#EB178E; border-radius:2px;'></div>
        </td>
      </tr>
      <tr>
        <td style='padding:20px; color:#333333; font-size:16px; line-height:1.6;'>
          <p align='left'>
            Durante los meses de <strong>septiembre y octubre</strong>, la asociación ha celebrado tres 
            <strong>workshops introductorios</strong> sobre <em>Inteligencia Artificial</em> y 
            <em>Machine Learning</em>.
            </p>

          <p align='left'>
            <ul>
              <li><strong style='color:#EB178E;'>I Data Preprocessing:</strong> aprendimos ideas básicas de limpieza de datos y EDA (Exploratory Data Analysis) con <strong>Numpy, Pandas, Matplotlib y Seaborn</strong></li>
              <li><strong style='color:#EB178E;'>II Regression:</strong></li>
              <li><strong style='color:#EB178E;'>III Neural Networks:</strong> entendimos conceptos como forward pass, back-propagation o qué son las funciones de activación. Además hicimos nuestra primer Red Neuronal de 0 con <strong>Numpy</strong> y la comparamos con una hecha con <strong>PyTorch</strong></li>
            </ul>
            </p>

          <p align='left'>
            Con esta serie damos por finalizada la serie
            <strong>Introducción a la IA & ML.</strong>
            Si te perdiste alguno de los talleres, recuerda que tienes todos los recursos en la web.
            <br> 
            <a href='https://aiscmadrid.com/events/evento.php?id=9' target='_blank'>Accede a los recursos</a>
            </p>
                        <!-- Button -->
            <tr>
                <td align='center' style='padding:20px;'>
                    <a href='https://aiscmadrid.com#events  '
                        style='background-color:#20CCF1; color:#ffffff; text-decoration:none; padding:12px 24px; border-radius:5px; display:inline-block; font-size:16px;'
                        target = '_blank'>
                        ¡No te pierdas los siguientes!
                    </a>
                </td>
            </tr>
            <tr>
              <td align='center' style='padding:20px;'>
                <!-- Substitute by image path -->
                <img src='https://aiscmadrid.com/images/events/event7/Workshop.png'
                    alt='AISC Madrid - Hugging Face Visit' width='80%'
                    style='max-width:560px; border-radius:6px; display:block;'>
              </td> 
            </tr>
        </td>
      </tr>

      <!-- SECCIÓN 3: BUSCAMOS UNA PERSONA PARA REDES -->
      <tr>
        <td align='center' style='padding:20px; color:#EB178E;'>
          <h2 style='margin:0; font-size:22px;'><strong>¡Estamos buscando a un nuevo miembro!</strong></h2>
          <div style='margin-top:10px; width:80px; height:4px; background-color:#EB178E; border-radius:2px;'></div>
        </td>
      </tr>
      <tr>
        <td style='padding:20px; color:#333333; font-size:16px; line-height:1.6;'>
          <p><strong> Buscamos una persona para 📣 Redes Sociales, Diseño y Marketing</strong></p>
          <p align='left'>
            Impulsa nuestra presencia con <strong>contenido, branding y materiales</strong> para eventos.
            </p>

            <p align='left'>
            <em>Se valora positivamente:</em>
            </p>
            <ul align='left'>
            <li><em>Creatividad e iniciativa</em></li>
            <li><em>Gestión de Instagram / LinkedIn / Comunidad de WhatsApp</em></li>
            <li><em>Edición foto / vídeo</em></li>
            </ul>
          <p>Si te interesa, envía un correo a:<strong style='color:#EB178E;'>aisc.asoc@uc3m.es</strong>

          </p>
        </td>
      </tr>

      <!-- SECCIÓN 4: OPORTUNIDAD GEMINI AI PRO -->
      <tr>
        <td align='center' style='padding:20px; color:#EB178E;'>
          <h2 style='margin:0; font-size:22px;'><strong>Aprovecha Gemini AI Pro</strong></h2>
          <div style='margin-top:10px; width:80px; height:4px; background-color:#EB178E; border-radius:2px;'></div>
        </td>
      </tr>
      <tr>
        <td style='padding:20px; color:#333333; font-size:16px; line-height:1.6;'>
          <p>¿Sabías que por ser universitario en España tienes <strong>1 año gratis de Gemini PRO?</strong></p>
          <p>Te da acceso a:</p>
          <ul>
            <li>Gemini 2.5 Pro</li>
            <li>Veo 3.1</li>
            <li>2 TB de almacenamiento en Google</li>
          </ul>
          <a href='https://gemini.google/es/students/?hl=es' target='_blank'>No dejes pasar esta oportunidad</a>
        </td>
      </tr>
      <!-- Newsletter Footer -->
      <tr>
        <td align='center' style='padding:0 20px;'>
          <table role='presentation' width='550' cellpadding='0' cellspacing='0' align='center' border='0'
            style='border-top:5px solid #EB178E; margin-top:20px; padding-top:20px; font-family:Arial, sans-serif; font-size:14px; color:#555555;'>
            
            <tr>
              <td style='padding:0px; color:#333333; font-size:16px; line-height:1.5;'> 
                <p>Para <strong>enterarte de todos los eventos, workshops y oportunidades</strong> te recomendamos que estés atento y nos sigas por:</p>
              </td>
            </tr>

            <tr>
              <td align='center' style='padding:10px 0;'>
                <a href='https://aiscmadrid.com/' target='_blank' style='margin:0 20px; display:inline-block;'>
                  <img src='https://aiscmadrid.com/images/logos/PNG/internet-rosa.png' width='32' height='32'>
                </a>
                <a href='https://www.instagram.com/aisc_madrid/' target='_blank' style='margin:0 20px; display:inline-block;'>
                  <img src='https://aiscmadrid.com/images/logos/PNG/instagram-rosa.png' width='32' height='32'>
                </a>
                <a href='https://www.linkedin.com/company/ai-student-collective-madrid/' target='_blank' style='margin:0 20px; display:inline-block;'>
                  <img src='https://aiscmadrid.com/images/logos/PNG/linkedin-rosa.png' width='32' height='32'>
                </a>
              </td>
            </tr>

            <tr>
              <td align='center' style='padding:10px; padding-left:40px'>
                <a href='https://aiscmadrid.com/' target='_blank'>
                  <img src='https://aiscmadrid.com/images/logos/SVG/AISCMadridLogoAndLetters.svg' alt='Logo Footer' width='300'>
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
