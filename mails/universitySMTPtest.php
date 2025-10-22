<?php
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$config = include('../config.php'); // debe devolver smtp_user y smtp_pass

$testRecipients = [
    'hcienteno@gmail.com',
    '100498982@alumnos.uc3m.es'
];

try {
    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8';
    $mail->isSMTP();

    // Debug para pruebas (poner 2 o 3 mientras debuggeas)
    $mail->SMTPDebug = 2;                     // 0 = off, 1 = client, 2 = client+server
    $mail->Debugoutput = 'html';

    $mail->Host = 'smtp.uc3m.es';
    $mail->Port = 587;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // STARTTLS
    $mail->SMTPAuth = true;
    $mail->Username = $config['smtp_user'];
    $mail->Password = $config['smtp_pass'];

    // Opcionales: timeouts y evitar validación estricta en algunos entornos
    $mail->Timeout = 30; // segundos
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
            'allow_self_signed' => false
        ]
    ];

    // Para envíos en lote: mantener viva la conexión
    $mail->SMTPKeepAlive = true;
    $mail->setFrom($config['smtp_user'], 'AISC Madrid');
    $mail->addReplyTo('aisc.asoc@uc3m.es', 'AISC Madrid');
    $mail->isHTML(true);
    $mail->Subject = 'Prueba SMTP UC3M — PHPMailer';

    $body = '<p>Prueba de envío SMTP desde PHPMailer. Ignorar.</p>';

    $mail->Body = $body;

    foreach ($testRecipients as $rcpt) {
        $mail->clearAddresses();
        $mail->addAddress($rcpt);
        echo "<h3>Enviando a: $rcpt</h3>";
        if (!$mail->send()) {
            echo "<p style='color:red'>ERROR: " . $mail->ErrorInfo . "</p>";
        } else {
            echo "<p style='color:green'>Enviado OK a $rcpt</p>";
        }
        // pequeña pausa para no saturar el servidor
        sleep(1);
    }

    // Cerrar conexión SMTP al final
    $mail->smtpClose();

} catch (Exception $e) {
    echo "Excepción PHPMailer: {$e->getMessage()}";
}
