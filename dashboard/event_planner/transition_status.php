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
$newStatus = $_POST['new_status'] ?? '';

try {
    event_planner_transition_plan_status($conn, $config, $planId, $newStatus, (int) $currentMember['id'], 'manual_board_action');
    ep_flash_set('success', 'Estado del evento actualizado.');
} catch (EventPlannerValidationException $e) {
    ep_flash_set('danger', $e->getMessage());
}

ep_redirect_to_plan($planId);
