<?php
// Página unificada de correo: junta "Crear Email" (mail-builder) y "Probar Envío de
// Correo" en una sola entrada de menú, con pestañas. Cada pestaña conserva la lógica
// original de mail_creation.php / send_test_email.php tal cual, solo movida aquí.
session_start();

$allowed_roles = ['admin', 'web'];
if (!isset($_SESSION['activated']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: /");
    exit();
}

require __DIR__ . '/../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$view = ($_GET['view'] ?? 'crear') === 'probar' ? 'probar' : 'crear';

// ============================================================================
// Pestaña "Probar Envío de Correo" — lógica de send_test_email.php sin cambios,
// solo se ejecuta cuando esta pestaña está activa.
// ============================================================================
if ($view === 'probar') {
    $config = include(__DIR__ . '/../config.php');
    require_once __DIR__ . '/../assets/csrf.php';
    require_once __DIR__ . '/../assets/db.php';
    require_once __DIR__ . '/../assets/contacts.php';
    require_once __DIR__ . '/../assets/cloudinary.php';

    $recipients = [];
    $message = '';
    $mail_files = glob('../mails/*/*.html');
    $events = $conn->query("SELECT id, title_es, title_en FROM events ORDER BY start_datetime DESC");

    define('DEFAULT_CONTACT_INTRO', "Buenos días {{contact_name}},\n\n"
        . "En el próximo evento de la asociación universitaria AI Student Collective recibimos la visita de {{event_speaker}} de {{event_company}}. Creemos que puede ser de gran interés para los alumnos del {{organization}}.\n"
        . "El evento se celebrará el próximo {{event_date}} a las {{event_time}} en {{event_location}}.\n\n"
        . "Al igual que en eventos anteriores, es una gran oportunidad para que los estudiantes puedan aprender sobre el sector y conectar con profesionales.\n\n"
        . "Te mando a continuación el mensaje que me gustaría que copiaras y pegaras para mandar.\n\n"
        . "Y nos gustaría agradecerte el apoyo que estás brindando a la asociación.\n\n"
        . "Muchas gracias.");

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
     * {{event_name}}, {{event_speaker}}, {{event_company}}, {{event_date}} ("lunes 17 de noviembre"), {{event_time}}, {{event_location}}.
     */
    function wrap_for_contact(string $html, string $intro, array $contact, ?array $event): string
    {
        // Drop the connector words when a value is missing, so the sentence still reads well
        $organization = trim((string) ($contact['organization'] ?? ''));
        if ($organization === '') {
            $intro = str_replace(' del {{organization}}', '', $intro);
        }
        if (trim((string) ($event['company'] ?? '')) === '') {
            $intro = str_replace(' de {{event_company}}', '', $intro);
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
                '{{event_company}}' => trim((string) ($event['company'] ?? '')),
                '{{event_date}}' => spanish_long_date($start),
                '{{event_time}}' => $start->format('H:i'),
                '{{event_location}}' => (string) ($event['location'] ?? ''),
            ];
        }

        $intro = nl2br(htmlspecialchars($intro));
        $intro = str_replace(array_keys($values), array_map('htmlspecialchars', array_values($values)), $intro);

        $header = '<div style="font-family:Arial,sans-serif; font-size:15px; color:#222; max-width:600px; margin:0 auto 24px auto; padding:16px; text-align:left; line-height:1.5;">' . $intro . '</div>'
            . '<div style="max-width:600px; margin:0 auto 16px auto; border-bottom:1px solid #ccc; padding-top:8px; font-family:Arial,sans-serif; font-size:12px; color:#888; text-align:center;">Mensaje para difundir</div>';

        if (preg_match('/<body\b[^>]*>/i', $html)) {
            return preg_replace_callback('/<body\b[^>]*>/i', fn($m) => $m[0] . $header, $html, 1);
        }
        return $header . $html;
    }

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
            $stmt_event = $conn->prepare("SELECT title_es, speaker, company, start_datetime, end_datetime, location, image_path FROM events WHERE id = ?");
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

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Email - Dashboard - AISC Madrid</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/css/styles3.css">
    <style>
        :root { --aisc-pink: #EB178E; --aisc-blue: #20CCF1; }
        .builder-container { display: flex; min-height: 70vh; border: 1px solid #e9ecef; border-radius: 8px; overflow: hidden; margin-top: 1rem; }
        #editor { width: 50%; padding: 20px; overflow-y: auto; background: #fff; border-right: 2px solid #ddd; }
        #preview-pane { width: 50%; padding: 40px; overflow-y: auto; display: flex; flex-direction: column; align-items: center; background: #fafafa; }
        .chunk-card { border-left: 5px solid var(--aisc-pink); padding: 15px; margin-bottom: 15px; background: #fafafa; border-radius: 4px; position: relative; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .chunk-card input, .chunk-card textarea { width: 100%; margin: 5px 0; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        .btn-pink { background-color: var(--aisc-pink) !important; color: white !important; }
        .btn-blue { background-color: var(--aisc-blue) !important; color: white !important; }
        #email-preview-frame { background: #fff; width: 600px; min-height: 400px; box-shadow: 0 0 20px rgba(0,0,0,0.1); border-radius: 8px; }
        .btn-delete { position: absolute; top: 5px; right: 5px; border: none; background: none; font-size: 20px; color: #cc0000; }
    </style>
</head>

<body class="bg-light">
    <?php include __DIR__ . '/../assets/nav_dashboard.php'; ?>

    <div class="container-fluid scroll-margin" style="max-width: 1200px;">
        <h2 class="mb-3"><i class="bi bi-envelope-paper me-2"></i>Email</h2>

        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link <?= $view === 'crear' ? 'active' : '' ?>" href="?view=crear"><i class="bi bi-magic me-1"></i>Crear Email</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $view === 'probar' ? 'active' : '' ?>" href="?view=probar"><i class="bi bi-send me-1"></i>Probar Envío de Correo</a>
            </li>
        </ul>

        <?php if ($view === 'crear'): ?>
            <div class="builder-container">
                <div id="editor">
                    <h2 class="h4 mb-3" style="color: var(--aisc-pink); font-weight: bold;">AISC Mail-Builder</h2>

                    <div class="controls d-grid gap-2 mb-3" style="grid-template-columns: 1fr 1fr;">
                        <button class="btn btn-pink btn-sm" onclick="addChunk('header')">+ Título</button>
                        <button class="btn btn-pink btn-sm" onclick="addChunk('subheader')">+ Subtítulo</button>
                        <button class="btn btn-pink btn-sm" onclick="addChunk('image')">+ Imagen</button>
                        <button class="btn btn-pink btn-sm" onclick="addChunk('text')">+ Texto Principal</button>
                        <button class="btn btn-pink btn-sm" onclick="addChunk('event')">+ Información Evento</button>
                        <button class="btn btn-blue btn-sm" onclick="addChunk('button')">+ Botón</button>
                    </div>

                    <div id="chunks-container"></div>

                    <div class="p-3 mb-3 bg-light border rounded">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="show-footer" checked onchange="updatePreview()">
                            <label class="form-check-label fw-bold" for="show-footer">Incluir Footer AISC Madrid</label>
                        </div>
                    </div>

                    <hr>
                    <h5 class="h6">Código Final</h5>
                    <textarea id="code-output" class="form-control mb-2" style="height: 100px; font-size: 11px; font-family: monospace;" readonly onclick="this.select(); document.execCommand('copy');"></textarea>

                    <button class="btn btn-blue w-100" onclick="downloadHTML()">
                        <i class="bi bi-download"></i> Descargar HTML
                    </button>
                </div>

                <div id="preview-pane">
                    <div id="email-preview-frame">
                        <div id="email-preview"></div>
                    </div>
                </div>
            </div>

            <script>
                let chunks = [];

                function addChunk(type) {
                    const id = Date.now();
                    let data = { id, type };

                    if (type === 'header') data.val = 'Título';
                    if (type === 'subheader') data.val = 'Subtítulo Sección';
                    if (type === 'image') data.val = 'https://aiscmadrid.com/images/logos/SVG/AISCMadridLogoAndLetters.svg';
                    if (type === 'text') { data.sub = 'Subtítulo'; data.body = 'Información extra'; data.align = 'center'; }
                    if (type === 'event') { data.title = 'Título Evento'; data.info = 'Extra info'; data.date = '20 de enero'; data.time = '10:00h'; data.place = 'Aula ejemplo'; }
                    if (type === 'button') { data.text = 'Ver eventos'; data.url = 'https://aiscmadrid.com/#events'; }

                    chunks.push(data);
                    renderEditor();
                    updatePreview();
                }

                function updateChunk(id, field, value) {
                    const chunk = chunks.find(c => c.id === id);
                    if (chunk) chunk[field] = value;
                    updatePreview();
                }

                function removeChunk(id) {
                    chunks = chunks.filter(c => c.id !== id);
                    renderEditor();
                    updatePreview();
                }

                function moveChunk(id, direction) {
                    const index = chunks.findIndex(c => c.id === id);
                    if (index === -1) return;
                    const newIndex = direction === 'up' ? index - 1 : index + 1;
                    if (newIndex < 0 || newIndex >= chunks.length) return;

                    [chunks[index], chunks[newIndex]] = [chunks[newIndex], chunks[index]];
                    renderEditor();
                    updatePreview();
                }

                function insertBold(id) {
                    const textarea = document.getElementById(`txt-${id}`);
                    const start = textarea.selectionStart;
                    const end = textarea.selectionEnd;
                    const text = textarea.value;
                    const selectedText = text.substring(start, end);
                    const newText = text.substring(0, start) + "<b>" + selectedText + "</b>" + text.substring(end);

                    textarea.value = newText;
                    updateChunk(id, 'body', newText);
                    textarea.focus();
                }

                function renderEditor() {
                    const container = document.getElementById('chunks-container');
                    container.innerHTML = '';

                    chunks.forEach((c, index) => {
                        const card = document.createElement('div');
                        card.className = 'chunk-card';

                        let navButtons = `
                            <div style="position: absolute; top: 5px; right: 35px; display: flex; gap: 5px;">
                                <button class="p-0 border-0 bg-transparent" onclick="moveChunk(${c.id}, 'up')" ${index === 0 ? 'disabled' : ''} style="color: ${index === 0 ? '#ccc' : 'var(--aisc-pink)'}">▲</button>
                                <button class="p-0 border-0 bg-transparent" onclick="moveChunk(${c.id}, 'down')" ${index === chunks.length - 1 ? 'disabled' : ''} style="color: ${index === chunks.length - 1 ? '#ccc' : 'var(--aisc-pink)'}">▼</button>
                            </div>
                        `;

                        let html = `<button class="btn-delete" onclick="removeChunk(${c.id})">&times;</button>${navButtons}<small class="text-muted fw-bold">${c.type.toUpperCase()}</small>`;

                        if (c.type === 'header' || c.type === 'image' || c.type === 'subheader') {
                            html += `<input type="text" value="${c.val}" oninput="updateChunk(${c.id}, 'val', this.value)">`;
                        } else if (c.type === 'text') {
                            html += `<div class="mb-2">
                                        <small class="text-muted">Alineación:</small>
                                        <select class="form-select form-select-sm d-inline-block w-auto" onchange="updateChunk(${c.id}, 'align', this.value)">
                                            <option value="center" ${c.align === 'center' ? 'selected' : ''}>Centrado</option>
                                            <option value="left" ${c.align === 'left' ? 'selected' : ''}>Izquierda</option>
                                            <option value="right" ${c.align === 'right' ? 'selected' : ''}>Derecha</option>
                                        </select>
                                    </div>`;

                            html += `<input type="text" placeholder="Subtítulo" value="${c.sub || ''}" oninput="updateChunk(${c.id}, 'sub', this.value)">`;

                            html += `<button type="button" class="btn btn-light btn-sm mb-1" onclick="insertBold(${c.id})"><b>B</b> Bold</button>`;
                            html += `<textarea id="txt-${c.id}" oninput="updateChunk(${c.id}, 'body', this.value)">${c.body || ''}</textarea>`;
                        } else if (c.type === 'event') {
                            html += `<input type="text" placeholder="Título" value="${c.title || ''}" oninput="updateChunk(${c.id}, 'title', this.value)">`;
                            html += `<input type="text" placeholder="Info extra" value="${c.info || ''}" oninput="updateChunk(${c.id}, 'info', this.value)">`;
                            html += `<input type="text" placeholder="Fecha" value="${c.date || ''}" oninput="updateChunk(${c.id}, 'date', this.value)">`;
                            html += `<input type="text" placeholder="Hora" value="${c.time || ''}" oninput="updateChunk(${c.id}, 'time', this.value)">`;
                            html += `<input type="text" placeholder="Lugar" value="${c.place || ''}" oninput="updateChunk(${c.id}, 'place', this.value)">`;
                        } else if (c.type === 'button') {
                            html += `<input type="text" placeholder="Texto" value="${c.text || ''}" oninput="updateChunk(${c.id}, 'text', this.value)">`;
                            html += `<input type="text" placeholder="URL" value="${c.url || ''}" oninput="updateChunk(${c.id}, 'url', this.value)">`;
                        }

                        card.innerHTML = html;
                        container.appendChild(card);
                    });
                }

                function updatePreview() {
                    let email = `<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body style="margin:0; padding:0; font-family: Arial, sans-serif; background-color:#f4f4f4;">
                                    <table align="center" width="600" style="border-collapse: collapse; background-color:#ffffff; margin-top:20px; border-radius:8px; overflow:hidden;">`;

                    chunks.forEach(c => {
                        if (c.type === 'header') email += `<tr><td align="center" style="padding:20px; background-color:#EB178E; color:#ffffff;"><h1 style="margin:0; font-size:24px;">${c.val}</h1></td></tr>`;
                        if (c.type === 'subheader') email += `<tr><td align="center" style="padding:20px; color:#EB178E;"><h2 style="margin:0; font-size:22px;">${c.val}</h2><div style="margin-top:10px; width:80px; height:4px; background-color:#EB178E; border-radius:2px;"></div></td></tr>`;
                        if (c.type === 'image') email += `<tr><td align="center" style="padding:20px;"><img src="${c.val}" width="100%" style="max-width:560px; border-radius:6px; display:block;"></td></tr>`;
                        if (c.type === 'text') {
                            const alignment = c.align || 'center';
                            email += `<tr>
                                        <td style="padding:20px; color:#333333; font-size:16px; line-height:1.5; text-align: ${alignment};">
                                            <p><strong>${c.sub}</strong></p>
                                            <p>${c.body}</p>
                                        </td></tr>`;
                        }
                        if (c.type === 'event') email += `<tr><td style="padding:20px; color:#333333; font-size:16px; text-align:left; line-height:1.6;"><p><strong>${c.title}</strong></p><p>${c.info}</p><p style="margin:8px 0;">📅 <strong>Fecha:</strong> ${c.date}</p><p style="margin:8px 0;">⏰ <strong>Horario:</strong> ${c.time || 'Por determinar'}</p><p style="margin:8px 0;">📍 <strong>Lugar:</strong> ${c.place}</p></td></tr>`;
                        if (c.type === 'button') email += `<tr><td align="center" style="padding:20px;"><a href="${c.url}" style="background-color:#20CCF1; color:#ffffff; text-decoration:none; padding:12px 24px; border-radius:5px; display:inline-block; font-size:16px;">${c.text}</a></td></tr>`;
                    });

                    if (document.getElementById('show-footer').checked) {
                        email += `<tr>
                                        <td align="center" style="padding:0 20px;">
                                            <table role="presentation" width="550" cellpadding="0" cellspacing="0" align = "center" border="0"
                                                style="border-top:5px solid #EB178E; margin-top:20px; padding-top:20px; font-family:Arial, sans-serif; font-size:14px; color:#555555;">
                                                <tr>
                                                    <td style="margin-top:20px; padding:0px; color:#333333; font-size:16px; line-height:1.5;">
                                                        <p>Para <strong>enterarte de todos los eventos, workshops y oportunidades</strong> te recomendamos que estés atento y nos sigas por:</p>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td align="center" style="padding:10px 0;">
                                                    <!-- Web -->
                                                    <a href="https://aiscmadrid.com/" target="_blank" style="margin:0 20px; display:inline-block;">

                                                        <img src="https://aiscmadrid.com/images/logos/PNG/internet-rosa.png" alt="Button 1" width="32" height="32" border="0" style="display:block;">
                                                    </a>
                                                    <!-- Instagram -->
                                                    <a href="https://www.instagram.com/aisc_madrid/" target="_blank" style="margin:0 20px; display:inline-block;">
                                                        <img src="https://aiscmadrid.com/images/logos/PNG/instagram-rosa.png" alt="Button 2" width="32" height="32" border="0" style="display:block;">
                                                    </a>
                                                    <!-- WhatsApp -->
                                                    <a href="https://chat.whatsapp.com/BpdXitZhwGCCpErwBoj3hv?mode=wwt" target='_blank' style='margin:0 20px; display:inline-block;'>
                                                        <img src="https://aiscmadrid.com/images/logos/PNG/whatsapp-rosa.png" alt="Button 3" width='32' height='32' border="0" style="display:block;">
                                                    </a>
                                                    <!-- LinkedIn -->
                                                    <a href="https://www.linkedin.com/company/ai-student-collective-madrid/" target="_blank" style="margin:0 20px; display:inline-block;">
                                                        <img src="https://aiscmadrid.com/images/logos/PNG/linkedin-rosa.png" alt="Button 4" width="32" height="32" border="0" style="display:block;">
                                                    </a>
                                                    </td>
                                                </tr>
                                                <!-- Logo footer-->
                                                <tr>
                                                    <td align="center" style="padding:10px; padding-left:40px">
                                                        <a href="https://aiscmadrid.com/" target="_blank" style="margin:0 20px; display:inline-block;">
                                                            <img src="https://aiscmadrid.com/images/logos/PNG/AISCMadridLogoAndLetters.png" alt="Logo Footer" width="300" border="0" style="display:block;">
                                                        </a>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td align="center" style="color: gray; text-decoration: none; font-family: Arial, sans-serif; font-size: 12px; padding:10px;">
                                                        <a href='https://aiscmadrid.com/index.php#newsletter' style='color: gray; text-decoration: none; font-family: Arial, sans-serif; font-size: 12px;'>¿Te han reenviado la Newsletter? Suscríbete aquí</a>
                                                        |
                                                        <a href='https://aiscmadrid.com/processing/unsubscribe.php?token=$unsubscribe_token' style='color: gray; text-decoration: none; font-family: Arial, sans-serif; font-size: 12px;'>Cancelar suscripción Newsletter</a>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>`;
                    }

                    email += `</table></body></html>`;
                    document.getElementById('email-preview').innerHTML = email;
                    document.getElementById('code-output').value = email;
                }

                function downloadHTML() {
                    const htmlContent = document.getElementById('code-output').value;
                    if (!htmlContent) return alert("Crea contenido primero");
                    const blob = new Blob([htmlContent], { type: 'text/html' });
                    const link = document.createElement("a");
                    link.download = `newsletter_aisc_${new Date().toISOString().slice(0, 10)}.html`;
                    link.href = window.URL.createObjectURL(blob);
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }

                addChunk('header');
            </script>

        <?php else: ?>
            <div class="row justify-content-center">
                <h4 class="text-center text-dark">Enviar Email de prueba</h4>
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
                    <form method="post" action="?view=probar">
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
                                <code>{{event_speaker}}</code>, <code>{{event_company}}</code>, <code>{{event_date}}</code>, <code>{{event_time}}</code>,
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
            <script>
                const recipientSelect = document.getElementById("recipients");
                const emailSearchInput = document.getElementById("email_search_container");

                recipientSelect.addEventListener("change", function () {
                    emailSearchInput.style.display = recipientSelect.value === "search" ? "block" : "none";
                });

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

                recipientSelect.addEventListener('change', function () {
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
        <?php endif; ?>
    </div>

    <?php include __DIR__ . '/../assets/footer.php'; ?>
</body>

</html>
