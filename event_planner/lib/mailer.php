<?php
// Cola de correo (email_outbox) + envío real por SMTP, igual que el resto del repo
// (PHPMailer, smtp.gmail.com:587, credenciales de config.php), pero el envío solo lo
// hace el cron (send_pending_emails), nunca la petición web, para que un fallo de SMTP
// no rompa un completado de tarea.

require_once __DIR__ . '/mail_render.php';

// Encola un correo. Si dedupe_key ya existe, no hace nada (idempotente).
function ep_queue_email(mysqli $conn, ?int $toMemberId, string $toEmail, string $subject, string $bodyHtml, string $bodyText, string $kind, string $dedupeKey, DateTimeImmutable $scheduledFor): void
{
    $scheduledForSql = $scheduledFor->format('Y-m-d H:i:s');
    $stmt = $conn->prepare(
        "INSERT INTO email_outbox (to_member_id, to_email, subject, body_html, body_text, kind, dedupe_key, status, scheduled_for)
         VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?)
         ON DUPLICATE KEY UPDATE id = id"
    );
    $stmt->bind_param('isssssss', $toMemberId, $toEmail, $subject, $bodyHtml, $bodyText, $kind, $dedupeKey, $scheduledForSql);
    $stmt->execute();
    $stmt->close();
}

// Envía los correos pendientes cuyo scheduled_for ya ha llegado. Lo llama el cron.
function ep_send_pending_emails(mysqli $conn, array $config, DateTimeImmutable $now, int $limit = 100): array
{
    $sent = 0;
    $failed = 0;

    $nowSql = $now->format('Y-m-d H:i:s');
    $stmt = $conn->prepare(
        "SELECT * FROM email_outbox WHERE status = 'pending' AND scheduled_for <= ? AND attempts < 3 ORDER BY scheduled_for ASC LIMIT ?"
    );
    $stmt->bind_param('si', $nowSql, $limit);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (empty($rows)) {
        return ['sent' => 0, 'failed' => 0];
    }

    require_once __DIR__ . '/../../vendor/autoload.php';
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    $mail->CharSet = 'UTF-8';
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->Port = 587;
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = 'tls';
    $mail->Username = $config['smtp_user'];
    $mail->Password = $config['smtp_pass'];
    $mail->setFrom($config['smtp_user'], 'AISC Madrid - Planificador');
    $mail->addReplyTo('aisc.asoc@uc3m.es', 'AISC Madrid');
    $mail->isHTML(true);
    $mail->SMTPKeepAlive = true;

    $redirectAllTo = getenv('MAIL_REDIRECT_ALL_TO') ?: null;

    foreach ($rows as $row) {
        $toEmail = $row['to_email'];
        $subject = $row['subject'];

        if ($redirectAllTo) {
            $subject = '[para: ' . $toEmail . '] ' . $subject;
            $toEmail = $redirectAllTo;
        }

        try {
            $mail->clearAddresses();
            $mail->addAddress($toEmail);
            $mail->Subject = $subject;
            $mail->Body = $row['body_html'];
            $mail->AltBody = $row['body_text'];
            $mail->send();

            $update = $conn->prepare("UPDATE email_outbox SET status = 'sent', sent_at = NOW(), attempts = attempts + 1 WHERE id = ?");
            $update->bind_param('i', $row['id']);
            $update->execute();
            $update->close();
            $sent++;
        } catch (Throwable $e) {
            $attempts = (int) $row['attempts'] + 1;
            $status = $attempts >= 3 ? 'failed' : 'pending';
            $update = $conn->prepare("UPDATE email_outbox SET status = ?, attempts = ?, last_error = ? WHERE id = ?");
            $error = substr($e->getMessage(), 0, 1000);
            $update->bind_param('sisi', $status, $attempts, $error, $row['id']);
            $update->execute();
            $update->close();
            error_log('EventPlanner mail error (outbox id ' . $row['id'] . '): ' . $e->getMessage());
            $failed++;
        }
    }

    $mail->smtpClose();

    return ['sent' => $sent, 'failed' => $failed];
}

function ep_base_url(array $config): string
{
    return rtrim($config['base_url'], '/');
}

function ep_plan_url(array $config, int $planId): string
{
    return ep_base_url($config) . '/dashboard/event_planner/plan.php?id=' . $planId;
}

function ep_task_url(array $config, int $planId, int $taskId): string
{
    return ep_base_url($config) . '/dashboard/event_planner/plan.php?id=' . $planId . '&task=' . $taskId;
}

function ep_my_tasks_url(array $config): string
{
    return ep_base_url($config) . '/dashboard/event_planner/mis_tareas.php';
}
