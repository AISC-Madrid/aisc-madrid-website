<?php
require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/_partials/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /dashboard/event_planner/index.php');
    exit();
}
ep_require_valid_csrf();

$taskId = (int) ($_POST['task_id'] ?? 0);
$planId = (int) ($_POST['plan_id'] ?? 0);
$notes = trim($_POST['notes'] ?? '') ?: null;

try {
    event_planner_complete_task($conn, $config, $taskId, (int) $currentMember['id'], $notes);
    ep_flash_set('success', 'Tarea completada.');
} catch (EventPlannerValidationException $e) {
    ep_flash_set('danger', $e->getMessage());
}

ep_redirect_to_plan($planId);
