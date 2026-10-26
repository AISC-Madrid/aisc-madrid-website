<?php
// Los 10 tipos de correo del planificador (ver documento, sección 5). Todos pasan por
// email_outbox vía ep_queue_email(); el envío real lo hace el cron.

require_once __DIR__ . '/mailer.php';
require_once __DIR__ . '/clock.php';

function ep_format_date(string $date): string
{
    return (new DateTimeImmutable($date))->format('d/m/Y');
}

function ep_format_event_datetime(string $startsAtUtc, int $durationMinutes): string
{
    $start = new DateTimeImmutable($startsAtUtc, new DateTimeZone('UTC'));
    $start = $start->setTimezone(event_planner_timezone());
    $end = $start->modify('+' . $durationMinutes . ' minutes');
    return $start->format('d/m/Y H:i') . ' - ' . $end->format('H:i');
}

// ---------------------------------------------------------------------------
// 1. Plan creado: resumen a cada asignado con SUS tareas
// ---------------------------------------------------------------------------
function ep_notify_plan_created(mysqli $conn, array $config, int $planId): void
{
    $plan = ep_fetch_plan($conn, $planId);
    if (!$plan) {
        return;
    }

    $stmt = $conn->prepare(
        "SELECT et.assignee_id, m.full_name, m.mail, et.title, et.due_date
         FROM event_tasks et JOIN members m ON m.id = et.assignee_id
         WHERE et.event_plan_id = ? ORDER BY et.assignee_id, et.due_date"
    );
    $stmt->bind_param('i', $planId);
    $stmt->execute();
    $tasks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $byMember = [];
    foreach ($tasks as $t) {
        $byMember[$t['assignee_id']]['member'] = $t;
        $byMember[$t['assignee_id']]['tasks'][] = $t;
    }

    foreach ($byMember as $memberId => $group) {
        $member = $group['member'];
        $rows = '';
        foreach ($group['tasks'] as $t) {
            $rows .= '<li>' . ep_html_escape($t['title']) . ' — <strong>' . ep_format_date($t['due_date']) . '</strong></li>';
        }
        $bodyHtml = '<p>Hola ' . ep_html_escape(explode(' ', $member['full_name'])[0]) . ', se ha creado el evento '
            . '<strong>' . ep_html_escape($plan['title']) . '</strong> con ' . ep_html_escape($plan['speaker_name']) . '.</p>'
            . '<p>Estas son tus tareas asignadas:</p><ul>' . $rows . '</ul>';
        $bodyText = "Se ha creado el evento {$plan['title']} con {$plan['speaker_name']}. Tus tareas:\n"
            . implode("\n", array_map(fn($t) => '- ' . $t['title'] . ' (' . ep_format_date($t['due_date']) . ')', $group['tasks']));

        $rendered = ep_render_email('Nuevo evento: ' . $plan['title'], $bodyHtml, $bodyText, ep_plan_url($config, $planId), 'Ver el plan');
        ep_queue_email(
            $conn, (int) $memberId, $member['mail'],
            'Nuevo evento asignado: ' . $plan['title'],
            $rendered['html'], $rendered['text'],
            'plan_created', "plan_created:{$planId}:{$memberId}",
            event_planner_now()
        );
    }
}

// ---------------------------------------------------------------------------
// 2. Tarea activada (+ aviso de dependencias pendientes si aplica, regla B)
// ---------------------------------------------------------------------------
function ep_notify_task_activated(mysqli $conn, array $config, int $planId, array $task, array $completedDependencyNotes, array $pendingDependencyTitles): void
{
    $stmt = $conn->prepare("SELECT full_name, mail FROM members WHERE id = ?");
    $stmt->bind_param('i', $task['assignee_id']);
    $stmt->execute();
    $assignee = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$assignee) {
        return;
    }

    $bodyHtml = '<p>Hola ' . ep_html_escape(explode(' ', $assignee['full_name'])[0]) . ', se te ha activado la tarea:</p>'
        . '<h3 style="color:#EB178E;">' . ep_html_escape($task['title']) . '</h3>';
    if (!empty($task['description'])) {
        $bodyHtml .= '<p>' . nl2br(ep_html_escape($task['description'])) . '</p>';
    }
    $bodyHtml .= '<p><strong>Fecha límite:</strong> ' . ep_format_date($task['due_date']) . '</p>';

    $bodyText = "Se te ha activado la tarea: {$task['title']}\nFecha límite: " . ep_format_date($task['due_date']);

    if (!empty($completedDependencyNotes)) {
        $notesHtml = '';
        foreach ($completedDependencyNotes as $note) {
            $notesHtml .= '<li>' . ep_html_escape($note) . '</li>';
        }
        $bodyHtml .= '<p><strong>Notas de las tareas previas:</strong></p><ul>' . $notesHtml . '</ul>';
        $bodyText .= "\nNotas de las tareas previas:\n" . implode("\n", array_map(fn($n) => '- ' . $n, $completedDependencyNotes));
    }

    if (!empty($pendingDependencyTitles)) {
        $pendingHtml = implode(', ', array_map('ep_html_escape', $pendingDependencyTitles));
        $bodyHtml .= '<p style="background-color:#fff3cd; padding:10px; border-radius:5px;">⚠️ Esta tarea tiene fecha de inicio fija, así que se activa aunque aún falten dependencias: <strong>' . $pendingHtml . '</strong>.</p>';
        $bodyText .= "\n⚠️ Aviso: aún faltan dependencias pendientes: " . implode(', ', $pendingDependencyTitles);
    }

    $rendered = ep_render_email('Tarea activada', $bodyHtml, $bodyText, ep_task_url($config, $planId, $task['id']), 'Ver la tarea');
    ep_queue_email(
        $conn, (int) $task['assignee_id'], $assignee['mail'],
        'Tarea activada: ' . $task['title'],
        $rendered['html'], $rendered['text'],
        'task_activated', "task_activated:{$task['id']}",
        event_planner_now()
    );
}

