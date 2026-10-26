<?php

use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/cloudinary.php';

/**
 * Configure a PHPMailer instance with the Gmail SMTP account.
 * The connection is kept open so a batch of emails reuses it.
 */
function configure_registration_mailer(PHPMailer $mail, array $config): void
{
    $mail->CharSet = 'UTF-8';
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->Port = 587;
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = 'tls';
    $mail->Username = $config['smtp_user'];
    $mail->Password = $config['smtp_pass'];
    $mail->Timeout = 30;
    $mail->SMTPKeepAlive = true;

    $mail->setFrom($config['smtp_user'], 'AISC Madrid');
    $mail->addReplyTo('aisc.asoc@uc3m.es', 'AISC Madrid');
    $mail->isHTML(true);
    $mail->Subject = '¡Gracias por registrarte al evento!';
}

/**
 * Send the "thanks for registering" email (with calendar link and .ics) to one attendee.
 *
 * @param array{title_es: string, start_datetime: string, end_datetime: ?string, location: string, image_path: ?string} $event_data
 */
function send_registration_email(PHPMailer $mail, array $config, array $event_data, int $event_id, string $name, string $email): void
{
    $mail->clearAddresses();
    $mail->clearAttachments();
    $mail->addAddress($email, $name);

    $htmlContent = file_get_contents(__DIR__ . '/../mails/event_registration/qr_code_template.html');

    $user_name_short = explode(' ', $name)[0];
    $utcTz = new DateTimeZone('UTC');
    $madridTz = new DateTimeZone('Europe/Madrid');

    // Dates are stored in UTC: show them in Madrid time, keep UTC for the calendar
    $startDate = new DateTime($event_data['start_datetime'], $utcTz);
    $startMadrid = clone $startDate;
    $startMadrid->setTimezone($madridTz);
    $formatted_date = $startMadrid->format('d/m/Y H:i');
    $start_utc = $startDate->format('Ymd\THis\Z');

    if (!empty($event_data['end_datetime'])) {
        $endDate = new DateTime($event_data['end_datetime'], $utcTz);
        $endMadrid = clone $endDate;
        $endMadrid->setTimezone($madridTz);
        $formatted_date .= " - " . $endMadrid->format('d/m/Y H:i');
        $end_utc = $endDate->format('Ymd\THis\Z');
    } else {
        $endDate = clone $startDate;
        $endDate->modify('+1 hour');
        $end_utc = $endDate->format('Ymd\THis\Z');
    }

    $image_url = cdn_from_image_path($event_data['image_path']);

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

    $ics_uid = uniqid('evento_') . "@aiscmadrid.es";
    $ics_dtstamp = gmdate('Ymd\THis\Z');
    $ics_description = "Más info: " . $config['base_url'] . "events/evento.php?id=" . $event_id;

    $ics_content = "BEGIN:VCALENDAR\r\n";
    $ics_content .= "VERSION:2.0\r\n";
    $ics_content .= "PRODID:-//AISC Madrid//Sitio Web//ES\r\n";
    $ics_content .= "CALSCALE:GREGORIAN\r\n";
    $ics_content .= "BEGIN:VEVENT\r\n";
    $ics_content .= "UID:{$ics_uid}\r\n";
    $ics_content .= "DTSTAMP:{$ics_dtstamp}\r\n";
    $ics_content .= "DTSTART:{$start_utc}\r\n";
    $ics_content .= "DTEND:{$end_utc}\r\n";
    $ics_content .= "SUMMARY:" . $event_data['title_es'] . "\r\n";
    $ics_content .= "LOCATION:" . $event_data['location'] . "\r\n";
    $ics_content .= "DESCRIPTION:" . $ics_description . "\r\n";
    $ics_content .= "END:VEVENT\r\n";
    $ics_content .= "END:VCALENDAR\r\n";

    $mail->addStringAttachment($ics_content, 'evento_aisc.ics', 'base64', 'text/calendar');

    $mail->send();
}
