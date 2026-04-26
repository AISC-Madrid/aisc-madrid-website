<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);


require __DIR__ . '/../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include('../assets/db.php');
$config = include('../config.php');
require_once __DIR__ . '/../assets/cloudinary.php';

echo "Starting QR code sending process...\n";


$sql = "SELECT er.event_id, er.email, er.name, e.title_es, e.start_datetime, e.end_datetime, e.location, e.image_path 
        FROM event_registrations er
        JOIN events e ON er.event_id = e.id
        WHERE er.qr_email_sent = 0 AND er.event_id = 21";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "Found " . $result->num_rows . " pending emails.\n";

    $mail = new PHPMailer(true);
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

    $baseHtmlContent = file_get_contents('../mails/event_registration/qr_code_template.html');

    $count = 0;
    while($row = $result->fetch_assoc()) {
        try {
            $mail->clearAddresses();
            $mail->addAddress($row['email'], $row['name']);

            $mail->isHTML(true);
            $mail->Subject = '¡Gracias por registrarte al evento!';
            $user_name_short = explode(' ', $row['name'])[0];
            $image_url = cdn_from_image_path($row['image_path']);
            // 1. Zonas horarias
            $utcTz = new DateTimeZone('UTC');
            $madridTz = new DateTimeZone('Europe/Madrid');

            // 2. Procesar Inicio (Leemos de la DB como UTC)
            $startDate = new DateTime($row['start_datetime'], $utcTz);

            // Formato para el HUMANO (Convertimos a Madrid)
            $startMadrid = clone $startDate;
            $startMadrid->setTimezone($madridTz);
            $formatted_date = $startMadrid->format('d/m/Y H:i');

            // Formato para el CALENDARIO (Mantenemos UTC + 'Z')
            $start_utc = $startDate->format('Ymd\THis\Z');

            // 3. Procesar Fin (si existe)
            if (!empty($row['end_datetime'])) {
                $endDate = new DateTime($row['end_datetime'], $utcTz);
                
                // Formato humano
                $endMadrid = clone $endDate;
                $endMadrid->setTimezone($madridTz);
                $formatted_date .= " - " . $endMadrid->format('d/m/Y H:i');
                
                // Formato calendario
                $end_utc = $endDate->format('Ymd\THis\Z');
            } else {
                // Si no hay fin, el calendario suma 1 hora al UTC original
                $endDate = clone $startDate;
                $endDate->modify('+1 hour');
                $end_utc = $endDate->format('Ymd\THis\Z');
            }
            
            $calendar_link = "https://www.google.com/calendar/render?action=TEMPLATE";
            $calendar_link .= "&text=" . urlencode("AISC Madrid - " . $row['title_es']);
            $calendar_link .= "&dates=" . $start_utc . "/" . $end_utc;
            $calendar_link .= "&details=" . urlencode("Más info: " . $config['base_url'] . "events/evento.php?id=" . $row['event_id']);
            $calendar_link .= "&location=" . urlencode($row['location']);
            $calendar_link .= "&sf=true&output=xml";

            $htmlContent = $baseHtmlContent;
            $htmlContent = str_replace('{{user_name}}', $user_name_short, $htmlContent);
            $htmlContent = str_replace('{{event_name}}', $row['title_es'], $htmlContent);
            $htmlContent = str_replace('{{event_date}}', $formatted_date, $htmlContent);
            $htmlContent = str_replace('{{event_location}}', $row['location'], $htmlContent);
            $htmlContent = str_replace('{{event_image}}', $image_url, $htmlContent);
            $htmlContent = str_replace('{{event_id}}', $row['event_id'], $htmlContent);
            $htmlContent = str_replace('{{mail}}', urlencode($row['email']), $htmlContent);
            $htmlContent = str_replace('{{calendar_link}}', $calendar_link, $htmlContent);

            $mail->Body = $htmlContent;
            $mail->send();

            // update DB
            $update_stmt = $conn->prepare("UPDATE event_registrations SET qr_email_sent = 1 WHERE event_id = ? AND email = ?");
            $update_stmt->bind_param("is", $row['event_id'], $row['email']);
            $update_stmt->execute();
            $update_stmt->close();

            echo "Sent to: " . $row['email'] . "\n";
            $count++;

        } catch (Exception $e) {
            echo "Error sending to " . $row['email'] . ": " . $mail->ErrorInfo . "\n";
        }
    }
    echo "Process completed. Sent $count emails.\n";

} else {
    echo "No pending emails found.\n";
}

$conn->close();
?>
