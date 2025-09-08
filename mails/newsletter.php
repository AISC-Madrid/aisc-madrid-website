<?php
// Turn off output buffering
while (ob_get_level()) ob_end_flush();
ini_set('output_buffering', 'off');
ini_set('zlib.output_compression', 0);

// Display all errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include("../assets/db.php");
?>
<!DOCTYPE html>
<html lang="es">
<?php include("../assets/head.php"); ?>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white px-4 py-2 shadow-sm fixed-top" role="navigation" aria-label="Dashboard navigation">
        <div class="container-fluid">

            <!-- Brand / Logo -->
            <a class="navbar-brand" href="/" title="AISC Madrid - Dashboard">
                <img src="images/logos/PNG/AISC Logo Color.png" alt="Logo de AISC Madrid" style="height:70px;">
                <span class="fw-bold">Newsletter</span>
            </a>

            <!-- Toggler for mobile -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#dashboardNav"
                aria-controls="dashboardNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Nav Items -->
            <div class="collapse navbar-collapse justify-content-end" id="dashboardNav">
                <ul class="navbar-nav align-items-center">

                    <!-- Dashboard link -->
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="bi bi-house-door me-1"></i> Resumen
                        </a>
                    </li>

                    <!-- Users -->
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">
                            <i class="bi bi-people me-1"></i> Usuarios
                        </a>
                    </li>

                    <!-- Newsletter -->
                    <li class="nav-item">
                        <a class="nav-link" href="mails/newsletter.php">
                            <i class="bi bi-envelope me-1"></i> Newsletter
                        </a>
                    </li>

                    <!-- Events -->
                    <li class="nav-item">
                        <a class="nav-link" href="events/events_list.php">
                            <i class="bi bi-calendar-event me-1"></i> Eventos
                        </a>
                    </li>

                    <!-- Recruiting -->
                    <li class="nav-item">
                        <a class="nav-link" href="recruiting/recruiting_list.php">
                            <i class="bi bi-briefcase me-1"></i> Recruiting
                        </a>
                    </li>

                    <!-- Divider -->

                    <!-- <li class="nav-item mx-2 d-none d-lg-block">
          <span class="text-light">|</span>
        </li> -->

                    <!-- Profile Dropdown -->

                    <!-- <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="profileDropdown" role="button"
             data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle me-1"></i> Admin
          </a>
          <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="profileDropdown">
            <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i> Configuración</a></li>
            <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i> Perfil</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Cerrar sesión</a></li>
          </ul>
        </li> -->

                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid" style="margin-top:90px;">
        <div class="row">
            <!-- Main content -->
            <main class="col-12 px-md-4 py-4">
                <h2 class="mb-4 text-dark">Enviar Newsletter</h2>

                <div class="card">
                    <div class="card-body">
                        <?php
                        // Step 1: Show button if page loaded without sending
                        if (!isset($_POST['send_emails'])) {
                        ?>
                            <form method="post">
                                <button type="submit" name="send_emails" class="btn btn-primary">
                                    Enviar Newsletter
                                </button>
                            </form>
                        <?php
                        } else {

                            // Step 2: Send emails if form submitted
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
                                        error_log("Error enviando a $email: " . $mail->ErrorInfo);
                                    } else {
                                        echo "Correo enviado a $email<br>";
                                    }
                                    // Force flush immediately
                                    if (ob_get_level()) ob_flush();  // flush PHP buffer
                                    flush();                          // flush system buffer
                                }
                            } else {
                                echo "No hay usuarios suscritos a la newsletter.";
                            }

                            $conn->close();
                        }

                        ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <?php include('../assets/footer.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>