<?php
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
    :root {
        --navbar-offset: 96px;
    }
    body {
        min-height: 100vh;
        background: linear-gradient(135deg, #f8f4ff 0%, #fce4f3 50%, #e8f7ff 100%);
        display: flex;
        flex-direction: column;
    }
    .success-wrapper {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: calc(var(--navbar-offset) + 1rem) 1rem 2rem;
    }
    .success-box {
        background: rgba(255,255,255,0.75);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(235,23,142,0.1);
        border-radius: 20px;
        padding: 2.5rem 2rem;
        width: 100%;
        max-width: 420px;
        text-align: center;
        box-shadow: 0 8px 32px rgba(235,23,142,0.08);
    }
    .success-icon {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: rgba(235,23,142,0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.25rem;
        font-size: 1.5rem;
        color: var(--primary);
    }
    .success-box h2 {
        color: var(--background);
        font-weight: 700;
        font-size: 1.5rem;
    }
    .success-box p {
        color: rgba(0,0,0,0.5);
        font-size: 0.9rem;
    }
    .accent-bar {
        width: 48px;
        height: 3px;
        background: var(--primary);
        border-radius: 2px;
        margin: 0.5rem auto 1.5rem;
    }
    .btn-home {
        background: transparent;
        border: 1px solid rgba(0,0,0,0.15);
        color: rgba(0,0,0,0.5);
        border-radius: 8px;
        font-size: 0.875rem;
        padding: 0.5rem 1.25rem;
        transition: border-color 0.2s, color 0.2s;
    }
    .btn-home:hover {
        border-color: var(--primary);
        color: var(--primary);
    }
    .btn-whatsapp {
        background: #25D366 !important;
        color: #fff !important;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        padding: 0.65rem 1.25rem;
        transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
        box-shadow: 0 4px 15px rgba(37,211,102,0.3);
    }
    .btn-whatsapp:hover {
        filter: brightness(110%);
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(37,211,102,0.45);
        background: #25D366 !important;
        color: #fff !important;
    }
    .btn-whatsapp:active {
        transform: translateY(0);
        box-shadow: 0 3px 10px rgba(37,211,102,0.3);
    }
    @media (max-width: 768px) {
        :root {
            --navbar-offset: 110px;
        }
        .success-wrapper {
            align-items: flex-start;
        }
    }
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
    :root {
        --navbar-offset: 96px;
    }
    body {
        min-height: 100vh;
        background: linear-gradient(135deg, #f8f4ff 0%, #fce4f3 50%, #e8f7ff 100%);
        display: flex;
        flex-direction: column;
    }
    .newsletter-wrapper {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: calc(var(--navbar-offset) + 1rem) 1rem 2rem;
    }
    .newsletter-box {
        background: rgba(255,255,255,0.75);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(235,23,142,0.1);
        border-radius: 20px;
        padding: 2.5rem 2rem;
        width: 100%;
        max-width: 420px;
        box-shadow: 0 8px 32px rgba(235,23,142,0.08);
    }
    .newsletter-box h2 {
        color: var(--background);
        font-weight: 700;
        font-size: 1.6rem;
    }
    .newsletter-box p.subtitle {
        color: rgba(0,0,0,0.5);
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
        color: rgba(0,0,0,0.6);
        font-size: 0.85rem;
        font-weight: 500;
    }
    .newsletter-box .form-control {
        background: rgba(255,255,255,0.9);
        border: 1px solid rgba(0,0,0,0.12);
        color: var(--background);
        border-radius: 8px;
    }
    .newsletter-box .form-control:focus {
        background: #fff;
        border-color: var(--primary);
        color: var(--background);
        box-shadow: 0 0 0 3px rgba(235,23,142,0.12);
    }
    .newsletter-box .form-control:disabled {
        background: rgba(0,0,0,0.04);
        border-color: rgba(0,0,0,0.07);
        color: rgba(0,0,0,0.35);
    }
    .newsletter-box .form-control::placeholder {
        color: rgba(0,0,0,0.25);
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
    @media (max-width: 768px) {
        :root {
            --navbar-offset: 110px;
        }
        .newsletter-wrapper {
            align-items: flex-start;
        }
    }
    footer { width: 100%; }
</style>
<body>
    <?php include("../assets/nav.php") ?>
    <div class="newsletter-wrapper ">
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

                    
                <!-- Consentimiento -->
                <div class="form-check mb-3">
                  <input class="form-check-input" type="checkbox" id="consent" name="consent" required>
                  <label class="form-check-label form-text" for="consent"
                    data-en="I consent to AISC Madrid storing my submitted data to contact me."
                    data-es="Doy mi consentimiento para que AISC Madrid almacene mis datos enviados para contactarme.">
                    Doy mi consentimiento para que AISC Madrid almacene mis datos enviados para contactarme.
                  </label>
                  <a class="form-check-label form-text" href="terms_conditions.php" target="_blank"
                    data-en="(Read terms and conditions)" data-es="(Leer términos y condiciones)">
                    (Leer términos y condiciones)
                  </a>
                  <div class="invalid-feedback" data-en="You must give permission to continue"
                    data-es="Debes dar tu consentimiento para continuar. ">Debes dar tu consentimiento para continuar.
                  </div>
                </div>


                    <button type="submit" class="btn btn-subscribe w-100" id="submitBtn">
                        <span id="btnText">Suscribirme</span>
                        <span id="btnLoading" style="display:none;">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Enviando...
                        </span>
                    </button>
                </form>
                <script>
                    document.querySelector('form').addEventListener('submit', function(e) {
                        e.preventDefault();
                        const form = this;
                        const btn = document.getElementById('submitBtn');
                        document.getElementById('btnText').style.display = 'none';
                        document.getElementById('btnLoading').style.display = 'inline';
                        btn.disabled = true;
                        setTimeout(function() { form.submit(); }, 300);
                    });
                </script>
            <?php endif; ?>
        </div>
    </div>
    <?php include('../assets/footer.php'); ?>
    <script src="../js/scripts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
