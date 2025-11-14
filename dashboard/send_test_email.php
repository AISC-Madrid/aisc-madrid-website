<?php
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

$config = include('../config.php');
require_once '../assets/db.php';

$recipients = [];
$message = '';
$mail_files = glob('../mails/*/*.html'); 
$events = $conn->query("SELECT id, title_es, title_en FROM events ORDER BY start_datetime DESC");

// Check if the user is logged in
if (!isset($_SESSION['activated']) || $_SESSION['role'] !== 'admin') {
    header("Location: events/login.php");
    exit();
}

echo '<script>
    document.addEventListener("DOMContentLoaded", function() {
        const recipientSelect = document.getElementById("recipients");
        const emailSearchInput = document.getElementById("email_search_container");

        recipientSelect.addEventListener("change", function() {
            if (recipientSelect.value === "search") {
                emailSearchInput.style.display = "block";
            } else {
                emailSearchInput.style.display = "none";
            }
        });
    });
</script>';



if (isset($_POST['submit'])) {
    $recipient_group = $_POST['recipients'];
    $email_search = (string)$_POST['email_search'];
    $mail_template = $_POST['mail_template'];
    $mail_subject = $_POST['mail_subject'];
    $event_id = (int)$_POST['event_search'];
    

    switch ($recipient_group) {
        case 'all':
            $sql = "SELECT email, full_name, unsubscribe_token FROM form_submissions";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $recipients[] = ['email' => $row['email'], 'full_name' => $row['full_name'], 'unsubscribe_token' => $row['unsubscribe_token']];
                }
            }
            break;
        case 'search':
            if (!empty($email_search)) {

                // Split input
                $emails = preg_split("/[;,]+/", $email_search);
                $emails = array_map('trim', $emails);
                $emails = array_filter($emails);

                if (!empty($emails)) {

                    // Escape emails safely
                    $safe_emails = array_map(function($email) use ($conn) {
                        return "'" . $conn->real_escape_string($email) . "'";
                    }, $emails);

                    // Create IN list
                    $inList = implode(",", $safe_emails);

                    // Run the query
                    $sql = "SELECT email, full_name, unsubscribe_token
                            FROM form_submissions
                            WHERE email IN ($inList)";

                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $recipients[] = [
                                'email' => $row['email'],
                                'full_name' => $row['full_name'],
                                'unsubscribe_token' => $row['unsubscribe_token'] ?? ''
                            ];
                        }
                    }
                }
            }
            break;
        case 'team':
            $sql = "SELECT mail, full_name FROM members";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $recipients[] = ['email' => $row['mail'], 'full_name' => $row['full_name']];
                }
            }
            break;
        case 'web_team':
            $sql = "SELECT mail, full_name FROM members WHERE position_es LIKE '%Web%'";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $recipients[] = ['email' => $row['mail'], 'full_name' => $row['full_name']];
                }
            }
            break;
        case 'event_users':
            $event_id = (int)$_POST['event_search'];
            $stmt = $conn->prepare("SELECT fs.email, fs.full_name AS full_name
                                    FROM form_submissions fs
                                    JOIN event_registrations er ON fs.email = er.email COLLATE utf8mb4_unicode_ci
                                    WHERE er.event_id = ? AND er.qr_email_sent = 0");
            $stmt->bind_param("i", $event_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $recipients[] = ['email' => $row['email'], 'full_name' => $row['full_name']];
                }
            }
            $stmt->close();
            break;
    }

    if (empty($recipients)) {
        $message = '<p class="text-warning">No recipients found for the selected criteria.</p>';
    } else {
        $mail = new PHPMailer(true);
        $subject = $mail_subject;
        $event_id = (int)$event_id;

        try {
            $mail->CharSet = 'UTF-8';
            $mail->isSMTP();
            $mail->SMTPDebug = 0;
            $mail->Debugoutput = 'html';

            // Configuración del servidor
            $mail->Host = 'smtp.gmail.com';
            $mail->Port = 587;
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = 'tls';
            $mail->Username = $config['smtp_user'];
            $mail->Password = $config['smtp_pass'];

            // Opciones
            $mail->Timeout = 30; // seconds
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                    'allow_self_signed' => false
                    ]
                ];

            $mail->SMTPKeepAlive = true; 

            $mail->setFrom($config['smtp_user'], 'AISC Madrid');
            $mail->addReplyTo('aisc.asoc@uc3m.es', 'AISC Madrid');
            
            $mail->isHTML(true);
            $mail->Subject = $subject;

            $baseHtmlContent = file_get_contents($mail_template);

            $totalEmails = count($recipients);
            $batchSize = 20;
            $pauseDuration = 5; // seconds

            for ($i = 0; $i < $totalEmails; $i++) {
                $recipient = $recipients[$i];
                $recipientEmail = (string)$recipient['email'];
                $fullName = (string)$recipient['full_name'];
                $unsubscribe_token = (string)($recipient['unsubscribe_token'] ?? '');


                $name = explode(' ', $fullName)[0];

                // Validar el email antes de intentar enviar
                if (empty($recipientEmail) || !filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
                    $message .= '<p class="text-warning">Dirección inválida omitida: ' . htmlspecialchars($recipientEmail) . '</p>';
                    continue; // Saltar a la siguiente persona
                }
                
                try {
                    $mail->clearAddresses();

                    $mail->addAddress($recipientEmail, $name); 

                    $htmlContent = $baseHtmlContent;
                    $htmlContent = str_replace('$full_name[0]', $name, $htmlContent);
                    if (!empty($unsubscribe_token)) {
                        $htmlContent = str_replace('$unsubscribe_token', $unsubscribe_token, $htmlContent);
                    }

                    $htmlContent = str_replace('{{mail}}', urlencode($recipientEmail), $htmlContent);

                    $htmlContent = str_replace('{{event_id}}', $event_id, $htmlContent);
                    
                    $mail->Body = $htmlContent;

                    $mail->send();
                    $message .= '<p class="text-success">Mensaje enviado a ' . $recipientEmail . ' (' . ($i + 1) . '/' . $totalEmails . ')</p>';

                    // Update qr_email_sent flag if sending to event users
                    if ($recipient_group === 'event_users') {
                        $sql_update_sent = "UPDATE event_registrations SET qr_email_sent = TRUE WHERE event_id = ? AND email = ?";
                        $stmt_update_sent = $conn->prepare($sql_update_sent);
                        if ($stmt_update_sent) {
                            $stmt_update_sent->bind_param("is", $event_id, $recipientEmail);
                            $stmt_update_sent->execute();
                            $stmt_update_sent->close();
                        }
                    }

                } catch (Exception $e) {
                    $message .= '<p class="text-danger">Error al enviar a ' . $recipientEmail . '. Mailer Error: ' . $mail->ErrorInfo . '</p>';
                }

                // Pause between batches
                if (($i + 1) % $batchSize == 0 && ($i + 1) < $totalEmails) {
                    $message .= "<p class='text-info'>Pausando por $pauseDuration segundos antes del siguiente lote...</p>";
                    if (ob_get_level()) ob_flush();
                    flush();
                    sleep($pauseDuration);
                }
            }

            $mail->smtpClose();
            $message .= "<p class='text-success'><strong>¡Todos los correos han sido enviados!</strong></p>";

        } catch (Exception $e) {
            $message .= '<p class="text-danger">Error fatal de PHPMailer: ' . $mail->ErrorInfo . '</p>';
            $message .= '<p class="text-danger">PHPMailer Exception: ' . $e->getMessage() . '</p>';
        } finally {
            $mail->smtpClose();
        }
    }
}

