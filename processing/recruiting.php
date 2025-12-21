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
        'tech' => 'Desarrollo Web'
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

// Success redirect
header("Location: /join.php?success=1#recruiting-form");
exit;
?>