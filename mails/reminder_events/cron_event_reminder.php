<?php
/**
 * Cron de recordatorios de eventos. Pensado para ejecutarse una vez al día (Scheduled Task
 * de Coolify): php /var/www/html/mails/reminder_events/cron_event_reminder.php
 * No es accesible por web.
 *
 * Envía el recordatorio a cada inscripción con reminder_sent = 0 de los eventos con
 * reminder_enabled = 1 que aún no han empezado y para los que faltan reminder_days_before
 * días o menos (contando en hora de Madrid). Si un día no se ejecuta o falla algún envío,
 * la siguiente ejecución recupera los pendientes.
 *
 * Para ver a quién se enviaría sin enviar nada:
 *   php mails/reminder_events/cron_event_reminder.php --dry-run
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("CLI only.\n");
}

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require __DIR__ . '/../../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
include(__DIR__ . '/../../assets/db.php');
$config = include(__DIR__ . '/../../config.php');
require_once __DIR__ . '/../../assets/cloudinary.php';
require_once __DIR__ . '/reminder_text.php';

$dryRun = in_array('--dry-run', $argv ?? [], true);

$utcTz = new DateTimeZone('UTC');
$madridTz = new DateTimeZone('Europe/Madrid');
$todayMadrid = new DateTime('today', $madridTz);
$nowUtc = new DateTime('now', $utcTz);

echo '[cron_event_reminder] ' . $nowUtc->format('Y-m-d H:i:s') . ' UTC' . ($dryRun ? ' (dry run)' : '') . "\n";

// Candidatos: eventos futuros con recordatorio que empiezan dentro de su ventana (+1 día de margen
// por la diferencia UTC/Madrid). El filtro exacto por días en hora de Madrid se hace en PHP.
// El nombre sale de la inscripción; form_submissions (un nombre por email) solo es el respaldo,
// así un email repetido allí no genera correos duplicados.
$query = "SELECT r.email, r.event_id, COALESCE(NULLIF(TRIM(r.name), ''), u.full_name) AS full_name,
        e.title_es, e.title_en, e.start_datetime, e.end_datetime, e.location, e.image_path, e.speaker,
        e.reminder_days_before, e.reminder_custom_text, e.type_es
    FROM event_registrations r
    JOIN events e ON r.event_id = e.id
    LEFT JOIN (
        SELECT email, MAX(full_name) AS full_name FROM form_submissions GROUP BY email
    ) u ON r.email COLLATE utf8mb4_unicode_ci = u.email COLLATE utf8mb4_unicode_ci
    WHERE e.reminder_enabled = 1
    AND e.status = 'published'
    AND r.reminder_sent = 0
    AND e.start_datetime > UTC_TIMESTAMP()
    AND e.start_datetime <= DATE_ADD(UTC_TIMESTAMP(), INTERVAL e.reminder_days_before + 1 DAY)
    ORDER BY e.start_datetime, r.event_id";

$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "No hay recordatorios para enviar hoy.\n";
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
$mail->SMTPKeepAlive = true; // una sola conexión SMTP para todo el lote
$mail->Username = $config['smtp_user'];
$mail->Password = $config['smtp_pass'];
$mail->setFrom($config['smtp_user'], 'AISC Madrid');
$mail->addReplyTo('aisc.asoc@uc3m.es', 'AISC Madrid');
$mail->isHTML(true);

$baseHtmlContent = file_get_contents(__DIR__ . '/event_reminder_template.html');

function escape_ics_text($value)
{
    $value = str_replace('\\', '\\\\', $value);
    $value = str_replace(["\r\n", "\r", "\n"], '\\n', $value);
    return str_replace([';', ',', '"'], ['\\;', '\\,', '\\"'], $value);
}

// Texto de la cabecera: "es hoy" / "es mañana" / "es en N días"
function days_left_text(int $days): string
{
    if ($days <= 0) return 'es hoy';
    if ($days === 1) return 'es mañana';
    return "es en $days días";
}

$update = $conn->prepare("UPDATE event_registrations SET reminder_sent = 1 WHERE email = ? AND event_id = ?");

$seen = [];
$sent = 0;
$failed = 0;

// Bucle para enviar a cada asistente
while ($row = $result->fetch_assoc()) {
    $email = trim($row['email']);
    $dedupeKey = strtolower($email) . '|' . $row['event_id'];
    if (isset($seen[$dedupeKey])) continue;
    $seen[$dedupeKey] = true;

    // Fechas: en la BD están en UTC; los días que faltan se cuentan en hora de Madrid
    $startDate = new DateTime($row['start_datetime'], $utcTz);
    $startDateMadrid = clone $startDate;
    $startDateMadrid->setTimezone($madridTz);
    $eventDayMadrid = new DateTime($startDateMadrid->format('Y-m-d'), $madridTz);
    $daysLeft = (int) $todayMadrid->diff($eventDayMadrid)->format('%r%a');

    if ($daysLeft > (int) $row['reminder_days_before']) continue;

    $fullName = trim($row['full_name'] ?? '');
    $nombre = $fullName !== '' ? explode(' ', $fullName)[0] : '';

    if ($dryRun) {
        echo "  [dry-run] evento {$row['event_id']} ({$row['title_es']}, faltan $daysLeft días) -> $email\n";
        continue;
    }

    $event_type_es = isset($row['type_es']) ? strtolower(trim($row['type_es'])) : '';
    $event_type_label = ($event_type_es === 'taller' || $event_type_es === 'workshop') ? 'taller' : 'evento';

    $image_url = cdn_from_image_path($row['image_path']);

    // Para el texto de la web: en hora de Madrid
    $formatted_date = $startDateMadrid->format('d/m/Y H:i');

    // Para el enlace de Calendario: formato UTC 'Z'
    $start_utc = $startDate->format('Ymd\THis\Z');

    if (!empty($row['end_datetime'])) {
        $endDate = new DateTime($row['end_datetime'], $utcTz);

        $endDateMadrid = clone $endDate;
        $endDateMadrid->setTimezone($madridTz);
        $formatted_date .= " - " . $endDateMadrid->format('d/m/Y H:i');

        $end_utc = $endDate->format('Ymd\THis\Z');
    } else {
        // Si no hay fin, sumamos 1 hora al inicio UTC
        $endDate = clone $startDate;
        $endDate->modify('+1 hour');
        $end_utc = $endDate->format('Ymd\THis\Z');
    }

    $event_url = $config['base_url'] . "events/evento.php?id=" . $row['event_id'];

    $calendar_link = "https://www.google.com/calendar/render?action=TEMPLATE";
    $calendar_link .= "&text=" . urlencode("AISC Madrid - " . $row['title_es']);
    $calendar_link .= "&dates=" . $start_utc . "/" . $end_utc;
    $calendar_link .= "&details=" . urlencode("Más info: " . $event_url);
    $calendar_link .= "&location=" . urlencode($row['location']);
    $calendar_link .= "&sf=true&output=xml";

    $calendar_host = parse_url($config['base_url'], PHP_URL_HOST);
    if (empty($calendar_host)) {
        $calendar_host = 'aiscmadrid.com';
    }
    $event_uid = $row['event_id'] . '-' . md5($row['email'] . '|' . $row['start_datetime']) . '@' . $calendar_host;
    $icsContent = "BEGIN:VCALENDAR\r\n";
    $icsContent .= "VERSION:2.0\r\n";
    $icsContent .= "PRODID:-//AISC Madrid//Event Reminder//ES\r\n";
    $icsContent .= "CALSCALE:GREGORIAN\r\n";
    $icsContent .= "METHOD:PUBLISH\r\n";
    $icsContent .= "BEGIN:VEVENT\r\n";
    $icsContent .= "UID:" . escape_ics_text($event_uid) . "\r\n";
    $icsContent .= "DTSTAMP:" . gmdate('Ymd\THis\Z') . "\r\n";
    $icsContent .= "DTSTART:" . $start_utc . "\r\n";
    $icsContent .= "DTEND:" . $end_utc . "\r\n";
    $icsContent .= "SUMMARY:" . escape_ics_text($row['title_es']) . "\r\n";
    $icsContent .= "DESCRIPTION:" . escape_ics_text("Más info: " . $event_url) . "\r\n";
    $icsContent .= "LOCATION:" . escape_ics_text($row['location']) . "\r\n";
    $icsContent .= "URL:" . escape_ics_text($event_url) . "\r\n";
    $icsContent .= "END:VEVENT\r\n";
    $icsContent .= "END:VCALENDAR\r\n";

    // Párrafo principal: texto del panel (o el de por defecto) construido con los datos actuales del evento
    $reminder_vars = [
        'event_type_label' => $event_type_label,
        'event_name' => $row['title_es'],
        'event_date' => $formatted_date,
    ];

    $htmlContent = $baseHtmlContent;
    $htmlContent = str_replace('{{reminder_body}}', build_reminder_html($row['reminder_custom_text'], $reminder_vars), $htmlContent);
    $htmlContent = str_replace('{{user_name}}', htmlspecialchars($nombre), $htmlContent);
    $htmlContent = str_replace('{{event_type_label}}', $event_type_label, $htmlContent);
    $htmlContent = str_replace('{{event_name}}', htmlspecialchars($row['title_es']), $htmlContent);
    $htmlContent = str_replace('{{event_date}}', $formatted_date, $htmlContent);
    $htmlContent = str_replace('{{event_location}}', htmlspecialchars($row['location']), $htmlContent);
    $htmlContent = str_replace('{{event_image}}', $image_url, $htmlContent);
    $htmlContent = str_replace('{{event_id}}', $row['event_id'], $htmlContent);
    $htmlContent = str_replace('{{mail}}', urlencode($row['email']), $htmlContent);
    $htmlContent = str_replace('{{event_speakers}}', htmlspecialchars($row['speaker']), $htmlContent);
    $htmlContent = str_replace('{{calendar_link}}', $calendar_link, $htmlContent);
    $htmlContent = str_replace('{{days_left_text}}', days_left_text($daysLeft), $htmlContent);

    try {
        $mail->clearAddresses();
        $mail->clearAttachments();
        $mail->addAddress($email, $nombre);
        $mail->Subject = "¡Recordatorio! {$row['title_es']} | AISC Madrid";
        $mail->Body = $htmlContent;
        $mail->addStringAttachment($icsContent, 'event-reminder-' . $row['event_id'] . '.ics', 'base64', 'text/calendar; charset=UTF-8; method=PUBLISH');
        $mail->AltBody = "Hola {$nombre},\n\n" . build_reminder_plain($row['reminder_custom_text'], $reminder_vars);

        $mail->send();

        // Marcamos como enviado para no repetir si el script corre dos veces
        $update->bind_param("si", $row['email'], $row['event_id']);
        $update->execute();
        $sent++;
    } catch (Exception $e) {
        // Un fallo no corta el lote: se registra y se reintenta en la siguiente ejecución
        $failed++;
        error_log("[cron_event_reminder] Error enviando a $email (evento {$row['event_id']}): " . $mail->ErrorInfo);
        echo "  ERROR -> $email (evento {$row['event_id']}): {$mail->ErrorInfo}\n";
        $mail->getSMTPInstance()->reset();
    }
}

$mail->smtpClose();
echo "Proceso finalizado. Enviados: $sent. Fallidos: $failed.\n";
$update->close();
$stmt->close();
$conn->close();
