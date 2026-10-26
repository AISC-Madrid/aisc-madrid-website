<?php
/**
 * Cron del planificador de eventos. Pensado para ejecutarse cada 15 minutos (Scheduled
 * Task de Coolify; ver event_planner/README.md). No es accesible por web.
 *
 * Pasos (documento, sección 6):
 *   1. confirmed -> held cuando ya ha pasado starts_at + duration.
 *   2. held -> closed cuando no quedan tareas pendientes.
 *   3. recomputePlan en todos los planes abiertos.
 *   4. Paso diario (>= 09:00 Madrid, una vez al día): recordatorios, vencidas, resumen
 *      de la junta y avisos de draft.
 *   5. Enviar los correos pendientes de email_outbox.
 *
 * Para probarlo fuera de producción:
 *   EVENT_PLANNER_ALLOW_CLOCK_OVERRIDE=1 EVENT_PLANNER_NOW="2026-11-10T09:05" php scripts/event_planner_cron.php
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("CLI only.\n");
}

require_once __DIR__ . '/../event_planner/lib/clock.php';
require_once __DIR__ . '/../event_planner/lib/recompute.php';
require_once __DIR__ . '/../event_planner/lib/notifications.php';
$config = include __DIR__ . '/../config.php';
require_once __DIR__ . '/../assets/db.php';

$now = event_planner_now();
echo '[event_planner_cron] now=' . $now->format('Y-m-d H:i:s') . " Europe/Madrid\n";

// ---------------------------------------------------------------------------
// Paso 1 y 2: transiciones de estado dependientes del tiempo
// ---------------------------------------------------------------------------
$stmt = $conn->prepare("SELECT id FROM event_plans WHERE status = 'confirmed'");
$stmt->execute();
$confirmedIds = array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'id');
$stmt->close();
foreach ($confirmedIds as $planId) {
    if (event_planner_maybe_advance_to_held($conn, (int) $planId)) {
        echo "  plan {$planId}: confirmed -> held\n";
    }
}

$stmt = $conn->prepare("SELECT id FROM event_plans WHERE status = 'held'");
$stmt->execute();
$heldIds = array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'id');
$stmt->close();
foreach ($heldIds as $planId) {
    if (event_planner_maybe_close_plan($conn, (int) $planId)) {
        echo "  plan {$planId}: held -> closed\n";
    }
}

// ---------------------------------------------------------------------------
// Paso 3: recomputePlan en todos los planes abiertos
// ---------------------------------------------------------------------------
$stmt = $conn->prepare("SELECT id FROM event_plans WHERE status NOT IN ('closed','cancelled')");
$stmt->execute();
$openIds = array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'id');
$stmt->close();
foreach ($openIds as $planId) {
    event_planner_recompute_plan($conn, $config, (int) $planId);
}
echo '  recomputePlan en ' . count($openIds) . " planes abiertos\n";

// ---------------------------------------------------------------------------
// Paso 4: paso diario (recordatorios, vencidas, resumen junta, draft sin confirmar)
// ---------------------------------------------------------------------------
if ((int) $now->format('H') >= 9) {
    $runKey = 'daily:' . $now->format('Y-m-d');
    $stmt = $conn->prepare("SELECT 1 FROM cron_runs WHERE run_key = ?");
    $stmt->bind_param('s', $runKey);
    $stmt->execute();
    $alreadyRan = (bool) $stmt->get_result()->fetch_row();
    $stmt->close();

    if (!$alreadyRan) {
        echo "  paso diario ({$runKey})\n";
        $tomorrow = $now->modify('+1 day')->format('Y-m-d');
        $yesterday = $now->modify('-1 day')->format('Y-m-d');
        $today = $now->format('Y-m-d');

        // 4. Recordatorio: tareas activas que vencen mañana
        $stmt = $conn->prepare("SELECT * FROM event_tasks WHERE status = 'active' AND due_date = ?");
        $stmt->bind_param('s', $tomorrow);
        $stmt->execute();
        $reminders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        foreach ($reminders as $task) {
            ep_notify_task_due_event($conn, $config, (int) $task['event_plan_id'], $task, 'reminder');
        }
        echo '    recordatorios encolados: ' . count($reminders) . "\n";

        // 5. Vencida: tareas sin completar cuya fecha límite fue ayer
        $stmt = $conn->prepare("SELECT * FROM event_tasks WHERE status NOT IN ('done','skipped') AND due_date = ?");
        $stmt->bind_param('s', $yesterday);
        $stmt->execute();
        $overdueYesterday = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        foreach ($overdueYesterday as $task) {
            ep_notify_task_due_event($conn, $config, (int) $task['event_plan_id'], $task, 'overdue');
        }
        echo '    vencidas encoladas: ' . count($overdueYesterday) . "\n";

        // 6. Resumen de retrasos para la junta: todas las tareas vencidas de todos los eventos
        $stmt = $conn->prepare(
            "SELECT et.id, et.title, et.due_date, p.title AS plan_title, m.full_name AS assignee_name
             FROM event_tasks et
             JOIN event_plans p ON p.id = et.event_plan_id
             JOIN members m ON m.id = et.assignee_id
             WHERE et.status NOT IN ('done','skipped') AND et.due_date < ? AND p.status NOT IN ('closed','cancelled')
             ORDER BY et.due_date ASC"
        );
        $stmt->bind_param('s', $today);
        $stmt->execute();
        $allOverdue = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        ep_notify_board_overdue_digest($conn, $config, $allOverdue, $now);
        echo '    tareas vencidas (resumen junta): ' . count($allOverdue) . "\n";

        // 10. Draft sin confirmar a <= 14 días del evento
        $stmt = $conn->prepare("SELECT * FROM event_plans WHERE status = 'draft'");
        $stmt->execute();
        $drafts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $draftsWarned = 0;
        foreach ($drafts as $plan) {
            $eventDate = event_planner_event_date($plan['starts_at']);
            $diffDays = (int) $now->setTime(0, 0, 0)->diff($eventDate)->format('%r%a');
            if ($diffDays <= 14) {
                ep_notify_draft_unconfirmed($conn, $config, (int) $plan['id'], $plan);
                $draftsWarned++;
            }
        }
        echo "    drafts avisados: {$draftsWarned}\n";

        $insert = $conn->prepare("INSERT INTO cron_runs (run_key) VALUES (?)");
        $insert->bind_param('s', $runKey);
        $insert->execute();
        $insert->close();
    } else {
        echo "  paso diario ya ejecutado hoy ({$runKey})\n";
    }
}

// ---------------------------------------------------------------------------
// Paso 5: enviar correos pendientes
// ---------------------------------------------------------------------------
require_once __DIR__ . '/../event_planner/lib/mailer.php';
$result = ep_send_pending_emails($conn, $config, $now, 200);
echo "  correos enviados: {$result['sent']}, fallidos: {$result['failed']}\n";

$conn->close();
echo "[event_planner_cron] terminado.\n";
