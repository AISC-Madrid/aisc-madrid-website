<?php
session_start();
require __DIR__ . '/../../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$config = include(__DIR__ . "/../../config.php");
$allowed_roles = ['admin', 'finance'];
if (!isset($_SESSION['activated']) || !in_array($_SESSION['role'], $allowed_roles)) {
    http_response_code(403);
    die("Acceso no autorizado");
}

include(__DIR__ . "/../../assets/db.php");

$password = $_POST['password'] ?? '';
if ($password === '') {
    die("<p style='color:red;'>❌ Error: La contraseña es obligatoria.</p>");
}

if (strlen($password) < 6) {
    die("<p style='color:red;'>❌ Error: La contraseña debe tener al menos 6 caracteres.</p>");
}

// Prepare SQL
$password_hash = password_hash($password, PASSWORD_DEFAULT);
$sql = "INSERT INTO members (
    full_name,
    mail,
    password_hash,
    position_es, position_en,
    phone,
    dni,
    socials,
    board,
    active,
    image_path,
    role
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

// Prepare statement
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Error al preparar la consulta: " . $conn->error);
}

// Bind parameters
$stmt->bind_param(
    "ssssssssssss",
    $_POST['full_name'],
    $_POST['mail'],
    $password_hash,
    $_POST['position_es'],
    $_POST['position_en'],
    $_POST['phone'],
    $_POST['dni'],
    $_POST['socials'],
    $_POST['board'],
    $_POST['active'],
    $_POST['image_path'],
    $_POST['role']
);

// Execute
if ($stmt->execute()) {
    echo "<p style='color:green;'>✅ Miembro guardado correctamente.</p>";

    // Send Welcome Email
    $mail = new PHPMailer(true);
    try {
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 587;
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'tls';
        $mail->Username = $config['smtp_user'];
        $mail->Password = $config['smtp_pass'];
        
        $mail->setFrom($config['smtp_user'], 'AISC Madrid');
        $mail->addAddress($_POST['mail'], $_POST['full_name']);
        
        $mail->isHTML(true);
        $mail->Subject = 'Bienvenid@ a AISC Madrid - Tus credenciales';
        
        $templatePath = __DIR__ . '/../../mails/welcome_member.html';
        if (file_exists($templatePath)) {
            $body = file_get_contents($templatePath);
            
            $firstName = explode(' ', trim($_POST['full_name']))[0];
            
            $body = str_replace('{{name}}', $firstName, $body);
            $body = str_replace('{{email}}', $_POST['mail'], $body);
            $body = str_replace('{{password}}', $password, $body);
            $body = str_replace('{{login_url}}', $config['base_url'] . 'login.php', $body);
            
            $mail->Body = $body;
            $mail->send();
            echo "<p style='color:green;'>📧 Email de bienvenida enviado correctamente.</p>";
        } else {
             echo "<p style='color:orange;'>⚠️ Email no enviado: No se encontró la plantilla.</p>";
        }

    } catch (Exception $e) {
        echo "<p style='color:orange;'>⚠️ Miembro guardado, pero error al enviar email: {$mail->ErrorInfo}</p>";
    }

    echo "<a href='team_members_list.php'>Añadir otro miembro</a>";
} else {
    echo "<p style='color:red;'>❌ Error: " . $stmt->error . "</p>";
}

// Close
$stmt->close();
$conn->close();
?>