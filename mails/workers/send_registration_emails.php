<?php
/**
 * Email worker: sends pending "thanks for registering" emails (qr_email_sent = 0).
 *
 * Runs from the command line every 30 seconds (see docker/entrypoint.sh), so web
 * requests never wait on Gmail. Only registrations from the last 2 days for events
 * that haven't finished are sent, so old failed rows are never mass-sent by surprise.
 * An address that fails 5 times in a row is skipped.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$config = include __DIR__ . '/../../config.php';
require __DIR__ . '/../../assets/db.php';
require_once __DIR__ . '/../../assets/registration_email.php';

const BATCH_SIZE = 25;
const MAX_ATTEMPTS = 5;
$failuresFile = sys_get_temp_dir() . '/aisc_registration_email_failures.json';

$failures = [];
if (is_file($failuresFile)) {
    $failures = json_decode((string) file_get_contents($failuresFile), true) ?: [];
}

$stmt = $conn->prepare(
    "SELECT er.event_id, er.name, er.email, e.title_es, e.start_datetime, e.end_datetime, e.location, e.image_path
     FROM event_registrations er
     JOIN events e ON e.id = er.event_id
     WHERE er.qr_email_sent = 0
       AND er.registration_date >= NOW() - INTERVAL 2 DAY
       AND COALESCE(e.end_datetime, e.start_datetime) >= UTC_TIMESTAMP()
     ORDER BY er.registration_date
     LIMIT ?"
);
$limit = BATCH_SIZE * 2;
$stmt->bind_param('i', $limit);
$stmt->execute();
$pending = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pending = array_values(array_filter(
    $pending,
    fn (array $row): bool => ($failures[$row['event_id'] . ':' . $row['email']] ?? 0) < MAX_ATTEMPTS
));
$pending = array_slice($pending, 0, BATCH_SIZE);

if ($pending === []) {
    $conn->close();
    exit(0);
}

$mail = new PHPMailer(true);
configure_registration_mailer($mail, $config);

$markSent = $conn->prepare("UPDATE event_registrations SET qr_email_sent = 1 WHERE event_id = ? AND email = ?");
$sent = 0;

foreach ($pending as $row) {
    $key = $row['event_id'] . ':' . $row['email'];

    try {
        send_registration_email($mail, $config, $row, (int) $row['event_id'], $row['name'], $row['email']);

        $eventId = (int) $row['event_id'];
        $markSent->bind_param('is', $eventId, $row['email']);
        $markSent->execute();
        unset($failures[$key]);
        $sent++;
    } catch (Exception $e) {
        $failures[$key] = ($failures[$key] ?? 0) + 1;
        error_log("Registration email to {$row['email']} failed (attempt {$failures[$key]}): {$mail->ErrorInfo}");

        // If the SMTP connection itself is broken, stop and retry the rest on the next run
        if (!$mail->getSMTPInstance()->connected()) {
            break;
        }
    }
}

$markSent->close();
$mail->smtpClose();
$conn->close();
file_put_contents($failuresFile, json_encode($failures));

echo date('c') . " Sent {$sent} registration email(s).\n";