// ---------------------------------------------------------------------------
// 3. Aviso a los asignados de las dependencias pendientes: "X espera por tu tarea Y"
// ---------------------------------------------------------------------------
function ep_notify_dependency_waiting(mysqli $conn, array $config, int $planId, array $waitingTask, array $pendingDependencyTask): void
{
    $stmt = $conn->prepare("SELECT full_name, mail FROM members WHERE id = ?");
    $stmt->bind_param('i', $pendingDependencyTask['assignee_id']);
    $stmt->execute();
    $assignee = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$assignee) {
        return;
    }

    $bodyHtml = '<p>Hola ' . ep_html_escape(explode(' ', $assignee['full_name'])[0]) . ', la tarea <strong>'
        . ep_html_escape($waitingTask['title']) . '</strong> ya se ha activado y está esperando a que completes tu tarea:</p>'
        . '<h3 style="color:#EB178E;">' . ep_html_escape($pendingDependencyTask['title']) . '</h3>';
    $bodyText = "La tarea \"{$waitingTask['title']}\" está esperando a que completes tu tarea \"{$pendingDependencyTask['title']}\".";

    $rendered = ep_render_email('Tu tarea está bloqueando otra', $bodyHtml, $bodyText, ep_task_url($config, $planId, $pendingDependencyTask['id']), 'Ver mi tarea');
    ep_queue_email(
        $conn, (int) $pendingDependencyTask['assignee_id'], $assignee['mail'],
        'Te están esperando: ' . $pendingDependencyTask['title'],
        $rendered['html'], $rendered['text'],
        'dependency_waiting', "dependency_waiting:{$waitingTask['id']}:{$pendingDependencyTask['id']}",
        event_planner_now()
    );
}

// ---------------------------------------------------------------------------
// 4 / 5. Recordatorio (día antes) y vencida (día después) — a asignado + creador del plan
// ---------------------------------------------------------------------------
function ep_notify_task_due_event(mysqli $conn, array $config, int $planId, array $task, string $kind): void
{
    $isReminder = $kind === 'reminder';
    $stmt = $conn->prepare(
        "SELECT a.full_name AS a_name, a.mail AS a_mail, c.id AS creator_id, c.full_name AS c_name, c.mail AS c_mail
         FROM event_tasks et
         JOIN members a ON a.id = et.assignee_id
         JOIN event_plans p ON p.id = et.event_plan_id
         JOIN members c ON c.id = p.created_by
         WHERE et.id = ?"
    );
    $stmt->bind_param('i', $task['id']);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$row) {
        return;
    }

    $heading = $isReminder ? 'Recordatorio: tarea vence mañana' : 'Tarea vencida';
    $intro = $isReminder
        ? 'La siguiente tarea vence <strong>mañana</strong>:'
        : 'La siguiente tarea venció <strong>ayer</strong> y sigue sin completarse:';

    $bodyHtml = '<p>' . $intro . '</p><h3 style="color:#EB178E;">' . ep_html_escape($task['title']) . '</h3>'
        . '<p><strong>Asignado a:</strong> ' . ep_html_escape($row['a_name']) . '<br>'
        . '<strong>Fecha límite:</strong> ' . ep_format_date($task['due_date']) . '</p>';
    $bodyText = $heading . ": {$task['title']} (asignado a {$row['a_name']}, fecha límite " . ep_format_date($task['due_date']) . ')';

    $rendered = ep_render_email($heading, $bodyHtml, $bodyText, ep_task_url($config, $planId, $task['id']), 'Ver la tarea');
    $recipients = [
        (int) $task['assignee_id'] => ['mail' => $row['a_mail']],
        (int) $row['creator_id'] => ['mail' => $row['c_mail']],
    ];
    foreach ($recipients as $memberId => $info) {
        ep_queue_email(
            $conn, $memberId, $info['mail'],
            ($isReminder ? 'Recordatorio: ' : 'Vencida: ') . $task['title'],
            $rendered['html'], $rendered['text'],
            $kind, "{$kind}:{$task['id']}:{$task['due_date']}:{$memberId}",
            event_planner_now()
        );
    }
}

