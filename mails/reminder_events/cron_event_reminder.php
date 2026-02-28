<?php
// Errores activados para debug (quitar en producción)
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
include(__DIR__ . '/../../assets/db.php');
$config = include(__DIR__ . '/../../config.php');

// Definir la fecha objetivo (dentro de 2 días)
$hoy = new DateTime();
$fecha_objetivo = $hoy->modify('+2 days')->format('Y-m-d');

$query = "SELECT u.full_name, r.email, r.event_id, e.title_es, e.title_en, e.start_datetime, e.end_datetime, e.location, e.image_path, e.speaker 
        FROM event_registrations r
        JOIN form_submissions u ON r.id = u.id
        JOIN events e ON r.event_id = e.id
        WHERE DATE(e.start_datetime) = ? AND r.reminder_sent = 0";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $fecha_objetivo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "No hay recordatorios para enviar hoy.";
    exit;
}

// Configuración base de PHPMailer
$mail = new PHPMailer;
$mail->CharSet = 'UTF-8';
$mail->isSMTP();
$mail->Host = 'smtp.hostinger.com';
$mail->Port = 587;
$mail->SMTPAuth = true;
$mail->Username = $config['smtp_user'];
$mail->Password = $config['smtp_pass'];
$mail->setFrom('info@aiscmadrid.com', 'AISC Madrid');
$mail->addReplyTo('aisc.asoc@uc3m.es', 'AISC Madrid');
$mail->isHTML(true);

$meses = ["", "enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];

// 4. Bucle para enviar a cada asistente
while ($row = $result->fetch_assoc()) {
    $nombre = explode(' ', $row['full_name'])[0];
    $email = $row['email'];

    // Datos del evento
    $titulo_es = $row['title_es'];
    $titulo_en = $row['title_en'];
    $lugar = htmlspecialchars($row['location']);
    $ponentes = htmlspecialchars($row['speaker']);
    $inicio = htmlspecialchars($row['start_datetime']);
    $final = htmlspecialchars($row['end_datetime']);

    // Construir fecha legible en español
    $dt = new DateTime($row['start_datetime']);
    $dia = $dt->format('j');
    $mes = $meses[(int)$dt->format('n')];
    $fecha_texto = "$dia de $mes";

    $mail->clearAddresses(); // Limpiar destinatario anterior
    $mail->addAddress($email, $nombre);
    $mail->Subject = "¡Recordatorio! $titulo_es | AISC Madrid";

    // 4. Inyectar el HTML con variables
    $htmlContent = <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Recordatorio - {$titulo_es} | AISC Madrid</title>
        </head>
        <body style="margin:0; padding:0; font-family: Arial, sans-serif; background-color:#f4f4f4;">
            <table align="center" width="600" style="border-collapse: collapse; background-color:#ffffff; margin-top:20px; border-radius:8px; overflow:hidden;">
            <tr>
                <td align="center" style="padding:20px; background-color:#EB178E; color:#ffffff;">
                <h1 style="margin:0; font-size:24px;">¡Tu evento es en 2 días!</h1>
                </td>
            </tr>
            <tr>
                <td style="padding:20px; color:#333333; font-size:16px; line-height:1.5;">
                <p align="center">
                    <strong>¡Hola {$nombre}!</strong>
                    <br><br>
                    Te recordamos que estás registrado/a en el taller <strong>{$titulo_es}</strong>, que tendrá lugar <strong>este próximo {$fecha_texto}</strong>.
                </p>
                </td>
            </tr>
            <tr>
                <td align="center" style="padding:10px 20px; color:#EB178E;">
                <h2 style="margin:0; font-size:22px;"><strong>{$titulo_es}</strong></h2>
                <div style="margin-top:15px; width:80px; height:4px; background-color:#EB178E; border-radius:2px;"></div>
                </td>
            </tr>
            <tr>
                <td style="padding:20px; color:#333333; font-size:16px; line-height:1.8;">
                <p style="margin:8px 0;">📅 <strong>Fecha:</strong> {$inicio} - {$final}</p>
                <p style="margin:8px 0;">📍 <strong>Aula:</strong> {$lugar}</p>
                <p style="margin:8px 0;">👥 <strong>Ponentes:</strong> {$ponentes}</p>
                </td>
            </tr>
            <tr>
                <td style="padding:0 20px 20px; color:#333333; font-size:14px; line-height:1.5;">
                <p align="center" style="color:#666666;">
                    ¡Te esperamos! Si tienes alguna duda, no dudes en contactarnos.
                    <br><br>
                    Equipo AISC Madrid
                </p>
                </td>
            </tr>
            </table>
        </body>
        </html>
    HTML;


    $mail->Body = $htmlContent;
    $mail->AltBody = "Hola {$nombre}, te recordamos que el evento {$titulo_es} es el {$inicio} en {$lugar}.";

    if ($mail->send()) {
        // Marcamos como enviado para no repetir si el script corre dos veces
        $update = $conn->prepare("UPDATE event_registrations SET reminder_sent = 1 WHERE email = ? AND event_id = ?");
        $update->bind_param("si", $email, $row['event_id']);
        $update->execute();
    } else {
        error_log("Error enviando a $email: " . $mail->ErrorInfo);
    }
}

echo "Proceso finalizado. Recordatorios enviados.";
$stmt->close();
$conn->close();

?>
