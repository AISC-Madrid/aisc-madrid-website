<?php
// Errores activados para debug (quitar en producción)
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
include(__DIR__ . '/../../assets/db.php');
$config = include(__DIR__ . '/../../config.php');

// Definir la fecha objetivo (dentro de 2 días)
$hoy = new DateTime();
$fecha_objetivo = $hoy->modify('+2 days')->format('Y-m-d');

$query = "SELECT u.full_name, r.email, r.event_id, e.title_es, e.title_en, e.start_datetime, e.end_datetime, e.location, e.image_path, e.speaker 
        FROM event_registrations r
        JOIN form_submissions u ON r.email COLLATE utf8mb4_unicode_ci = u.email COLLATE utf8mb4_unicode_ci
        JOIN events e ON r.event_id = e.id
        WHERE DATE(e.start_datetime) = ? AND r.reminder_sent = 0";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $fecha_objetivo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "No hay recordatorios para enviar hoy.";
    exit;
}

// Configuración base de PHPMailer
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
$mail->isHTML(true);

$baseHtmlContent = file_get_contents(__DIR__ . '/event_reminder_template.html');

$meses = ["", "enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];

// 4. Bucle para enviar a cada asistente
while ($row = $result->fetch_assoc()) {
    $nombre = explode(' ', $row['full_name'])[0];
    $email = $row['email'];

    // Datos del evento
    $titulo_es = $row['title_es'];
    $titulo_en = $row['title_en'];
    $lugar = htmlspecialchars($row['location']);
    $ponentes = htmlspecialchars($row['speaker']);
    $inicio = htmlspecialchars($row['start_datetime']);
    $final = htmlspecialchars($row['end_datetime']);

    // Construir fecha legible en español
    $dt = new DateTime($row['start_datetime']);
    $dia = $dt->format('j');
    $mes = $meses[(int)$dt->format('n')];
    $fecha_texto = "$dia de $mes";

    $mail->clearAddresses(); // Limpiar destinatario anterior
    $mail->addAddress($email, $nombre);
    $mail->Subject = "¡Recordatorio! $titulo_es | AISC Madrid";

    // replace placeholders
    $formatted_date = date('d/m/Y H:i', strtotime($row['start_datetime']));
    if (!empty($row['end_datetime'])) {
        $formatted_date .= " - " . date('d/m/Y H:i', strtotime($row['end_datetime']));
    }
    
    $domain = $config['base_url']; 
    $image_url = $domain . ltrim($row['image_path'], '/');

    // Generate Calendar Link
    $madridTz = new DateTimeZone('Europe/Madrid');
    $utcTz = new DateTimeZone('UTC');

    $startDate = new DateTime($row['start_datetime'], $madridTz);
    $startDate->setTimezone($utcTz);
    $start_utc = $startDate->format('Ymd\THis\Z');

    if (!empty($row['end_datetime'])) {
        $endDate = new DateTime($row['end_datetime'], $madridTz);
        $endDate->setTimezone($utcTz);
        $end_utc = $endDate->format('Ymd\THis\Z');
    } else {
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
    $htmlContent = str_replace('{{user_name}}', $nombre, $htmlContent);
    $htmlContent = str_replace('{{event_name}}', $row['title_es'], $htmlContent);
    $htmlContent = str_replace('{{event_date}}', $formatted_date, $htmlContent);
    $htmlContent = str_replace('{{event_location}}', $row['location'], $htmlContent);
    $htmlContent = str_replace('{{event_image}}', $image_url, $htmlContent);
    $htmlContent = str_replace('{{event_id}}', $row['event_id'], $htmlContent);
    $htmlContent = str_replace('{{mail}}', urlencode($row['email']), $htmlContent);
    $htmlContent = str_replace('{{event_speakers}}', htmlspecialchars($row['speaker']), $htmlContent);
    $htmlContent = str_replace('{{calendar_link}}', $calendar_link, $htmlContent);


    $mail->Body = $htmlContent;
    $mail->AltBody = "Hola {$nombre}, te recordamos que el evento {$titulo_es} es el {$inicio} en {$lugar}.";

    if ($mail->send()) {
        // Marcamos como enviado para no repetir si el script corre dos veces
        $update = $conn->prepare("UPDATE event_registrations SET reminder_sent = 1 WHERE email = ? AND event_id = ?");
        $update->bind_param("si", $email, $row['event_id']);
        $update->execute();
    } else {
        error_log("Error enviando a $email: " . $mail->ErrorInfo);
    }
}

echo "Proceso finalizado. Recordatorios enviados.";
$stmt->close();
$conn->close();

?>
