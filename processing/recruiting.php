<?php
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$campus = trim($_POST['campus'] ?? '');
$position = trim($_POST['position'] ?? '');
$reason = trim($_POST['reason'] ?? '');
$consent = isset($_POST['consent']) ? 1 : 0;

$errors = [];

// Validación campo por campo
if ($name === '')
    $errors['error_name'] = 1;
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL))
    $errors['error_email'] = 1;
if ($campus === '')
    $errors['error_campus'] = 1;
if ($position === '')
    $errors['error_position'] = 1;
if ($reason === '' || strlen($reason) > 1000)
    $errors['error_interest'] = 1;
if ($consent !== 1)
    $errors['error_consent'] = 1;

// Si hay errores, redirigir a join.php con errores y valores previos
if (!empty($errors)) {
    $query = http_build_query(array_merge($errors, [
        'name' => $name,
        'email' => $email,
        'campus' => $campus,
        'position' => $position,
        'interest' => $reason,
        'consent' => $consent
    ]));
    header("Location: /join.php?$query#recruiting-form");
    exit;
}

include("../assets/db.php");

// Verificar si el correo ya está en DB
$checkStmt = $conn->prepare("SELECT id FROM recruiting_2026 WHERE email = ?");
$checkStmt->bind_param("s", $email);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    $checkStmt->close();
    $conn->close();
    header("Location: /join.php?error_duplicate=1&name=$name&email=$email&campus=$campus&position=$position&interest=$reason&consent=$consent#recruiting-form");
    exit;
}
$checkStmt->close();

