<?php
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$config = include('config.php');
require_once 'assets/db.php'; // Include database connection

$recipients = [];
$message = '';

if (isset($_POST['submit'])) {
    $recipient_group = $_POST['recipients'];
    $email_search = $_POST['email_search'];

    switch ($recipient_group) {
        case 'all':
            $sql = "SELECT email, name FROM newsletter_emails";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $recipients[] = ['email' => $row['email'], 'name' => $row['name']];
                }
            }
            break;
        case 'search':
            if (!empty($email_search)) {
                $stmt = $conn->prepare("SELECT email, name FROM newsletter_emails WHERE email = ?");
                $stmt->bind_param("s", $email_search);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $recipients[] = ['email' => $row['email'], 'name' => $row['name']];
                    }
                }
                $stmt->close();
            }
            break;
        case 'team':
            $sql = "SELECT email, name FROM team_members";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $recipients[] = ['email' => $row['email'], 'name' => $row['name']];
                }
            }
            break;
        case 'web_team':
            $sql = "SELECT email, name FROM team_members WHERE role = 'Webmaster'";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $recipients[] = ['email' => $row['email'], 'name' => $row['name']];
                }
            }
            break;
    }

    if (empty($recipients)) {
        $message = '<p class="text-warning">No recipients found for the selected criteria.</p>';
    } else {
        foreach ($recipients as $recipient) {
            $recipientEmail = $recipient['email'];
            $name = $recipient['name'];
            $subject = 'PHPMailer Test Localhost';

            $mail = new PHPMailer(true);

            try {
                $mail->CharSet = 'UTF-8';
                $mail->isSMTP();

                // Enable verbose debug output
                $mail->SMTPDebug = 0; // Set to 0 for production
                $mail->Debugoutput = 'html';

                // SMTP server settings
                $mail->Host = 'smtp.hostinger.com';
                $mail->Port = 587;
                $mail->SMTPAuth = true;
                $mail->Username = $config['smtp_user'];
                $mail->Password = $config['smtp_pass'];

                // Timeouts and SSL options
                $mail->Timeout = 30; // seconds
                $mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer' => true,
                        'verify_peer_name' => true,
                        'allow_self_signed' => false
                    ]
                ];

                // Sender and recipient
                $mail->setFrom($config['smtp_user'], 'AISC Madrid Test');
                $mail->addReplyTo('aisc.asoc@uc3m.es', 'AISC Madrid');
                $mail->addAddress($recipientEmail, $name); // Add a recipient

                // Content
                $mail->isHTML(true);
                $mail->Subject = $subject;

                $token = 'test_token'; // Replace with a real token
                $unsubscribeUrl = 'https://aiscmadrid.com/processing/unsubscribe.php?token=' . urlencode($token);

                // Get the HTML content from a file
                $htmlContent = file_get_contents('mails/template_newsletter_new.html');

                // Replace placeholders with dynamic content
                $htmlContent = str_replace('Título 1', $subject, $htmlContent);
                $htmlContent = str_replace('Subtítulo 1', '¡Hola ' . explode(' ', $name)[0] . '!', $htmlContent);
                $htmlContent = str_replace('{{unsubscribe_url}}', $unsubscribeUrl, $htmlContent);
                $mail->Body = $htmlContent;

                $mail->send();
                $message .= '<p class="text-success">Message has been sent successfully to ' . $recipientEmail . '</p>';

            } catch (Exception $e) {
                $message .= '<p class="text-danger">Message could not be sent to ' . $recipientEmail . '. Mailer Error: ' . $mail->ErrorInfo . '</p>';
                $message .= '<p class="text-danger">PHPMailer Exception: ' . $e->getMessage() . '</p>';
            }

            // Close SMTP connection
            if ($mail->SMTPKeepAlive) {
                $mail->smtpClose();
            }
        }
    }
}

// Close database connection
$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<?php include("assets/head.php"); ?>
<body>
    <?php include("assets/nav.php"); ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <form method="post" action="">
                <div class="mb-3">
                    <h1>Enviar Email de prueba</h1>
                    <label for="recipients" class="form-label">Seleccionar destinatarios:</label>
                    <select name="recipients" id="recipients" class="form-select">
                        <option value="search">Buscar un email</option>
                        <option value="all">Todos</option>
                        <option value="team">Miembros del equipo</option>
                        <option value="web_team">Miembros del equipo web</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="email_search" class="form-label">Buscar un email:</label>
                    <input type="text" name="email_search" id="email_search" class="form-control" placeholder="Introducir dirección de email">
                </div>
                <div class="d-grid">
                    <button type="submit" name="submit" class="btn btn-primary">Enviar Email</button>
                </div>
            </form>
            <div class="mt-4" id="message">
                <?php echo $message; ?>
            </div>
            
        </div>
    </div>

    <?php include('assets/footer.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>