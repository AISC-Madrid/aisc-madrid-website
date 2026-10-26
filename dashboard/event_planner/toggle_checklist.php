<?php
require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/_partials/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /dashboard/event_planner/index.php');
    exit();
}
ep_require_valid_csrf();

$itemId = (int) ($_POST['item_id'] ?? 0);
$planId = (int) ($_POST['plan_id'] ?? 0);
$done = isset($_POST['done']) && $_POST['done'] === '1';

$stmt = $conn->prepare('SELECT event_plan_id FROM event_checklist_items WHERE id = ?');
$stmt->bind_param('i', $itemId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($row && (int) $row['event_plan_id'] === $planId) {
    $actorId = (int) $currentMember['id'];
    $nowSql = event_planner_now()->format('Y-m-d H:i:s');
    if ($done) {
        $stmt = $conn->prepare('UPDATE event_checklist_items SET done = 1, done_by = ?, done_at = ? WHERE id = ?');
        $stmt->bind_param('isi', $actorId, $nowSql, $itemId);
    } else {
        $stmt = $conn->prepare('UPDATE event_checklist_items SET done = 0, done_by = NULL, done_at = NULL WHERE id = ?');
        $stmt->bind_param('i', $itemId);
    }
    $stmt->execute();
    $stmt->close();
}

ep_redirect_to_plan($planId);
