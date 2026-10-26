<?php
// El corazón del sistema: recomputePlan() activa las tareas "blocked" que ya cumplen su
// gate + regla de activación (A o B), de forma idempotente. Se llama al crear el plan, al
// completar/skip una tarea, al cambiar fecha/estado del plan y desde el cron.

require_once __DIR__ . '/clock.php';
require_once __DIR__ . '/activity_log.php';
require_once __DIR__ . '/notifications.php';

function event_planner_recompute_plan(mysqli $conn, array $config, int $planId): void
{
    $conn->begin_transaction();

    $stmt = $conn->prepare("SELECT * FROM event_plans WHERE id = ? FOR UPDATE");
    $stmt->bind_param('i', $planId);
    $stmt->execute();
    $plan = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$plan || in_array($plan['status'], ['cancelled', 'closed'], true)) {
        $conn->commit();
        return;
    }

    $stmt = $conn->prepare("SELECT * FROM event_tasks WHERE event_plan_id = ?");
    $stmt->bind_param('i', $planId);
    $stmt->execute();
    $tasks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $tasksById = [];
    foreach ($tasks as $t) {
        $tasksById[(int) $t['id']] = $t;
    }

    $stmt = $conn->prepare(
        "SELECT etd.task_id, etd.depends_on_task_id FROM event_task_dependencies etd
         JOIN event_tasks et ON et.id = etd.task_id WHERE et.event_plan_id = ?"
    );
    $stmt->bind_param('i', $planId);
    $stmt->execute();
    $depRows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $depsByTask = [];
    foreach ($depRows as $r) {
        $depsByTask[(int) $r['task_id']][] = (int) $r['depends_on_task_id'];
    }

    $now = event_planner_now();
    $nowSql = $now->format('Y-m-d H:i:s');

    foreach ($tasks as $task) {
        if ($task['status'] !== 'blocked') {
            continue;
        }

        $gateOk = event_planner_gate_satisfied($task['gate'], $plan['status']);
        if (!$gateOk) {
            continue;
        }

        $pendingDeps = [];
        $completedDeps = [];
        foreach ($depsByTask[(int) $task['id']] ?? [] as $depId) {
            $depTask = $tasksById[$depId] ?? null;
            if (!$depTask) {
                continue;
            }
            if (in_array($depTask['status'], ['done', 'skipped'], true)) {
                $completedDeps[] = $depTask;
            } else {
                $pendingDeps[] = $depTask;
            }
        }
        $depsSatisfied = empty($pendingDeps);

        $isRuleB = $task['start_date'] !== null;
        $shouldActivate = $isRuleB
            ? ($now >= event_planner_start_threshold($task['start_date']))
            : $depsSatisfied;

        if (!$shouldActivate) {
            continue;
        }

        $update = $conn->prepare("UPDATE event_tasks SET status = 'active', activated_at = ? WHERE id = ? AND status = 'blocked'");
        $update->bind_param('si', $nowSql, $task['id']);
        $update->execute();
        $activated = $update->affected_rows === 1;
        $update->close();

        if (!$activated) {
            continue; // ya activada por otra ejecución concurrente
        }

        event_planner_log($conn, $planId, null, 'task_activated', [
            'task_id' => (int) $task['id'],
            'title' => $task['title'],
            'pending_dependencies' => array_map(fn($d) => $d['title'], $pendingDeps),
        ], (int) $task['id']);

        $completedNotes = array_values(array_filter(array_map(fn($d) => $d['completion_notes'], $completedDeps)));
        $pendingTitles = array_map(fn($d) => $d['title'], $pendingDeps);

        ep_notify_task_activated($conn, $config, $planId, $task, $completedNotes, $pendingTitles);

        foreach ($pendingDeps as $pendingDep) {
            ep_notify_dependency_waiting($conn, $config, $planId, $task, $pendingDep);
        }
    }

    $conn->commit();
}

function event_planner_gate_satisfied(string $gate, string $planStatus): bool
{
    return match ($gate) {
        'none' => true,
        'confirmed' => in_array($planStatus, ['confirmed', 'held', 'closed'], true),
        'held' => in_array($planStatus, ['held', 'closed'], true),
        default => false,
    };
}

// Paso 1 del cron: confirmed -> held cuando ya ha pasado starts_at + duration.
function event_planner_maybe_advance_to_held(mysqli $conn, int $planId): bool
{
    $stmt = $conn->prepare("SELECT * FROM event_plans WHERE id = ? FOR UPDATE");
    $stmt->bind_param('i', $planId);
    $stmt->execute();
    $plan = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$plan || $plan['status'] !== 'confirmed') {
        return false;
    }

    $start = new DateTimeImmutable($plan['starts_at'], new DateTimeZone('UTC'));
    $end = $start->modify('+' . (int) $plan['duration_minutes'] . ' minutes');
    $nowUtc = event_planner_now()->setTimezone(new DateTimeZone('UTC'));

    if ($nowUtc < $end) {
        return false;
    }

    $nowSql = event_planner_now()->format('Y-m-d H:i:s');
    $update = $conn->prepare("UPDATE event_plans SET status = 'held', held_at = ? WHERE id = ? AND status = 'confirmed'");
    $update->bind_param('si', $nowSql, $planId);
    $update->execute();
    $ok = $update->affected_rows === 1;
    $update->close();

    if ($ok) {
        event_planner_log($conn, $planId, null, 'plan_held', []);
    }

    return $ok;
}

// Paso 2 del cron: held -> closed cuando no quedan tareas pendientes.
function event_planner_maybe_close_plan(mysqli $conn, int $planId): bool
{
    $stmt = $conn->prepare("SELECT * FROM event_plans WHERE id = ? FOR UPDATE");
    $stmt->bind_param('i', $planId);
    $stmt->execute();
    $plan = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$plan || $plan['status'] !== 'held') {
        return false;
    }

    $stmt = $conn->prepare("SELECT COUNT(*) AS n FROM event_tasks WHERE event_plan_id = ? AND status NOT IN ('done','skipped')");
    $stmt->bind_param('i', $planId);
    $stmt->execute();
    $pending = (int) $stmt->get_result()->fetch_assoc()['n'];
    $stmt->close();

    if ($pending > 0) {
        return false;
    }

    $nowSql = event_planner_now()->format('Y-m-d H:i:s');
    $update = $conn->prepare("UPDATE event_plans SET status = 'closed', closed_at = ? WHERE id = ? AND status = 'held'");
    $update->bind_param('si', $nowSql, $planId);
    $update->execute();
    $ok = $update->affected_rows === 1;
    $update->close();

    if ($ok) {
        event_planner_log($conn, $planId, null, 'plan_closed', []);
    }

    return $ok;
}
