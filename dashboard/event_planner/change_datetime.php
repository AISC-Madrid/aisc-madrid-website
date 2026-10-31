<?php
require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/_partials/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /dashboard/event_planner/index.php');
    exit();
}
ep_require_valid_csrf();
ep_require_board_or_admin($currentMember);

$planId = (int) ($_POST['plan_id'] ?? 0);
$date = trim($_POST['event_date'] ?? '');
$time = trim($_POST['event_time'] ?? '');
$duration = (int) ($_POST['duration_minutes'] ?? 0);

try {
    if ($date === '' || $time === '' || $duration <= 0) {
        throw new EventPlannerValidationException('Faltan datos de fecha, hora o duración.');
    }
    $startsAtUtc = event_planner_local_to_utc($date . ' ' . $time);
    event_planner_change_event_datetime($conn, $config, $planId, $startsAtUtc, $duration, (int) $currentMember['id']);
    ep_flash_set('success', 'Fecha del evento actualizada. Se han recalculado las tareas abiertas.');
} catch (EventPlannerValidationException $e) {
    ep_flash_set('danger', $e->getMessage());
}

ep_redirect_to_plan($planId);