// ---------------------------------------------------------------------------
// 6. Resumen diario de retrasos para la junta
// ---------------------------------------------------------------------------
function ep_notify_board_overdue_digest(mysqli $conn, array $config, array $overdueTasks, DateTimeImmutable $today): void
{
    if (empty($overdueTasks)) {
        return;
    }

    $stmt = $conn->prepare("SELECT id, full_name, mail FROM members WHERE active = 'yes' AND (role = 'admin' OR board = 'yes')");
    $stmt->execute();
    $boardMembers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (empty($boardMembers)) {
        return;
    }

    $rows = '';
    foreach ($overdueTasks as $t) {
        $rows .= '<li>[' . ep_html_escape($t['plan_title']) . '] ' . ep_html_escape($t['title'])
            . ' — ' . ep_html_escape($t['assignee_name']) . ' (vencía ' . ep_format_date($t['due_date']) . ')</li>';
    }
    $bodyHtml = '<p>Tareas vencidas en todos los eventos a día de hoy:</p><ul>' . $rows . '</ul>';
    $bodyText = "Tareas vencidas:\n" . implode("\n", array_map(
        fn($t) => "- [{$t['plan_title']}] {$t['title']} — {$t['assignee_name']} (vencía " . ep_format_date($t['due_date']) . ')',
        $overdueTasks
    ));
    $dateKey = $today->format('Y-m-d');

    $rendered = ep_render_email('Resumen de tareas vencidas', $bodyHtml, $bodyText, ep_base_url($config) . '/dashboard/event_planner/index.php', 'Ver eventos');
    foreach ($boardMembers as $member) {
        ep_queue_email(
            $conn, (int) $member['id'], $member['mail'],
            'Resumen de tareas vencidas (' . $dateKey . ')',
            $rendered['html'], $rendered['text'],
            'board_overdue_digest', "board_overdue_digest:{$dateKey}:{$member['id']}",
            event_planner_now()
        );
    }
}

// ---------------------------------------------------------------------------
// 7. Cambio de fecha/hora del evento
// ---------------------------------------------------------------------------
function ep_notify_event_datetime_changed(mysqli $conn, array $config, int $planId, array $changesByMember, string $newStartsAt): void
{
    foreach ($changesByMember as $memberId => $data) {
        $rows = '';
        foreach ($data['changes'] as $c) {
            $rows .= '<li>' . ep_html_escape($c['title']) . ': ' . ep_format_date($c['old_due']) . ' → <strong>' . ep_format_date($c['new_due']) . '</strong></li>';
        }
        $bodyHtml = '<p>Ha cambiado la fecha del evento. Estas son tus tareas afectadas:</p><ul>' . $rows . '</ul>';
        $bodyText = "Ha cambiado la fecha del evento. Tareas afectadas:\n" . implode("\n", array_map(
            fn($c) => "- {$c['title']}: " . ep_format_date($c['old_due']) . ' -> ' . ep_format_date($c['new_due']),
            $data['changes']
        ));

        $rendered = ep_render_email('Cambio de fecha del evento', $bodyHtml, $bodyText, ep_plan_url($config, $planId), 'Ver el plan');
        ep_queue_email(
            $conn, (int) $memberId, $data['mail'],
            'Cambio de fecha del evento',
            $rendered['html'], $rendered['text'],
            'event_datetime_changed', "event_datetime_changed:{$planId}:{$memberId}:{$newStartsAt}",
            event_planner_now()
        );
    }
}

