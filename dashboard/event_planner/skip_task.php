<?php
require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/_partials/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /dashboard/event_planner/index.php');
    exit();
}
ep_require_valid_csrf();
ep_require_board_or_admin($currentMember);

$taskId = (int) ($_POST['task_id'] ?? 0);
$planId = (int) ($_POST['plan_id'] ?? 0);

try {
    event_planner_skip_task($conn, $config, $taskId, (int) $currentMember['id']);
    ep_flash_set('success', 'Tarea marcada como No aplica.');
} catch (EventPlannerValidationException $e) {
    ep_flash_set('danger', $e->getMessage());
}

ep_redirect_to_plan($planId);
