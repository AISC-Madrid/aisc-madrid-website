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
$dependsOn = array_map('intval', $_POST['depends_on'] ?? []);

try {
    event_planner_add_custom_task($conn, $config, $planId, [
        'title' => trim($_POST['title'] ?? ''),
        'description' => trim($_POST['description'] ?? '') ?: null,
        'team' => $_POST['team'] ?? 'EVENTOS',
        'assignee_id' => (int) ($_POST['assignee_id'] ?? 0),
        'due_offset_days' => $_POST['due_offset_days'] !== '' ? (int) $_POST['due_offset_days'] : null,
        'due_date' => $_POST['due_date'] ?: null,
        'start_offset_days' => $_POST['start_offset_days'] !== '' ? (int) $_POST['start_offset_days'] : null,
        'start_date' => $_POST['start_date'] ?: null,
        'gate' => $_POST['gate'] ?? 'none',
        'depends_on' => $dependsOn,
    ], (int) $currentMember['id']);
    ep_flash_set('success', 'Tarea personalizada añadida.');
} catch (EventPlannerValidationException $e) {
    ep_flash_set('danger', $e->getMessage());
}

ep_redirect_to_plan($planId);
