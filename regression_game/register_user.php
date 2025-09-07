<?php
session_start();
include("../assets/db.php");

$email     = trim($_POST['email'] ?? '');
$full_name = trim($_POST['full_name'] ?? '');

// Validate email
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email']);
    exit;
}

// 1. Check if user already registered
$stmt = $conn->prepare("SELECT id, full_name FROM form_submissions WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    $user_id = $user['id'];
    $name    = $user['full_name'];
} else {
    // If full_name provided, register new user
    if ($full_name !== '') {
        $token = bin2hex(random_bytes(16));
        $stmtInsert = $conn->prepare("INSERT INTO form_submissions (full_name, email, newsletter, unsubscribe_token) VALUES (?, ?, 'yes', ?)");
        $stmtInsert->bind_param("sss", $full_name, $email, $token);
        if ($stmtInsert->execute()) {
            $user_id = $stmtInsert->insert_id;
            $name = $full_name;

            if ($stmt->execute()) {
                // Enviar correo
                $mail = new PHPMailer;
                $mail->CharSet = 'UTF-8';
                $mail->isSMTP();
                $mail->SMTPDebug = 0;
                $mail->Host = 'smtp.hostinger.com';
                $mail->Port = 587;
                $mail->SMTPAuth = true;

                $config = include('../config.php');
                $mail->Username = $config['smtp_user'];
                $mail->Password = $config['smtp_pass'];
                $mail->setFrom('info@aiscmadrid.com', 'AISC Madrid');
                $mail->addReplyTo('aisc.asoc@uc3m.es', 'AISC Madrid');
                $mail->addAddress($email, explode(' ', $name)[0]);
                $mail->Subject = '¡Bienvenid@ a la comunidad AISC Madrid!';

                $htmlContent = "
    <html>
    <head><title>¡Bienvenid@ a la comunidad AISC Madrid!</title></head>
    <body>
      <h2>¡Hola " . explode(' ', $name)[0] . "!</h2>
      <p>Gracias por unirte a la newsletter de <strong>AISC Madrid</strong>.</p>
      <p>A partir de ahora, recibirás noticias sobre nuestros próximos eventos, talleres y actividades.</p>
      <p>Estamos encantados de tenerte con nosotros.</p>
      <p>Únete al canal de WhatsApp para ser parte de la comunidad AISC Madrid y enterarte de los eventos y oportunidades en exclusiva:</p>

      <a href='https://chat.whatsapp.com/BpdXitZhwGCCpErwBoj3hv?mode=r_c'
      target='_blank'
      style='display: inline-flex; align-items: center; gap: 10px; padding: 10px 20px; background-color: #25D366; color: white !important; text-decoration: none; border-radius: 5px; font-weight: bold; font-family: Arial, sans-serif; margin-top: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'>Únete a la comunidad AISC Madrid en WhatsApp</a>

      <br><br>

      <p>Síguenos también en redes sociales:</p>

      <a href='https://instagram.com/aisc_madrid' target='_blank' style='display: inline-flex; align-items: center; gap: 10px; padding: 10px 20px; background-color: #D43089; color: white !important; text-decoration: none; border-radius: 5px; font-weight: bold; font-family: Arial, sans-serif; margin-top: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'>Instagram</a>

      <a href='https://www.linkedin.com/company/ai-student-collective-madrid/' target='_blank' style='display: inline-flex; align-items: center; gap: 10px; padding: 10px 20px; background-color: #0B66C3; color: white !important; text-decoration: none; border-radius: 5px; font-weight: bold; font-family: Arial, sans-serif; margin-top: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'>LinkedIn</a>

      <p>Nos vemos pronto,<br>Equipo de AISC UC3M</p>

      <div style='text-align:right; margin-top:30px;'>
          <a href='https://aiscmadrid.com/processing/unsubscribe.php?token=" . urlencode($token) . "' style='color: gray; text-decoration: none; font-family: Arial, sans-serif; font-size: 12px;'>Cancelar suscripción Newsletter</a>
      </div>
    </body>
    </html>";

                $mail->isHTML(true);
                $mail->Body = $htmlContent;
                $mail->AltBody = "Hola " . explode(' ', $name)[0] . ", gracias por unirte a la comunidad AISC Madrid.";
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to register user']);
            exit;
        }
    } else {
        // No full name → email not registered
        echo json_encode(['success' => false, 'message' => 'Not registered']);
        exit;
    }
}

// 2. Check if user already submitted a guess
$stmt = $conn->prepare("SELECT COUNT(*) as c FROM regression_guesses WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$already_played = ($row['c'] > 0);

// Save session info
$_SESSION['player_id']    = $user_id;
$_SESSION['player_name']  = $name;
$_SESSION['player_email'] = $email;

// Return success
echo json_encode([
    'success' => true,
    'user_id' => $user_id,
    'full_name' => $name,
    'email' => $email,
    'already_played' => $already_played
]);

$conn->close();
