<?php
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;

$email = trim($_GET['email'] ?? '');
$nameFromUrl = trim($_GET['name'] ?? '');
$emailValid = filter_var($email, FILTER_VALIDATE_EMAIL);

// If both name and email are in the URL, treat it as a direct submission
if ($emailValid && $nameFromUrl !== '' && $_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['success']) && !isset($_GET['error'])) {
    $_POST['name']  = $nameFromUrl;
    $_POST['email'] = $email;
    $_SERVER['REQUEST_METHOD'] = 'POST';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name']  ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ?email=" . urlencode($email) . "&error=validation");
        exit;
    }

    include('../assets/db.php');

    $checkStmt = $conn->prepare("SELECT id FROM form_submissions WHERE email = ?");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        $checkStmt->close();
        $conn->close();
        header("Location: ?email=" . urlencode($email) . "&error=duplicate");
        exit;
    }
    $checkStmt->close();

    $token = bin2hex(random_bytes(16));
    $stmt  = $conn->prepare("INSERT INTO form_submissions (full_name, email, newsletter, unsubscribe_token) VALUES (?, ?, 'yes', ?)");
    $stmt->bind_param("sss", $name, $email, $token);

    if (!$stmt->execute()) {
        $stmt->close();
        $conn->close();
        header("Location: ?email=" . urlencode($email) . "&error=db_error");
        exit;
    }

    $stmt->close();
    $conn->close();

    // Send welcome email
    $mail = new PHPMailer;
    $mail->CharSet = 'UTF-8';
    $mail->isSMTP();
    $mail->SMTPDebug = 0;
    $mail->Host     = 'smtp.hostinger.com';
    $mail->Port     = 587;
    $mail->SMTPAuth = true;

    $config = include('../config.php');
    $mail->Username = $config['smtp_user'];
    $mail->Password = $config['smtp_pass'];
    $mail->setFrom('info@aiscmadrid.com', 'AISC Madrid');
    $mail->addReplyTo('aisc.asoc@uc3m.es', 'AISC Madrid');
    $firstName = explode(' ', $name)[0];
    $mail->addAddress($email, $firstName);
    $mail->Subject = '¡Bienvenid@ a la comunidad AISC Madrid!';

    $htmlContent = "
    <html>
    <head><title>¡Bienvenid@ a la comunidad AISC Madrid!</title></head>
    <body>
      <h2>¡Hola {$firstName}!</h2>
      <p>Gracias por unirte a la newsletter de <strong>AISC Madrid</strong>.</p>
      <p>A partir de ahora, recibirás noticias sobre nuestros próximos eventos, talleres y actividades.</p>
      <p>Únete al canal de WhatsApp para ser parte de la comunidad:</p>
      <a href='https://chat.whatsapp.com/BpdXitZhwGCCpErwBoj3hv?mode=r_c' target='_blank'
         style='display:inline-block;padding:10px 20px;background-color:#25D366;color:white;text-decoration:none;border-radius:5px;font-weight:bold;font-family:Arial,sans-serif;margin-top:15px;'>
        Únete a la comunidad AISC Madrid en WhatsApp
      </a>
      <br><br>
      <p>Síguenos en redes sociales:</p>
      <a href='https://instagram.com/aisc_madrid' target='_blank'
         style='display:inline-block;padding:10px 20px;background-color:#D43089;color:white;text-decoration:none;border-radius:5px;font-weight:bold;font-family:Arial,sans-serif;margin-top:15px;'>
        Instagram
      </a>
      <a href='https://www.linkedin.com/company/ai-student-collective-madrid/' target='_blank'
         style='display:inline-block;padding:10px 20px;background-color:#0B66C3;color:white;text-decoration:none;border-radius:5px;font-weight:bold;font-family:Arial,sans-serif;margin-top:15px;'>
        LinkedIn
      </a>
      <p>Nos vemos pronto,<br>Equipo de AISC Madrid</p>
      <div style='text-align:right;margin-top:30px;'>
        <a href='https://aiscmadrid.com/processing/unsubscribe.php?token=" . urlencode($token) . "'
           style='color:gray;text-decoration:none;font-family:Arial,sans-serif;font-size:12px;'>
          Cancelar suscripción Newsletter
        </a>
      </div>
    </body>
    </html>";

    $mail->isHTML(true);
    $mail->Body    = $htmlContent;
    $mail->AltBody = "Hola {$firstName}, gracias por unirte a la comunidad AISC Madrid.";

    if (!$mail->send()) {
        error_log('Mailer Error: ' . $mail->ErrorInfo);
    }

    header("Location: ?email=" . urlencode($email) . "&success=1");
    exit;
}

