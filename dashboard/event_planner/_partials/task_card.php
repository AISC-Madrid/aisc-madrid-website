<?php
// Tarjeta de tarea para el tablero (Kanban por estado o por equipo). Espera $t (fila de
// event_tasks + assignee_name/completed_by_name/skipped_by_name) y $depsByTask en el
// scope de quien la incluye (plan.php).
$deps = $depsByTask[(int) $t['id']] ?? [];
$dueClass = ep_due_class($t['due_date'], $t['status']);
?>
<div class="card mb-2 ep-task-card ep-team-border-<?= $t['team'] ?>"
     data-id="<?= (int) $t['id'] ?>"
     data-title="<?= htmlspecialchars($t['title'], ENT_QUOTES) ?>"
     data-description="<?= htmlspecialchars($t['description'] ?? '', ENT_QUOTES) ?>"
     data-team="<?= $t['team'] ?>"
     data-status="<?= $t['status'] ?>"
     data-due="<?= ep_fmt_date($t['due_date']) ?>"
     data-start="<?= $t['start_date'] ? ep_fmt_date($t['start_date']) : '' ?>"
     data-assignee-id="<?= (int) $t['assignee_id'] ?>"
     data-assignee-name="<?= htmlspecialchars($t['assignee_name'], ENT_QUOTES) ?>"
     data-notes="<?= htmlspecialchars($t['completion_notes'] ?? '', ENT_QUOTES) ?>"
     data-completed-by="<?= htmlspecialchars($t['completed_by_name'] ?? '', ENT_QUOTES) ?>"
     data-completed-at="<?= $t['completed_at'] ? ep_fmt_date($t['completed_at']) : '' ?>"
     data-team-badge="<?= htmlspecialchars(ep_team_badge($t['team']), ENT_QUOTES) ?>"
     data-status-badge="<?= htmlspecialchars(ep_status_badge($t['status']), ENT_QUOTES) ?>"
     data-deps='<?= htmlspecialchars(json_encode($deps, JSON_UNESCAPED_UNICODE), ENT_QUOTES) ?>'>
    <div class="card-body py-2 px-3">
        <div class="d-flex justify-content-between align-items-start">
            <span class="small fw-semibold"><?= htmlspecialchars($t['title']) ?></span>
            <?= ep_status_badge($t['status']) ?>
        </div>
        <div class="small text-muted"><?= htmlspecialchars($t['assignee_name']) ?></div>
        <div class="small <?= $dueClass ?>"><?= ep_fmt_date($t['due_date']) ?></div>
    </div>
</div>
