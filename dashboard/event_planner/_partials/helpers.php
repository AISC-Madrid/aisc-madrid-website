<?php
// Helpers de presentación (HTML) para las vistas del planificador. Lógica de dominio pura
// vive en event_planner/lib/; esto es solo renderizado.

function ep_status_label(string $status): string
{
    return match ($status) {
        'blocked' => 'Bloqueada',
        'active' => 'Activa',
        'done' => 'Completada',
        'skipped' => 'No aplica',
        default => $status,
    };
}

function ep_status_icon(string $status): string
{
    return match ($status) {
        'blocked' => 'bi-lock-fill',
        'active' => 'bi-play-circle-fill',
        'done' => 'bi-check-circle-fill',
        'skipped' => 'bi-slash-circle',
        default => 'bi-circle',
    };
}

function ep_status_badge(string $status): string
{
    return '<span class="badge ep-status-' . $status . '"><i class="bi ' . ep_status_icon($status) . ' me-1"></i>' . ep_status_label($status) . '</span>';
}

function ep_team_label(string $team): string
{
    return match ($team) {
        'EVENTOS' => 'Eventos',
        'REDES' => 'Redes',
        'WEB' => 'Web',
        default => $team,
    };
}

function ep_team_icon(string $team): string
{
    return match ($team) {
        'EVENTOS' => 'bi-calendar-event-fill',
        'REDES' => 'bi-megaphone-fill',
        'WEB' => 'bi-code-slash',
        default => 'bi-people-fill',
    };
}

function ep_team_badge(string $team, bool $soft = false): string
{
    $class = $soft ? 'ep-team-soft-' . $team : 'ep-team-' . $team;
    return '<span class="badge ' . $class . '"><i class="bi ' . ep_team_icon($team) . ' me-1"></i>' . ep_team_label($team) . '</span>';
}

function ep_plan_status_label(string $status): string
{
    return match ($status) {
        'draft' => 'Borrador',
        'confirmed' => 'Confirmado',
        'held' => 'Celebrado',
        'closed' => 'Cerrado',
        'cancelled' => 'Cancelado',
        default => $status,
    };
}

function ep_plan_status_icon(string $status): string
{
    return match ($status) {
        'draft' => 'bi-pencil-square',
        'confirmed' => 'bi-patch-check-fill',
        'held' => 'bi-mic-fill',
        'closed' => 'bi-check-circle-fill',
        'cancelled' => 'bi-x-circle-fill',
        default => 'bi-circle',
    };
}

function ep_plan_status_badge(string $status): string
{
    return '<span class="badge ep-plan-' . $status . '"><i class="bi ' . ep_plan_status_icon($status) . ' me-1"></i>' . ep_plan_status_label($status) . '</span>';
}

// Pill con icono de reloj para la fecha límite, en rojo/ámbar si procede (ver ep_due_class).
function ep_due_pill(string $dueDate, string $status): string
{
    $class = ep_due_class($dueDate, $status);
    $icon = $class === 'ep-due-overdue' ? 'bi-exclamation-circle-fill' : 'bi-calendar3';
    return '<span class="ep-due-pill ' . $class . '"><i class="bi ' . $icon . '"></i>' . ep_fmt_date($dueDate) . '</span>';
}

function ep_activity_label(string $action): string
{
    return match ($action) {
        'plan_created' => 'Evento creado',
        'task_activated' => 'Tarea activada',
        'task_completed' => 'Tarea completada',
        'task_skipped' => 'Tarea marcada como No aplica',
        'task_reassigned' => 'Tarea reasignada',
        'custom_task_added' => 'Tarea personalizada añadida',
        'event_datetime_changed' => 'Fecha del evento cambiada',
        'plan_status_changed' => 'Estado del evento cambiado',
        'plan_held' => 'El evento se ha celebrado',
        'plan_closed' => 'Evento cerrado',
        'public_event_linked' => 'Enlazado con el evento público',
        'plan_details_updated' => 'Datos del evento actualizados',
        default => $action,
    };
}

function ep_activity_icon(string $action): string
{
    return match (true) {
        $action === 'plan_created' => 'bi-flag-fill',
        str_starts_with($action, 'task_activated') => 'bi-play-fill',
        str_starts_with($action, 'task_completed') => 'bi-check-lg',
        str_starts_with($action, 'task_skipped') => 'bi-slash-circle',
        str_starts_with($action, 'task_reassigned') => 'bi-arrow-left-right',
        $action === 'custom_task_added' => 'bi-plus-lg',
        $action === 'event_datetime_changed' => 'bi-calendar3',
        str_starts_with($action, 'plan_status') || str_starts_with($action, 'plan_held') || str_starts_with($action, 'plan_closed') => 'bi-flag',
        $action === 'public_event_linked' => 'bi-link-45deg',
        default => 'bi-dot',
    };
}

function ep_empty_state(string $icon, string $title, string $subtitle = ''): string
{
    $html = '<div class="ep-empty-state"><i class="bi ' . $icon . '"></i><h5 class="mb-1">' . htmlspecialchars($title) . '</h5>';
    if ($subtitle !== '') {
        $html .= '<p class="mb-0 small">' . htmlspecialchars($subtitle) . '</p>';
    }
    return $html . '</div>';
}

function ep_fmt_date(?string $date): string
{
    if (!$date) {
        return '—';
    }
    return (new DateTimeImmutable($date))->format('d/m/Y');
}

function ep_fmt_datetime_madrid(string $startsAtUtc): string
{
    $dt = new DateTimeImmutable($startsAtUtc, new DateTimeZone('UTC'));
    return $dt->setTimezone(event_planner_timezone())->format('d/m/Y H:i');
}

// Clase CSS para resaltar la fecha límite: rojo si vencida, ámbar si quedan <=2 días.
function ep_due_class(string $dueDate, string $status): string
{
    if (in_array($status, ['done', 'skipped'], true)) {
        return '';
    }
    $today = event_planner_today();
    $due = new DateTimeImmutable($dueDate, event_planner_timezone());
    $diffDays = (int) $today->diff($due)->format('%r%a');
    if ($diffDays < 0) {
        return 'ep-due-overdue';
    }
    if ($diffDays <= 2) {
        return 'ep-due-soon';
    }
    return '';
}

function ep_csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generate_csrf_token()) . '">';
}

function ep_require_valid_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!validate_csrf_token($token)) {
        http_response_code(400);
        exit('Token CSRF inválido. Recarga la página e inténtalo de nuevo.');
    }
}

function ep_flash_set(string $type, string $message): void
{
    $_SESSION['ep_flash'] = ['type' => $type, 'message' => $message];
}

function ep_flash_take(): ?array
{
    if (empty($_SESSION['ep_flash'])) {
        return null;
    }
    $flash = $_SESSION['ep_flash'];
    unset($_SESSION['ep_flash']);
    return $flash;
}

function ep_redirect_to_plan(int $planId, ?int $taskId = null): void
{
    $url = '/dashboard/event_planner/plan.php?id=' . $planId;
    if ($taskId) {
        $url .= '&task=' . $taskId;
    }
    header('Location: ' . $url);
    exit();
}