// Show success page
if (isset($_GET['success'])) {
?>
<!DOCTYPE html>
<html lang="es">
<?php include("../assets/head.php"); ?>
<style>
    body {
        min-height: 100vh;
        background-color: var(--background);
        display: flex;
        flex-direction: column;
    }
    .success-wrapper {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
    }
    .success-box {
        background: var(--muted);
        border: 1px solid rgba(255,255,255,0.07);
        border-radius: 16px;
        padding: 2.5rem 2rem;
        width: 100%;
        max-width: 420px;
        text-align: center;
    }
    .success-icon {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: rgba(32,204,241,0.12);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.25rem;
        font-size: 1.5rem;
        color: var(--secondary);
    }
    .success-box h2 {
        color: var(--foreground);
        font-weight: 700;
        font-size: 1.5rem;
    }
    .success-box p {
        color: rgba(255,255,255,0.5);
        font-size: 0.9rem;
    }
    .accent-bar {
        width: 48px;
        height: 3px;
        background: var(--secondary);
        border-radius: 2px;
        margin: 0.5rem auto 1.5rem;
    }
    .btn-home {
        background: transparent;
        border: 1px solid rgba(255,255,255,0.15);
        color: rgba(255,255,255,0.7);
        border-radius: 8px;
        font-size: 0.875rem;
        padding: 0.5rem 1.25rem;
        transition: border-color 0.2s, color 0.2s;
    }
    .btn-home:hover {
        border-color: var(--secondary);
        color: var(--secondary);
    }
    .btn-whatsapp {
        background: #25D366;
        color: #fff;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        padding: 0.65rem 1.25rem;
        transition: filter 0.2s;
    }
    .btn-whatsapp:hover { filter: brightness(110%); color: #fff; }
    footer { width: 100%; }
</style>
<body>
    <?php include("../assets/nav.php") ?>
    <div class="success-wrapper">
        <div class="success-box">
            <div class="success-icon"><i class="bi bi-check-lg"></i></div>
            <h2>¡Ya eres parte de AISC Madrid!</h2>
            <div class="accent-bar"></div>
            <p>Hemos recibido tus datos. Revisa tu bandeja de entrada o spam.</p>
            <div class="d-flex flex-column gap-2 mt-3">
                <a href="https://chat.whatsapp.com/BpdXitZhwGCCpErwBoj3hv?mode=r_c"
                   target="_blank" class="btn btn-whatsapp d-inline-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-whatsapp"></i>
                    Únete a la comunidad en WhatsApp
                </a>
                <a href="/" class="btn btn-home">Volver al inicio</a>
            </div>
        </div>
    </div>
    <?php include('../assets/footer.php'); ?>
    <script src="../js/scripts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
    exit;
}

// Show subscribe form
$error = $_GET['error'] ?? '';
$errorMsg = match($error) {
    'validation' => 'Por favor, introduce un nombre válido.',
    'duplicate'  => 'Este correo ya está suscrito a la newsletter.',
    'db_error'   => 'Error al guardar. Inténtalo de nuevo.',
    default      => '',
};
?>
<!DOCTYPE html>
<html lang="es">
<?php include("../assets/head.php"); ?>
<style>
    body {
        min-height: 100vh;
        background: linear-gradient(135deg, hsl(300, 3%, 10%) 0%, hsl(270, 5%, 14%) 100%);
        display: flex;
        flex-direction: column;
    }
    .newsletter-wrapper {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
    }
    .newsletter-box {
        background: rgba(255,255,255,0.05);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 20px;
        padding: 2.5rem 2rem;
        width: 100%;
        max-width: 420px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.3);
    }
    .newsletter-box h2 {
        color: var(--foreground);
        font-weight: 700;
        font-size: 1.6rem;
    }
    .newsletter-box p.subtitle {
        color: rgba(255,255,255,0.5);
        font-size: 0.9rem;
    }
    .accent-bar {
        width: 48px;
        height: 3px;
        background: var(--primary);
        border-radius: 2px;
        margin: 0.5rem 0 1.5rem 0;
    }
    .newsletter-box .form-label {
        color: rgba(255,255,255,0.7);
        font-size: 0.85rem;
        font-weight: 500;
    }
    .newsletter-box .form-control {
        background: rgba(255,255,255,0.06);
        border: 1px solid rgba(255,255,255,0.12);
        color: var(--foreground);
        border-radius: 8px;
    }
    .newsletter-box .form-control:focus {
        background: rgba(255,255,255,0.09);
        border-color: var(--secondary);
        color: var(--foreground);
        box-shadow: 0 0 0 3px rgba(32,204,241,0.15);
    }
    .newsletter-box .form-control:disabled {
        background: rgba(255,255,255,0.04);
        border-color: rgba(255,255,255,0.07);
        color: rgba(255,255,255,0.35);
    }
    .newsletter-box .form-control::placeholder {
        color: rgba(255,255,255,0.25);
    }
    .btn-subscribe {
        background: linear-gradient(90deg, var(--primary), #c4106f);
        color: #fff;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        padding: 0.65rem;
        transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
        box-shadow: 0 4px 15px rgba(235,23,142,0.3);
    }
    .btn-subscribe:hover {
        filter: brightness(115%);
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(235,23,142,0.45);
        color: #fff;
    }
    .btn-subscribe:active {
        transform: translateY(0);
        box-shadow: 0 3px 10px rgba(235,23,142,0.3);
    }
    .alert-custom {
        background: rgba(220,53,69,0.15);
        border: 1px solid rgba(220,53,69,0.3);
        color: #ff6b7a;
        border-radius: 8px;
        font-size: 0.875rem;
        padding: 0.75rem 1rem;
    }
    footer { width: 100%; }
</style>
<body>
    <?php include("../assets/nav.php") ?>
    <div class="newsletter-wrapper">
        <div class="newsletter-box">
            <h2>Newsletter</h2>
            <div class="accent-bar"></div>
            <p class="subtitle">Mantente al día de eventos, talleres y oportunidades de AISC Madrid.</p>

            <?php if ($errorMsg): ?>
                <div class="alert-custom mb-3"><?= htmlspecialchars($errorMsg) ?></div>
            <?php endif; ?>

            <?php if (!$emailValid): ?>
                <div class="alert-custom">Enlace inválido. Escanea de nuevo el QR.</div>
            <?php else: ?>
                <form method="POST" action="">
                    <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">

                    <div class="mb-3">
                        <label for="name" class="form-label">Tu nombre</label>
                        <input type="text" class="form-control" id="name" name="name"
                               placeholder="Nombre y apellidos" required autofocus>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" value="<?= htmlspecialchars($email) ?>" disabled>
                    </div>

                    <button type="submit" class="btn btn-subscribe w-100">Suscribirme</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <?php include('../assets/footer.php'); ?>
    <script src="../js/scripts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
