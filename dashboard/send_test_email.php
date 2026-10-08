<?php
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

// Check if the user is logged in. First thing to do before doing any other loading
$allowed_roles = ['admin', 'web'];
if (!isset($_SESSION['activated']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: /");
    exit();
}

$config = include('../config.php');
require_once '../assets/csrf.php';
require_once '../assets/db.php';
require_once '../assets/contacts.php';
require_once '../assets/cloudinary.php';
include("../assets/nav_dashboard.php");

$recipients = [];
$message = '';
$mail_files = glob('../mails/*/*.html');
$events = $conn->query("SELECT id, title_es, title_en FROM events ORDER BY start_datetime DESC");

const DEFAULT_CONTACT_INTRO = "Buenos días {{contact_name}},\n\n"
    . "En el próximo evento de la asociación universitaria AI Student Collective recibimos la visita de {{event_speaker}}; creemos que puede ser de gran interés para los alumnos del {{organization}}.\n"
    . "El evento se celebrará el próximo {{event_date}} a las {{event_time}} en {{event_location}}.\n\n"
    . "Al igual que en eventos anteriores, es una gran oportunidad para que los estudiantes puedan aprender sobre el sector y conectar con profesionales.\n\n"
    . "Te mando a continuación el mensaje que me gustaría que copiaras y pegaras para mandar.\n\n"
    . "Y nos gustaría agradecerte el apoyo que estás brindando a la asociación.\n\n"
    . "Muchas gracias.";

// Active contacts + templates already sent to each one (for the selection table)
$contacts = [];
$contacts_result = $conn->query("SELECT id, full_name, greeting_name, email, category, organization FROM contacts WHERE active = 1 ORDER BY category, organization, full_name");
while ($row = $contacts_result->fetch_assoc()) {
    $row['sent_templates'] = [];
    $contacts[$row['id']] = $row;
}
$logs_result = $conn->query("SELECT contact_id, template_name, MAX(sent_at) AS sent_at FROM contact_email_logs GROUP BY contact_id, template_name");
while ($row = $logs_result->fetch_assoc()) {
    if (isset($contacts[$row['contact_id']])) {
        $contacts[$row['contact_id']]['sent_templates'][$row['template_name']] = $row['sent_at'];
    }
}

/**
 * Turn a personalised template into a generic one that a contact can forward to students:
 * removes the recipient's name and the newsletter unsubscribe links.
 */
function make_forwardable(string $html): string
{
    $html = preg_replace('/\s*(\$full_name\[0\]|\{\{user_name\}\})/', '', $html);
    $html = preg_replace('/\s*\|\s*<a\b[^>]*unsubscribe[^>]*>.*?<\/a>/is', '', $html);
    $html = preg_replace('/<a\b[^>]*unsubscribe[^>]*>.*?<\/a>/is', '', $html);
    return $html;
}

/**
 * Put the personal intro for a contact on top of the email they are asked to forward.
 * Placeholders: {{contact_name}}, {{organization}} and, from the selected event,
 * {{event_name}}, {{event_speaker}}, {{event_date}} ("lunes 17 de noviembre"), {{event_time}}, {{event_location}}.
 */
function wrap_for_contact(string $html, string $intro, array $contact, ?array $event): string
{
    $organization = trim((string) ($contact['organization'] ?? ''));
    if ($organization === '') {
        $intro = str_replace(' del {{organization}}', '', $intro);
    }

    $values = [
        '{{contact_name}}' => contact_greeting_name($contact),
        '{{organization}}' => $organization,
    ];
    if ($event) {
        $start = new DateTime($event['start_datetime'], new DateTimeZone('UTC'));
        $start->setTimezone(new DateTimeZone('Europe/Madrid'));
        $values += [
            '{{event_name}}' => $event['title_es'],
            '{{event_speaker}}' => (string) ($event['speaker'] ?? ''),
            '{{event_date}}' => spanish_long_date($start),
            '{{event_time}}' => $start->format('H:i'),
            '{{event_location}}' => (string) ($event['location'] ?? ''),
        ];
    }

    $intro = nl2br(htmlspecialchars($intro));
    $intro = str_replace(array_keys($values), array_map('htmlspecialchars', array_values($values)), $intro);

    $header = '<div style="font-family:Arial,sans-serif; font-size:15px; color:#222; max-width:600px; margin:0 auto 24px auto; padding:16px; text-align:left; line-height:1.5;">' . $intro . '</div>'
        . '<div style="max-width:600px; margin:0 auto 16px auto; border-top:1px solid #ccc; padding-top:8px; font-family:Arial,sans-serif; font-size:12px; color:#888; text-align:center;">Mensaje para difundir</div>';

    if (preg_match('/<body\b[^>]*>/i', $html)) {
        return preg_replace_callback('/<body\b[^>]*>/i', fn($m) => $m[0] . $header, $html, 1);
    }
    return $header . $html;
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
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        die("Token CSRF inválido.");
    }
    if (!in_array($_POST['mail_template'] ?? '', $mail_files, true)) {
        die("Plantilla de email inválida.");
    }
    $recipient_group = $_POST['recipients'];
    $email_search = (string) $_POST['email_search'];
    $mail_template = $_POST['mail_template'];
    $mail_subject = $_POST['mail_subject'];
    $event_id = (int) $_POST['event_search'];


    switch ($recipient_group) {
        case 'all':
            $sql = "SELECT email, full_name, unsubscribe_token FROM form_submissions";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $recipients[] = ['email' => $row['email'], 'full_name' => $row['full_name'], 'unsubscribe_token' => $row['unsubscribe_token']];
                }
            }
            break;
        case 'newsletter':
            $sql = "SELECT f.email, f.full_name, f.unsubscribe_token 
                FROM form_submissions f
                LEFT JOIN newsletter_logs n 
                ON f.email = n.email AND n.template_name = ?
                WHERE n.email IS NULL AND f.newsletter = 'yes'";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $mail_template);
            $stmt->execute();
            $result = $stmt->get_result();

            $recipients = [];

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $recipients[] = [
                        'email' => $row['email'],
                        'full_name' => $row['full_name'],
                        'unsubscribe_token' => $row['unsubscribe_token']
                    ];
                }
            }

            $stmt->close();

            break;
        case 'search':
            if (!empty($email_search)) {

                // Split input
                $emails = preg_split("/[;,]+/", $email_search);
                $emails = array_map('trim', $emails);
                $emails = array_filter($emails);
                if (!empty($emails)) {

                    // Escape emails safely
                    $safe_emails = array_map(function ($email) use ($conn) {
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
        case 'contacts':
            // Only ids are posted; emails/names come from the DB
            foreach ((array) ($_POST['contact_ids'] ?? []) as $contact_id) {
                $contact_id = (int) $contact_id;
                if (isset($contacts[$contact_id])) {
                    $recipients[] = $contacts[$contact_id] + ['contact_id' => $contact_id];
                }
            }
            break;
        case 'team':
            $sql = "SELECT mail, full_name FROM members";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $recipients[] = ['email' => $row['mail'], 'full_name' => $row['full_name']];
                }
            }
            break;
        case 'web_team':
            $sql = "SELECT mail, full_name FROM members WHERE position_es LIKE '%Web%'";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $recipients[] = ['email' => $row['mail'], 'full_name' => $row['full_name']];
                }
            }
            break;
        case 'event_users':
            $event_id = (int) $_POST['event_search'];
            // ALL registered users for the event
            $stmt = $conn->prepare("SELECT email, name AS full_name
                                    FROM event_registrations
                                    WHERE event_id = ?");
            $stmt->bind_param("i", $event_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $recipients[] = ['email' => $row['email'], 'full_name' => $row['full_name']];
                }
            }
            $stmt->close();
            break;

        case 'pending_qrs':
            $event_id = (int) $_POST['event_search'];
            // fetch ONLY pending QR users
            $stmt = $conn->prepare("SELECT email, name AS full_name
                                    FROM event_registrations
                                    WHERE event_id = ? AND qr_email_sent = 0");
            $stmt->bind_param("i", $event_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $recipients[] = ['email' => $row['email'], 'full_name' => $row['full_name']];
                }
            }
            $stmt->close();
            break;
    }

    $event_data = null;
    if ($event_id > 0) {
        $stmt_event = $conn->prepare("SELECT title_es, speaker, start_datetime, end_datetime, location, image_path FROM events WHERE id = ?");
        $stmt_event->bind_param("i", $event_id);
        $stmt_event->execute();
        $result_event = $stmt_event->get_result();
        $event_data = $result_event->fetch_assoc();
        $stmt_event->close();
    }

    if (empty($recipients)) {
        $message = '<p class="text-warning">No recipients found for the selected criteria.</p>';
    } else {
        $mail = new PHPMailer(true);
        $subject = $mail_subject;
        $event_id = (int) $event_id;

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
            if ($recipient_group === 'contacts') {
                $baseHtmlContent = make_forwardable($baseHtmlContent);
                $contact_intro = trim((string) ($_POST['contact_intro'] ?? '')) ?: DEFAULT_CONTACT_INTRO;
            }

            $totalEmails = count($recipients);
            $batchSize = 20;
            $pauseDuration = 5; // seconds

            for ($i = 0; $i < $totalEmails; $i++) {
                $recipient = $recipients[$i];
                $recipientEmail = (string) $recipient['email'];
                $fullName = (string) $recipient['full_name'];
                $unsubscribe_token = (string) ($recipient['unsubscribe_token'] ?? '');


                $name = explode(' ', $fullName)[0];

                // validar el email antes de intentar enviar
                if (empty($recipientEmail) || !filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
                    $message .= '<p class="text-warning">Dirección inválida omitida: ' . htmlspecialchars($recipientEmail) . '</p>';
                    continue;
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

                    if ($event_data) {
                        $user_name_short = explode(' ', $name)[0];
                        // 1. Procesamos la fecha de inicio
                        $start_dt = new DateTime($event_data['start_datetime'], new DateTimeZone('UTC'));
                        $start_dt->setTimezone(new DateTimeZone('Europe/Madrid'));
                        $formatted_date = $start_dt->format('d/m/Y H:i');

                        // 2. Si existe fecha de fin, la procesamos y concatenamos
                        if (!empty($event_data['end_datetime'])) {
                            $end_dt = new DateTime($event_data['end_datetime'], new DateTimeZone('UTC'));
                            $end_dt->setTimezone(new DateTimeZone('Europe/Madrid'));
                            $formatted_date .= " - " . $end_dt->format('d/m/Y H:i');
                        }
                        $image_url = cdn_from_image_path($event_data['image_path']);

                        // generate Calendar Link
                        $madridTz = new DateTimeZone('Europe/Madrid');
                        $utcTz = new DateTimeZone('UTC');

                        // 1. Leemos la fecha de la DB sabiendo que es UTC
                        $startDate = new DateTime($event_data['start_datetime'], $utcTz);
                        $start_utc = $startDate->format('Ymd\THis\Z');

                        if (!empty($event_data['end_datetime'])) {
                            // 2. Leemos la fecha de fin de la DB sabiendo que es UTC
                            $endDate = new DateTime($event_data['end_datetime'], $utcTz);
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
                        $htmlContent = str_replace('{{calendar_link}}', $calendar_link, $htmlContent);
                    }

                    if ($recipient_group === 'contacts') {
                        $htmlContent = wrap_for_contact($htmlContent, $contact_intro, $recipient, $event_data);
                    }

                    $mail->Body = $htmlContent;

                    $mail->send();
                    $message .= '<p class="text-success">Mensaje enviado a ' . $recipientEmail . ' (' . ($i + 1) . '/' . $totalEmails . ')</p>';

                    if ($recipient_group === 'pending_qrs') {
                        $sql_update_sent = "UPDATE event_registrations SET qr_email_sent = TRUE WHERE event_id = ? AND email = ?";
                        $stmt_update_sent = $conn->prepare($sql_update_sent);
                        if ($stmt_update_sent) {
                            $stmt_update_sent->bind_param("is", $event_id, $recipientEmail);
                            $stmt_update_sent->execute();
                            $stmt_update_sent->close();
                        }
                    }

                    if ($recipient_group === 'contacts') {
                        $stmt_contact_log = $conn->prepare("INSERT INTO contact_email_logs (contact_id, template_name) VALUES (?, ?)");
                        if ($stmt_contact_log) {
                            $stmt_contact_log->bind_param("is", $recipient['contact_id'], $mail_template);
                            $stmt_contact_log->execute();
                            $stmt_contact_log->close();
                        }
                    }

                    // Update newsletter_logs if sending newsletter
                    if ($recipient_group === 'newsletter') {
                        $sql_update_newsletter = "INSERT INTO newsletter_logs (email, template_name, sent_at) VALUES (?, ?, NOW())";
                        $stmt_update_newsletter = $conn->prepare($sql_update_newsletter);
                        if ($stmt_update_newsletter) {
                            $stmt_update_newsletter->bind_param("ss", $recipientEmail, $mail_template);
                            $stmt_update_newsletter->execute();
                            $stmt_update_newsletter->close();
                        }
                    }

                } catch (Exception $e) {
                    $message .= '<p class="text-danger">Error al enviar a ' . $recipientEmail . '. Mailer Error: ' . $mail->ErrorInfo . '</p>';
                    error_log('PHPMailer Exception: Error enviando correo a ' . $recipientEmail . '. Error: ' . $e->getMessage());
                }

                // Pause between batches
                if (($i + 1) % $batchSize == 0 && ($i + 1) < $totalEmails) {
                    $message .= "<p class='text-info'>Pausando por $pauseDuration segundos antes del siguiente lote...</p>";
                    if (ob_get_level())
                        ob_flush();
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
    <main style="flex: 1;" class="scroll-margin">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <h1 class="text-center text-dark">Enviar Email de prueba</h1>
                <p class="text-center text-dark">Envía un correo electrónico de prueba a los destinatarios
                    seleccionados.</p>
                <div class="text-left">
                    <!-- Lista de tipos de destinatarios -->
                    <div class="mb-4 text-dark">
                        <p>Tipos de destinatarios:</p>
                        <ul>
                            <li><strong>Buscar un email:</strong> Envía un mensaje a una dirección de email específica.
                            </li>
                            <li><strong>Miembros del equipo:</strong> Envía un mensaje a todos los miembros del equipo.
                            </li>
                            <li><strong>Miembros del equipo web:</strong> Envía un mensaje solo a los miembros del
                                equipo web.</li>
                            <li><strong>Usuarios registrados en el evento:</strong> Envía un mensaje a todos los usuarios
                                registrados en el evento especificado.</li>
                            <li><strong>Enviar QR pendientes:</strong> Envía el correo y marca como enviado el QR a los usuarios que aún no lo tienen.</li>
                            <li><strong>Todos:</strong> Envía un mensaje a todos los correos registrados en la base de
                                datos.</li>
                            <li><strong>Contactos:</strong> Envía la plantilla elegida a los contactos seleccionados
                                (directores de titulación, profesores...) con un texto personalizado pidiendo su difusión.</li>

                        </ul>
                    </div>
                    <form method="post" action="">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>">
                        <div class="mb-3">

                            <label for="recipients" class="form-label">Seleccionar destinatarios:</label>
                            <select name="recipients" id="recipients" class="form-select">
                                <option value="search">Buscar un email</option>
                                <option value="team">Miembros del equipo</option>
                                <option value="web_team">Miembros del equipo web</option>
                                <option value="event_users">Usuarios registrados en el evento</option>
                                <option value="pending_qrs">Enviar QR pendientes</option>
                                <option value="newsletter">Newsletter</option>
                                <option value="contacts">Contactos (directores, profesores...)</option>
                                <option value="all">Todos</option>
                            </select>
                        </div>
                        <div class="mb-3" id="email_search_container">
                            <label for="email_search" class="form-label">Buscar un email:</label>
                            <input type="text" name="email_search" id="email_search" class="form-control"
                                placeholder="Introducir dirección de email">
                        </div>
                        <div class="mb-3 border rounded p-3 bg-light" id="contacts_container" style="display:none;">
                            <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                                <select id="contact_category_filter" class="form-select w-auto">
                                    <option value="">Todas las categorías</option>
                                    <?php foreach (CONTACT_CATEGORIES as $key => $label): ?>
                                        <option value="<?= $key ?>"><?= htmlspecialchars($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="text" id="contact_search" class="form-control w-auto flex-grow-1"
                                    placeholder="Buscar por nombre, email o titulación">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="contacts_select_visible">Seleccionar visibles</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="contacts_select_none">Ninguno</button>
                                <span class="text-dark small"><span id="contacts_selected_count">0</span> seleccionados</span>
                            </div>
                            <div style="max-height:350px; overflow-y:auto;">
                                <table class="table table-sm table-hover align-middle mb-0">
                                    <thead class="table-light" style="position:sticky; top:0;">
                                        <tr>
                                            <th></th>
                                            <th>Nombre</th>
                                            <th>Titulación / organización</th>
                                            <th>Email</th>
                                            <th>Esta plantilla</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($contacts)): ?>
                                            <tr><td colspan="5" class="text-center text-muted">No hay contactos. Añádelos en <a href="/dashboard/contacts/contacts_list.php">Contactos</a>.</td></tr>
                                        <?php endif; ?>
                                        <?php foreach ($contacts as $contact): ?>
                                            <tr class="contact-row" data-category="<?= htmlspecialchars($contact['category']) ?>"
                                                data-search="<?= htmlspecialchars(mb_strtolower($contact['full_name'] . ' ' . $contact['email'] . ' ' . $contact['organization'])) ?>"
                                                data-sent="<?= htmlspecialchars(json_encode($contact['sent_templates'])) ?>">
                                                <td><input type="checkbox" class="form-check-input contact-checkbox" name="contact_ids[]" value="<?= $contact['id'] ?>"></td>
                                                <td><?= htmlspecialchars($contact['full_name']) ?><br>
                                                    <small class="text-muted"><?= htmlspecialchars(contact_category_label($contact['category'])) ?></small></td>
                                                <td><small><?= htmlspecialchars($contact['organization'] ?? '') ?></small></td>
                                                <td><small><?= htmlspecialchars($contact['email']) ?></small></td>
                                                <td class="contact-sent small"></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <label for="contact_intro" class="form-label mt-3">Texto previo (antes del email a difundir).
                                Se sustituyen <code>{{contact_name}}</code>, <code>{{organization}}</code> (por contacto) y
                                <code>{{event_speaker}}</code>, <code>{{event_date}}</code>, <code>{{event_time}}</code>,
                                <code>{{event_location}}</code>, <code>{{event_name}}</code> (del evento seleccionado arriba):</label>
                            <textarea name="contact_intro" id="contact_intro" class="form-control" rows="6"><?= htmlspecialchars(DEFAULT_CONTACT_INTRO) ?></textarea>
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
                            <input type="text" name="mail_subject" id="mail_subject" class="form-control"
                                placeholder="Introducir asunto del email" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" name="submit" class="btn btn-primary">Enviar Email</button>
                        </div>
                    </form>
                    <div class="mt-4" id="preview">
                        <h3 class="text-dark">Vista previa:</h3>
                        <iframe id="preview_frame" src="" width="100%" height="500px"
                            style="border: 1px solid #ccc;"></iframe>
                    </div>
                    <div class="mt-4" id="message">
                        <?php echo $message; ?>
                    </div>

                </div>
            </div>
    </main>

    <?php include('../assets/footer.php'); ?>
    <script>
        document.getElementById('mail_template').addEventListener('change', function () {
            var selected_file = this.value;
            document.getElementById('preview_frame').src = selected_file;
        });

        document.getElementById('mail_template').dispatchEvent(new Event('change'));

        // --- Contactos ---
        const contactsContainer = document.getElementById('contacts_container');
        const contactRows = Array.from(document.querySelectorAll('.contact-row'));
        const categoryFilter = document.getElementById('contact_category_filter');
        const contactSearch = document.getElementById('contact_search');
        const selectedCount = document.getElementById('contacts_selected_count');

        document.getElementById('recipients').addEventListener('change', function () {
            contactsContainer.style.display = this.value === 'contacts' ? 'block' : 'none';
        });

        function filterContacts() {
            const category = categoryFilter.value;
            const term = contactSearch.value.trim().toLowerCase();
            contactRows.forEach(row => {
                const visible = (!category || row.dataset.category === category)
                    && (!term || row.dataset.search.includes(term));
                row.style.display = visible ? '' : 'none';
            });
        }

        function updateSelectedCount() {
            selectedCount.textContent = document.querySelectorAll('.contact-checkbox:checked').length;
        }

        function updateSentStatus() {
            const template = document.getElementById('mail_template').value;
            contactRows.forEach(row => {
                const sent = JSON.parse(row.dataset.sent || '{}');
                row.querySelector('.contact-sent').innerHTML = sent[template]
                    ? '<span class="badge bg-warning text-dark">Enviado ' + sent[template].substring(0, 10) + '</span>'
                    : '<span class="text-muted">—</span>';
            });
        }

        categoryFilter.addEventListener('change', filterContacts);
        contactSearch.addEventListener('input', filterContacts);
        document.querySelectorAll('.contact-checkbox').forEach(cb => cb.addEventListener('change', updateSelectedCount));

        document.getElementById('contacts_select_visible').addEventListener('click', function () {
            contactRows.forEach(row => {
                if (row.style.display !== 'none') row.querySelector('.contact-checkbox').checked = true;
            });
            updateSelectedCount();
        });
        document.getElementById('contacts_select_none').addEventListener('click', function () {
            document.querySelectorAll('.contact-checkbox').forEach(cb => cb.checked = false);
            updateSelectedCount();
        });

        document.getElementById('mail_template').addEventListener('change', updateSentStatus);
        updateSentStatus();
    </script>
</body>

</html>