// ---------------------------------------------------------------------------
// 8. Reasignación
// ---------------------------------------------------------------------------
function ep_notify_task_reassigned(mysqli $conn, array $config, int $planId, array $task, array $fromMember, array $toMember): void
{
    $at = event_planner_now()->format('YmdHis');

    $rendered = ep_render_email(
        'Nueva tarea asignada',
        '<p>Se te ha asignado la tarea <strong>' . ep_html_escape($task['title']) . '</strong> (antes de ' . ep_html_escape($fromMember['full_name']) . ').</p>',
        "Se te ha asignado la tarea \"{$task['title']}\" (antes de {$fromMember['full_name']}).",
        ep_task_url($config, $planId, $task['id']), 'Ver la tarea'
    );
    ep_queue_email(
        $conn, (int) $toMember['id'], $toMember['mail'],
        'Nueva tarea asignada: ' . $task['title'],
        $rendered['html'], $rendered['text'],
        'task_reassigned', "task_reassigned:to:{$task['id']}:{$toMember['id']}:{$at}",
        event_planner_now()
    );

    $renderedFrom = ep_render_email(
        'Tarea reasignada',
        '<p>La tarea <strong>' . ep_html_escape($task['title']) . '</strong> ya no está asignada a ti; ahora la lleva ' . ep_html_escape($toMember['full_name']) . '.</p>',
        "La tarea \"{$task['title']}\" ya no está asignada a ti; ahora la lleva {$toMember['full_name']}.",
        ep_plan_url($config, $planId), 'Ver el plan'
    );
    ep_queue_email(
        $conn, (int) $fromMember['id'], $fromMember['mail'],
        'Tarea reasignada: ' . $task['title'],
        $renderedFrom['html'], $renderedFrom['text'],
        'task_reassigned', "task_reassigned:from:{$task['id']}:{$fromMember['id']}:{$at}",
        event_planner_now()
    );
}

// ---------------------------------------------------------------------------
// 9. Cancelación del evento
// ---------------------------------------------------------------------------
function ep_notify_plan_cancelled(mysqli $conn, array $config, int $planId, array $plan): void
{
    $stmt = $conn->prepare(
        "SELECT DISTINCT m.id, m.full_name, m.mail FROM event_tasks et JOIN members m ON m.id = et.assignee_id WHERE et.event_plan_id = ?"
    );
    $stmt->bind_param('i', $planId);
    $stmt->execute();
    $assignees = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $bodyHtml = '<p>El evento <strong>' . ep_html_escape($plan['title']) . '</strong> con ' . ep_html_escape($plan['speaker_name']) . ' se ha cancelado. Tus tareas asociadas ya no son necesarias.</p>';
    $bodyText = "El evento \"{$plan['title']}\" con {$plan['speaker_name']} se ha cancelado.";
    $rendered = ep_render_email('Evento cancelado', $bodyHtml, $bodyText, ep_plan_url($config, $planId), 'Ver el plan');

    foreach ($assignees as $member) {
        ep_queue_email(
            $conn, (int) $member['id'], $member['mail'],
            'Evento cancelado: ' . $plan['title'],
            $rendered['html'], $rendered['text'],
            'plan_cancelled', "plan_cancelled:{$planId}:{$member['id']}",
            event_planner_now()
        );
    }
}

// ---------------------------------------------------------------------------
// 10. Draft sin confirmar a ≤ 14 días
// ---------------------------------------------------------------------------
function ep_notify_draft_unconfirmed(mysqli $conn, array $config, int $planId, array $plan): void
{
    $stmt = $conn->prepare("SELECT full_name, mail FROM members WHERE id = ?");
    $stmt->bind_param('i', $plan['created_by']);
    $stmt->execute();
    $creator = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$creator) {
        return;
    }

    $bodyHtml = '<p>El evento <strong>' . ep_html_escape($plan['title']) . '</strong> con ' . ep_html_escape($plan['speaker_name'])
        . ' sigue en borrador y faltan 14 días o menos para la fecha prevista. Revisa si hace falta cerrar fecha y hora con el ponente.</p>';
    $bodyText = "El evento \"{$plan['title']}\" sigue en borrador y faltan 14 días o menos.";
    $rendered = ep_render_email('Draft sin confirmar', $bodyHtml, $bodyText, ep_plan_url($config, $planId), 'Ver el plan');

    ep_queue_email(
        $conn, (int) $plan['created_by'], $creator['mail'],
        'Draft sin confirmar: ' . $plan['title'],
        $rendered['html'], $rendered['text'],
        'draft_unconfirmed', "draft_unconfirmed:{$planId}",
        event_planner_now()
    );
}

function ep_fetch_plan(mysqli $conn, int $planId): ?array
{
    $stmt = $conn->prepare("SELECT * FROM event_plans WHERE id = ?");
    $stmt->bind_param('i', $planId);
    $stmt->execute();
    $plan = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $plan ?: null;
}
