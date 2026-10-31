<?php
// Registro de actividad del plan. actorId = null significa "sistema" (cron).

function event_planner_log(mysqli $conn, int $planId, ?int $actorId, string $action, array $payload = [], ?int $taskId = null): void
{
    $payloadJson = $payload ? json_encode($payload, JSON_UNESCAPED_UNICODE) : null;
    $stmt = $conn->prepare(
        "INSERT INTO event_activity_log (event_plan_id, task_id, actor_id, action, payload) VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param('iiiss', $planId, $taskId, $actorId, $action, $payloadJson);
    $stmt->execute();
    $stmt->close();
}
