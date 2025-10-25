<?php
// Cargar las clases de PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

require '../vendor/autoload.php'; 

include_once '../assets/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger y limpiar los datos del formulario
    $name = htmlspecialchars(trim($_POST['name']));
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $event_id = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);

    // Validación de datos
    if (empty($name) || !$email || !$event_id) {
        header("Location: /events/event_registration.php?id=$event_id&error_validation=1&name=".urlencode($name)."&email=".urlencode($_POST['email']));
        exit;
    }

    // Comprobar si ya está inscrito en ESTE evento (evitar duplicados)
    $sql_check_event = "SELECT id FROM event_registrations WHERE event_id = ? AND email = ?";
    $stmt_check_event = $conn->prepare($sql_check_event);
    $stmt_check_event->bind_param("is", $event_id, $email);
    $stmt_check_event->execute();
    $stmt_check_event->store_result();

    if ($stmt_check_event->num_rows > 0) {
        $stmt_check_event->close();
        header("Location: /events/event_registration.php?id=$event_id&error_duplicate=1");
        exit;
    }
    $stmt_check_event->close();

    // Intentar añadirlo a la tabla de newsletter (si no está ya)
    $user_id = null; // Initialize user_id
    $sql_check_newsletter = "SELECT id FROM form_submissions WHERE email = ?";
    $stmt_check_newsletter = $conn->prepare($sql_check_newsletter);
    $stmt_check_newsletter->bind_param("s", $email);
    $stmt_check_newsletter->execute();
    $stmt_check_newsletter->store_result();

    if ($stmt_check_newsletter->num_rows > 0) {
        $stmt_check_newsletter->bind_result($user_id);
        $stmt_check_newsletter->fetch();
    } else {
        $token = bin2hex(random_bytes(16));
        $stmtInsert = $conn->prepare("INSERT INTO form_submissions (full_name, email, newsletter, unsubscribe_token) VALUES (?, ?, 'yes', ?)");
        $stmtInsert->bind_param("sss", $name, $email , $token);
        if ($stmtInsert->execute()) {
            $user_id = $stmtInsert->insert_id;
        }
    }
    $stmt_check_newsletter->close();

        
    // Inscribir al usuario en el evento
    $sql_insert_registration = "INSERT INTO event_registrations (event_id, name, email) VALUES (?, ?, ?)";
    $stmt_insert_registration = $conn->prepare($sql_insert_registration);
    if ($stmt_insert_registration) {
        $stmt_insert_registration->bind_param("iss", $event_id, $name, $email);
        
        if ($stmt_insert_registration->execute()) {
            // Inserción exitosa, ahora generar QR y enviar correo

            // --- QR Code Generation ---
            if ($user_id) {
                $qr_data = "user_id:" . $user_id . ",event_id:" . $event_id;
                
                $qrCode = QrCode::create($qr_data)
                    ->setSize(300)
                    ->setMargin(10);

                $writer = new PngWriter();
                $qr_code_result = $writer->write($qrCode);
                $qr_code_data_uri = $qr_code_result->getDataUri();

                // --- Email Sending ---
                $mail = new PHPMailer(true);
                try {
                    //Server settings
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.hostinger.com'; // From phpmailer.php
                    $mail->SMTPAuth   = true;
                    $config = include('../config.php');
                    $mail->Username   = $config['smtp_user'];
                    $mail->Password   = $config['smtp_pass'];
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;

                    //Recipients
                    $mail->setFrom('info@aiscmadrid.com', 'AISC Madrid');
                    $mail->addAddress($email, $name); // Add a recipient
                    $mail->addReplyTo('aisc.asoc@uc3m.es', 'AISC Madrid');

                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = '¡Gracias por registrarte al evento!';
                    
                    $htmlContent = file_get_contents('../mails/registration/gracias_registro.html');
                    $htmlContent = str_replace('{{user_name}}', $name, $htmlContent);
                    
                    $sql_event = "SELECT name, event_date, location FROM events WHERE id = ?";
                    $stmt_event = $conn->prepare($sql_event);
                    $stmt_event->bind_param("i", $event_id);
                    $stmt_event->execute();
                    $result_event = $stmt_event->get_result();
                    $event = $result_event->fetch_assoc();
                    $stmt_event->close();

                    $htmlContent = str_replace('{{event_name}}', $event['name'], $htmlContent);
                    $htmlContent = str_replace('{{event_date}}', $event['event_date'], $htmlContent);
                    $htmlContent = str_replace('{{event_location}}', $event['location'], $htmlContent);
                    $htmlContent = str_replace('{{qr_code_url}}', $qr_code_data_uri, $htmlContent);
                    
                    $mail->Body = $htmlContent;
                    $mail->AltBody = 'Hola ' . htmlspecialchars($name) . ', gracias por registrarte al evento. Adjunto encontrarás tu código QR de asistencia.';

                    $mail->send();
                } catch (Exception $e) {
                    error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
                }
            }

            // Inserción exitosa, redirigir a la página de éxito
            header("Location: /events/event_registration.php?id=$event_id&success=1");
        } else {
            // Error en la inserción en el evento
            header("Location: /events/event_registration.php?id=$event_id&error_db=1");
        }
        $stmt_insert_registration->close();
    } else {
        // Error general de la base de datos
        header("Location: /events/event_registration.php?id=$event_id&error_db=1");
    }

    $conn->close();
    exit;

} else {
    // Si no es una solicitud POST, redirigir al inicio
    header("Location: /index.php");
    exit;
}
?>