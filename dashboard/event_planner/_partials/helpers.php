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

function ep_status_badge(string $status): string
{
    return '<span class="badge ep-status-' . $status . '">' . ep_status_label($status) . '</span>';
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

function ep_team_badge(string $team): string
{
    return '<span class="badge ep-team-' . $team . '">' . ep_team_label($team) . '</span>';
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
