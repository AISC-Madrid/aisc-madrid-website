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

try {
    event_planner_update_plan_details($conn, $planId, [
        'title' => $_POST['title'] ?? '',
        'description' => trim($_POST['description'] ?? ''),
        'speaker_name' => $_POST['speaker_name'] ?? '',
        'speaker_company' => trim($_POST['speaker_company'] ?? ''),
        'speaker_contact' => trim($_POST['speaker_contact'] ?? ''),
        'location' => trim($_POST['location'] ?? ''),
        'drive_folder_url' => trim($_POST['drive_folder_url'] ?? ''),
    ], (int) $currentMember['id']);
    ep_flash_set('success', 'Datos del evento actualizados.');
} catch (EventPlannerValidationException $e) {
    ep_flash_set('danger', $e->getMessage());
}

ep_redirect_to_plan($planId);
