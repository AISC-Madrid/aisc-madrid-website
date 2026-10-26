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
$publicEventId = (int) ($_POST['public_event_id'] ?? 0);

if ($publicEventId > 0) {
    $stmt = $conn->prepare('SELECT id FROM events WHERE id = ?');
    $stmt->bind_param('i', $publicEventId);
    $stmt->execute();
    $exists = (bool) $stmt->get_result()->fetch_row();
    $stmt->close();

    if ($exists) {
        event_planner_link_public_event($conn, $planId, $publicEventId, (int) $currentMember['id']);
        ep_flash_set('success', 'Evento público enlazado.');
    } else {
        ep_flash_set('danger', 'Ese evento público no existe.');
    }
}

ep_redirect_to_plan($planId);
