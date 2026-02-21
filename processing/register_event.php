<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include('../assets/db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get and sanitize form data
    $event_id = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
    $name = htmlspecialchars(trim($_POST['name'])); 
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $consent = isset($_POST['consent']);

    // Basic validation
    if (!$event_id || !$name || !$email || !$consent) {
        header("Location: /events/event_registration.php?id=$event_id&error_validation=1");
        exit;
    }

    // Check for duplicate registration
    $stmt = $conn->prepare("SELECT id FROM event_registrations WHERE event_id = ? AND email = ?");
    $stmt->bind_param("is", $event_id, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        header("Location: /events/event_registration.php?id=$event_id&error_duplicate=1");
        exit;
    }
    $stmt->close();

    // Insert new registration
    $stmt = $conn->prepare("INSERT INTO event_registrations (event_id, name, email) VALUES (?, ?, ?)");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("iss", $event_id, $name, $email);

    if ($stmt->execute()) {
        $stmt->close();
        // Fall into the newsletter dwell if user wasn't already
        $checkForm = $conn->prepare("SELECT id FROM form_submissions WHERE email = ?");
        $checkForm->bind_param("s", $email);
        $checkForm->execute();
        $checkForm->store_result();

        if ($checkForm->num_rows === 0) {
            $checkForm->close();

            $unsubscribe_token = bin2hex(random_bytes(16)); 

            $stmt2 = $conn->prepare("INSERT INTO form_submissions (full_name, email, unsubscribe_token) VALUES (?, ?, ?)");
            $stmt2->bind_param("sss", $name, $email, $unsubscribe_token);
            $stmt2->execute();
            $stmt2->close();
        } else {
            $checkForm->close();
        }

        $conn->close();

        $config = include('../config.php');
        $mail = new PHPMailer(true);

        try {
            include('../assets/db.php'); 
            
            $stmt_event = $conn->prepare("SELECT title_es, start_datetime, end_datetime, location, image_path FROM events WHERE id = ?");
            $stmt_event->bind_param("i", $event_id);
            $stmt_event->execute();
            $result_event = $stmt_event->get_result();
            $event_data = $result_event->fetch_assoc();
            $stmt_event->close();

            if ($event_data) {
                $mail->CharSet = 'UTF-8';
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->Port = 587;
                $mail->SMTPAuth = true;
                $mail->SMTPSecure = 'tls';
                $mail->Username = $config['smtp_user'];
                $mail->Password = $config['smtp_pass'];

                $mail->setFrom($config['smtp_user'], 'AISC Madrid');
                $mail->addReplyTo('aisc.asoc@uc3m.es', 'AISC Madrid');
                $mail->addAddress($email, $name);

                $mail->isHTML(true);
                $mail->Subject = '¡Gracias por registrarte al evento!';

                $htmlContent = file_get_contents('../mails/event_registration/qr_code_template.html');
                
                $user_name_short = explode(' ', $name)[0];
                $start_date = date('d/m/Y H:i', strtotime($event_data['start_datetime']));
                $formatted_date = $start_date;
                if (!empty($event_data['end_datetime'])) {
                    $end_date = date('d/m/Y H:i', strtotime($event_data['end_datetime']));
                    $formatted_date .= " - " . $end_date;
                }
                
                $domain = $config['base_url']; 
                $image_url = $domain . ltrim($event_data['image_path'], '/');

                // Generate Calendar Link
                $madridTz = new DateTimeZone('Europe/Madrid');
                $utcTz = new DateTimeZone('UTC');

                $startDate = new DateTime($event_data['start_datetime'], $madridTz);
                $startDate->setTimezone($utcTz);
                $start_utc = $startDate->format('Ymd\THis\Z');

                // Default end date to 1 hour later if not set, or use end_datetime
                if (!empty($event_data['end_datetime'])) {
                    $endDate = new DateTime($event_data['end_datetime'], $madridTz);
                    $endDate->setTimezone($utcTz);
                    $end_utc = $endDate->format('Ymd\THis\Z');
                } else {
                    $endDate = clone $startDate;
                    $endDate->modify('+1 hour');
                    $end_utc = $endDate->format('Ymd\THis\Z');
                }
                
                $calendar_link = "https://www.google.com/calendar/render?action=TEMPLATE";
                $calendar_link .= "&text=" . urlencode("AISC Madrid - " . $event_data['title_es']);
                $calendar_link .= "&dates=" . $start_utc . "/" . $end_utc;
                $calendar_link .= "&details=" . urlencode("Más info: " . $config['base_url'] . "events/evento.php?id=" . $event_id);
                $calendar_link .= "&location=" . urlencode($event_data['location']);
                $calendar_link .= "&sf=true&output=xml";

                $htmlContent = str_replace('{{user_name}}', $user_name_short, $htmlContent);
                $htmlContent = str_replace('{{event_name}}', $event_data['title_es'], $htmlContent);
                $htmlContent = str_replace('{{event_date}}', $formatted_date, $htmlContent);
                $htmlContent = str_replace('{{event_location}}', $event_data['location'], $htmlContent);
                $htmlContent = str_replace('{{event_image}}', $image_url, $htmlContent);
                $htmlContent = str_replace('{{event_id}}', $event_id, $htmlContent);
                $htmlContent = str_replace('{{mail}}', urlencode($email), $htmlContent);
                $htmlContent = str_replace('{{calendar_link}}', $calendar_link, $htmlContent);

                $mail->Body = $htmlContent;
                $mail->send();

                // update qr_email_sent
                $stmt_update = $conn->prepare("UPDATE event_registrations SET qr_email_sent = 1 WHERE event_id = ? AND email = ?");
                $stmt_update->bind_param("is", $event_id, $email);
                $stmt_update->execute();
                $stmt_update->close();
            }
            $conn->close();

        } catch (Exception $e) {
            error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }

        header("Location: /events/event_registration.php?id=$event_id&success=1");
        exit;
    } else {
        $stmt->close();
        header("Location: /events/event_registration.php?id=$event_id&error_db=1");
        exit;
    }

} else {
    header("Location: /index.php");
    exit;
}
?>