// Insertar en la DB
$stmt = $conn->prepare("INSERT INTO recruiting_2026 (full_name, email, campus, position, interest) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $name, $email, $campus, $position, $reason);
$stmt->execute();
$stmt->close();

// Save in form_submissions (newsletter) table if not already present
$checkForm = $conn->prepare("SELECT id FROM form_submissions WHERE email = ?");
$checkForm->bind_param("s", $email);
$checkForm->execute();
$checkForm->store_result();

if ($checkForm->num_rows === 0) {
    $checkForm->close();

    // Generate unsubscribe token
    $unsubscribe_token = bin2hex(random_bytes(16));

    $stmt2 = $conn->prepare("INSERT INTO form_submissions (full_name, email, unsubscribe_token) VALUES (?, ?, ?)");
    $stmt2->bind_param("sss", $name, $email, $unsubscribe_token);
    $stmt2->execute();
    $stmt2->close();
} else {
    $checkForm->close();
}
// Get board members for email CC before closing connection
$boardMembers = [];
$boardQuery = $conn->prepare("SELECT full_name, mail FROM members WHERE board = 'yes'");
$boardQuery->execute();
$boardResult = $boardQuery->get_result();
while ($row = $boardResult->fetch_assoc()) {
    $boardMembers[] = ['name' => $row['full_name'], 'email' => $row['mail']];
}
$boardQuery->close();

$conn->close();

// Send notification email to AISC team via Gmail SMTP
try {
    $config = include('../config.php');

    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8';
    $mail->isSMTP();
    $mail->SMTPDebug = 0;
    $mail->Host = 'smtp.gmail.com';
    $mail->Port = 587;
    $mail->SMTPSecure = 'tls';
    $mail->SMTPAuth = true;
    $mail->Username = $config['smtp_user'];
    $mail->Password = $config['smtp_pass'];

    $mail->setFrom($config['smtp_user'], 'AISC Madrid Recruiting');
    $mail->addAddress('aisc.asoc@uc3m.es', 'AISC Madrid');

    // Add board members as CC dynamically
    foreach ($boardMembers as $member) {
        if (!empty($member['email'])) {
            $mail->addCC($member['email'], $member['name']);
        }
    }

    $mail->Subject = 'Nueva solicitud Recruiting 2026: ' . $name;

    $positionLabels = [
        'marketing' => 'Eventos y talleres',
        'events' => 'Marketing Digital',
        'tech' => 'Desarrollo Web',
        'finance' => 'Gestión y finanzas'
    ];
    $positionDisplay = $positionLabels[$position] ?? $position;

    $campusLabels = [
        'getafe' => 'Getafe',
        'leganes' => 'Leganés',
        'puertatoledo' => 'Puerta de Toledo',
        'colmenarejo' => 'Colmenarejo'
    ];
    $campusDisplay = $campusLabels[$campus] ?? $campus;

    $htmlContent = "
    <html>
    <head><title>Nueva solicitud Recruiting 2026</title></head>
    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
        <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
            <h2 style='color: #EB178E; border-bottom: 2px solid #20CCF1; padding-bottom: 10px;'>
                🎯 Nueva solicitud de Recruiting 2026
            </h2>
            
            <table style='width: 100%; border-collapse: collapse; margin-top: 20px;'>
                <tr>
                    <td style='padding: 10px; background: #f5f5f5; font-weight: bold; width: 30%;'>Nombre</td>
                    <td style='padding: 10px; border-bottom: 1px solid #eee;'>" . htmlspecialchars($name) . "</td>
                </tr>
                <tr>
                    <td style='padding: 10px; background: #f5f5f5; font-weight: bold;'>Email</td>
                    <td style='padding: 10px; border-bottom: 1px solid #eee;'>
                        <a href='mailto:" . htmlspecialchars($email) . "'>" . htmlspecialchars($email) . "</a>
                    </td>
                </tr>
                <tr>
                    <td style='padding: 10px; background: #f5f5f5; font-weight: bold;'>Campus</td>
                    <td style='padding: 10px; border-bottom: 1px solid #eee;'>" . htmlspecialchars($campusDisplay) . "</td>
                </tr>
                <tr>
                    <td style='padding: 10px; background: #f5f5f5; font-weight: bold;'>Posición</td>
                    <td style='padding: 10px; border-bottom: 1px solid #eee;'>" . htmlspecialchars($positionDisplay) . "</td>
                </tr>
            </table>
            
            <div style='margin-top: 20px; padding: 15px; background: #f9f9f9; border-left: 4px solid #20CCF1;'>
                <h4 style='margin: 0 0 10px 0; color: #20CCF1;'>Motivación:</h4>
                <p style='margin: 0; white-space: pre-wrap;'>" . htmlspecialchars($reason) . "</p>
            </div>
            
            <p style='margin-top: 30px; font-size: 12px; color: #888;'>
                Este correo se ha generado automáticamente desde el formulario de recruiting de aiscmadrid.com
            </p>
        </div>
    </body>
    </html>";

    $mail->isHTML(true);
    $mail->Body = $htmlContent;
    $mail->AltBody = "Nueva solicitud Recruiting 2026\n\nNombre: $name\nEmail: $email\nCampus: $campusDisplay\nPosición: $positionDisplay\n\nMotivación:\n$reason";

    $mail->send();
} catch (Exception $e) {
    // Log error but don't stop the flow
    error_log('Recruiting notification email error: ' . $e->getMessage());
}

// Send automated confirmation email to the applicant
try {
    // We already have $config and labels from the first block
    $mailApplicant = new PHPMailer(true);
    $mailApplicant->CharSet = 'UTF-8';
    $mailApplicant->isSMTP();
    $mailApplicant->Host = 'smtp.gmail.com';
    $mailApplicant->Port = 587;
    $mailApplicant->SMTPSecure = 'tls';
    $mailApplicant->SMTPAuth = true;
    $mailApplicant->Username = $config['smtp_user'];
    $mailApplicant->Password = $config['smtp_pass'];

    $mailApplicant->setFrom($config['smtp_user'], 'AISC Madrid');
    $mailApplicant->addAddress($email, $name);

    $mailApplicant->Subject = '¡Formulario recibido! | AISC Madrid';

    $htmlApplicant = "
    <html>
    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f9f9f9; padding: 20px;'>
        <div style='max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);'>
            <!-- Header -->
            <div style='background: #EB178E; padding: 30px; text-align: center;'>
                <h1 style='color: #ffffff; margin: 0; font-size: 24px;'>¡Hola, " . htmlspecialchars($name) . "!</h1>
            </div>
            
            <!-- Body -->
            <div style='padding: 30px;'>
                <p style='font-size: 16px;'>¡Gracias por tu interés en AISC Madrid! Hemos recibido correctamente tu solicitud para el equipo de <b>" . htmlspecialchars($positionDisplay) . "</b> y nos pondremos en contacto contigo en los próximos días.
                <br>Mientras tanto, sigue nuestras redes sociales para estar al tanto de las últimas novedades.</p>
            </div>

            <!-- Same footer as newsletter -->
            <div style='padding:0 30px 30px;'>
                <table role='presentation' width='100%' cellpadding='0' cellspacing='0' style='border-top:5px solid #EB178E; padding-top:20px; font-family:Arial, sans-serif; font-size:14px; color:#555555;'>
                    <tr>
                        <td align='center' style='padding:10px 0;'>
                            <!-- Web -->
                            <a href='https://aiscmadrid.com/' target='_blank' style='margin:0 15px; display:inline-block;'>
                                <img src='https://aiscmadrid.com/images/logos/PNG/internet-rosa.png' alt='Web' width='32' height='32' border='0'>
                            </a>
                            <!-- Instagram -->
                            <a href='https://www.instagram.com/aisc_madrid/' target='_blank' style='margin:0 15px; display:inline-block;'>
                                <img src='https://aiscmadrid.com/images/logos/PNG/instagram-rosa.png' alt='Instagram' width='32' height='32' border='0'>
                            </a>
                            <!-- WhatsApp -->
                            <a href='https://chat.whatsapp.com/BpdXitZhwGCCpErwBoj3hv?mode=wwt' target='_blank' style='margin:0 15px; display:inline-block;'>
                                <img src='https://aiscmadrid.com/images/logos/PNG/whatsapp-rosa.png' alt='WhatsApp' width='32' height='32' border='0'>
                            </a>
                            <!-- LinkedIn -->
                            <a href='https://www.linkedin.com/company/ai-student-collective-madrid/' target='_blank' style='margin:0 15px; display:inline-block;'>
                                <img src='https://aiscmadrid.com/images/logos/PNG/linkedin-rosa.png' alt='LinkedIn' width='32' height='32' border='0'>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td align='center' style='padding:10px;'>
                            <a href='https://aiscmadrid.com/' target='_blank'>
                                <img src='https://aiscmadrid.com/images/logos/PNG/AISCMadridLogoAndLetters.png' alt='AISC Madrid' width='250' border='0'>
                            </a>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </body>
    </html>";

    $mailApplicant->isHTML(true);
    $mailApplicant->Body = $htmlApplicant;
    $mailApplicant->AltBody = "¡Hola $name! Hemos recibido tu interés por AISC Madrid. Nos pondremos en contacto pronto.";

    $mailApplicant->send();
} catch (Exception $e) {
    error_log('Confirmation email to applicant error: ' . $e->getMessage());
}

// Send notification email to AISC team via Gmail SMTP
try {
    $config = include('../config.php');

    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8';
    $mail->isSMTP();
    $mail->SMTPDebug = 0;
    $mail->Host = 'smtp.gmail.com';
    $mail->Port = 587;
    $mail->SMTPSecure = 'tls';
    $mail->SMTPAuth = true;
    $mail->Username = $config['smtp_user'];
    $mail->Password = $config['smtp_pass'];

    $mail->setFrom($config['smtp_user'], 'AISC Madrid Recruiting');
    $mail->addAddress('aisc.asoc@uc3m.es', 'AISC Madrid');

    // Add board members as CC dynamically
    foreach ($boardMembers as $member) {
        if (!empty($member['email'])) {
            $mail->addCC($member['email'], $member['name']);
        }
    }

    //Temporal: add Juanjo and Álvaro until theh have board positions
    $mail->addCC('juanjose.rosales@alumnos.uc3m.es', 'Juanjo');
    $mail->addCC('alvaro.artano@alumnos.uc3m.es', 'Álvaro');
    $mail->Subject = 'Nueva solicitud Recruiting 2026: ' . $name;

    $positionLabels = [
        'marketing' => 'Eventos y talleres',
        'events' => 'Marketing Digital',
        'tech' => 'Desarrollo Web',
        'finance' => 'Gestión y finanzas'
    ];
    $positionDisplay = $positionLabels[$position] ?? $position;

    $campusLabels = [
        'getafe' => 'Getafe',
        'leganes' => 'Leganés',
        'puertatoledo' => 'Puerta de Toledo',
        'colmenarejo' => 'Colmenarejo'
    ];
    $campusDisplay = $campusLabels[$campus] ?? $campus;

    $htmlContent = "
    <html>
    <head><title>Nueva solicitud Recruiting 2026</title></head>
    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
        <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
            <h2 style='color: #EB178E; border-bottom: 2px solid #20CCF1; padding-bottom: 10px;'>
                🎯 Nueva solicitud de Recruiting 2026
            </h2>
            
            <table style='width: 100%; border-collapse: collapse; margin-top: 20px;'>
                <tr>
                    <td style='padding: 10px; background: #f5f5f5; font-weight: bold; width: 30%;'>Nombre</td>
                    <td style='padding: 10px; border-bottom: 1px solid #eee;'>" . htmlspecialchars($name) . "</td>
                </tr>
                <tr>
                    <td style='padding: 10px; background: #f5f5f5; font-weight: bold;'>Email</td>
                    <td style='padding: 10px; border-bottom: 1px solid #eee;'>
                        <a href='mailto:" . htmlspecialchars($email) . "'>" . htmlspecialchars($email) . "</a>
                    </td>
                </tr>
                <tr>
                    <td style='padding: 10px; background: #f5f5f5; font-weight: bold;'>Campus</td>
                    <td style='padding: 10px; border-bottom: 1px solid #eee;'>" . htmlspecialchars($campusDisplay) . "</td>
                </tr>
                <tr>
                    <td style='padding: 10px; background: #f5f5f5; font-weight: bold;'>Posición</td>
                    <td style='padding: 10px; border-bottom: 1px solid #eee;'>" . htmlspecialchars($positionDisplay) . "</td>
                </tr>
            </table>
            
            <div style='margin-top: 20px; padding: 15px; background: #f9f9f9; border-left: 4px solid #20CCF1;'>
                <h4 style='margin: 0 0 10px 0; color: #20CCF1;'>Motivación:</h4>
                <p style='margin: 0; white-space: pre-wrap;'>" . htmlspecialchars($reason) . "</p>
            </div>
        </div>
    </body>
    </html>";

    $mail->isHTML(true);
    $mail->Body = $htmlContent;
    $mail->AltBody = "Nueva solicitud Recruiting 2026\n\nNombre: $name\nEmail: $email\nCampus: $campusDisplay\nPosición: $positionDisplay\n\nMotivación:\n$reason";

    $mail->send();
} catch (Exception $e) {
    // Log error but don't stop the flow
    error_log('Recruiting notification email error: ' . $e->getMessage());
}

// Success redirect
header("Location: /join.php?success=1#recruiting-form");
exit;
?>