// Close database connection
$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<?php include("../assets/head.php"); ?>
<body>
    <?php include("dashboard_nav.php"); ?>

    <main style="flex: 1;" class="scroll-margin">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <h1 class="text-center text-dark">Enviar Email de prueba</h1>
            <p class="text-center text-dark">Envía un correo electrónico de prueba a los destinatarios seleccionados.</p>
            <div class="text-left">
                <!-- Lista de tipos de destinatarios -->
                <div class="mb-4 text-dark">
                    <p>Tipos de destinatarios:</p>
                    <ul>
                        <li><strong>Buscar un email:</strong> Envía un mensaje a una dirección de email específica.</li>
                        <li><strong>Miembros del equipo:</strong> Envía un mensaje a todos los miembros del equipo.</li>
                        <li><strong>Miembros del equipo web:</strong> Envía un mensaje solo a los miembros del equipo web.</li>
                        <li><strong>Usuarios registrados en el evento:</strong> Envía un mensaje solo a los usuarios registrados en el evento especificado.</li>
                        <li><strong>Todos:</strong> Envía un mensaje a todos los correos registrados en la base de datos.</li>
                        
                    </ul>
                </div>
            <form method="post" action="">
                <div class="mb-3">
                    
                    <label for="recipients" class="form-label">Seleccionar destinatarios:</label>
                    <select name="recipients" id="recipients" class="form-select">
                        <option value="search">Buscar un email</option>
                        <option value="team">Miembros del equipo</option>
                        <option value="web_team">Miembros del equipo web</option>
                        <option value="event_users">Usuarios registrados en el evento</option>
                        <option value="all">Todos</option>
                    </select>
                </div>
                <div class="mb-3" id="email_search_container">
                    <label for="email_search" class="form-label">Buscar un email:</label>
                    <input type="text" name="email_search" id="email_search" class="form-control" placeholder="Introducir dirección de email">
                </div>
                <div class="mb-3" id="event_search_container">
                    <label for="event_search" class="form-label">Evento:</label>
                        <select name="event_search" id="event_search" class="form-select">
                            <?php while ($event = $events->fetch_assoc()): ?>
                                <option value="<?php echo $event['id']; ?>">
                                    <?php echo htmlspecialchars($event['title_es'] . " / " . $event['title_en']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                </div>
                <div class="mb-3">
                    <label for="mail_template" class="form-label">Seleccionar plantilla de email:</label>
                    <select name="mail_template" id="mail_template" class="form-select">
                        <?php foreach ($mail_files as $file): ?>
                            <option value="<?php echo $file; ?>"><?php echo basename($file); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3" id="mail_subject_container">
                    <label for="mail_subject" class="form-label">Asunto del email</label>
                    <input type="text" name="mail_subject" id="mail_subject" class="form-control" placeholder="Introducir asunto del email" required>
                </div>
                <div class="d-grid">
                    <button type="submit" name="submit" class="btn btn-primary">Enviar Email</button>
                </div>
            </form>
            <div class="mt-4" id="preview">
                <h3 class="text-dark">Vista previa:</h3>
                <iframe id="preview_frame" src="" width="100%" height="500px" style="border: 1px solid #ccc;"></iframe>
            </div>
            <div class="mt-4" id="message">
                <?php echo $message; ?>
            </div>
            
        </div>
    </div>
    </main>

    <?php include('../assets/footer.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('mail_template').addEventListener('change', function() {
            var selected_file = this.value;
            document.getElementById('preview_frame').src = selected_file;
        });

        document.getElementById('mail_template').dispatchEvent(new Event('change'));
    </script>
</body>
</html>
