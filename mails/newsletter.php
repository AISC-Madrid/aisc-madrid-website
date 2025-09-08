<?php
// Turn off output buffering so browser sees output immediately
ob_implicit_flush(true);
ob_end_flush();

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
require '../config/db.php';

?>
<!DOCTYPE html>
<html lang="es">
<?php include("../assets/head.php"); ?>
<body>

<!-- Navbar -->
<?php include("../assets/dashboard_nav.php"); ?>

<div class="container-fluid" style="margin-top:90px;">
  <div class="row">
    <!-- Sidebar -->
    <nav class="col-md-2 d-none d-md-block bg-light sidebar py-4" style="min-height:100vh;">
      <div class="nav flex-column">
        <a class="nav-link" href="../dashboard.php"><i class="bi bi-speedometer2"></i> Resumen</a>
        <a class="nav-link" href="../users.php"><i class="bi bi-people"></i> Usuarios</a>
        <a class="nav-link active" href="newsletter.php"><i class="bi bi-envelope"></i> Newsletter</a>
        <a class="nav-link" href="../events/events_list.php"><i class="bi bi-calendar-event"></i> Eventos</a>
      </div>
    </nav>

    <!-- Main content -->
    <main class="col-md-10 ms-sm-auto col-lg-10 px-md-4 py-4">
      <h2 class="mb-4">Enviar Newsletter</h2>

      <div class="card">
        <div class="card-body">
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = $_POST['subject'] ?? '';
    $body = $_POST['body'] ?? '';

    // Query subscribers
    $sql = "SELECT email, unsubscribe_token FROM subscribers WHERE status = 'active'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {

        // Add padding so browser starts rendering immediately
        echo str_repeat(' ', 1024);

        while ($row = $result->fetch_assoc()) {
            $email = $row['email'];
            $token = $row['unsubscribe_token'];
            $unsubscribe_link = "https://yoursite.com/unsubscribe.php?token=" . urlencode($token);

            // PHPMailer setup
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.example.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'your@email.com';
                $mail->Password = 'yourpassword';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('no-reply@yoursite.com', 'AISC Madrid');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body = $body . "<br><br><a href='$unsubscribe_link'>Darse de baja</a>";

                $mail->send();
                echo "<p class='text-success mb-1'>✅ Correo enviado a $email</p>";
            } catch (Exception $e) {
                echo "<p class='text-danger mb-1'>❌ Error enviando a $email: {$mail->ErrorInfo}</p>";
            }

            // Force flush after each line
            @ob_flush();
            flush();
        }
    } else {
        echo "<p class='text-warning'>⚠️ No hay suscriptores activos.</p>";
    }
}
?>
        </div>
      </div>
    </main>
  </div>
</div>

<!-- Footer -->
<?php include("../assets/footer.php"); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
