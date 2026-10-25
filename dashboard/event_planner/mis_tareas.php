<?php
require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/_partials/helpers.php';

$flash = ep_flash_take();
$memberId = (int) $currentMember['id'];

$stmt = $conn->prepare(
    "SELECT et.*, p.id AS plan_id, p.title AS plan_title
     FROM event_tasks et JOIN event_plans p ON p.id = et.event_plan_id
     WHERE et.assignee_id = ? AND et.status = 'active' AND p.status NOT IN ('closed','cancelled')
     ORDER BY et.due_date ASC"
);
$stmt->bind_param('i', $memberId);
$stmt->execute();
$activeTasks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$today = event_planner_today();
$groups = ['overdue' => [], 'this_week' => [], 'later' => []];
foreach ($activeTasks as $t) {
    $due = new DateTimeImmutable($t['due_date'], event_planner_timezone());
    $diff = (int) $today->diff($due)->format('%r%a');
    if ($diff < 0) {
        $groups['overdue'][] = $t;
    } elseif ($diff <= 7) {
        $groups['this_week'][] = $t;
    } else {
        $groups['later'][] = $t;
    }
}

$stmt = $conn->prepare(
    "SELECT et.*, p.id AS plan_id, p.title AS plan_title
     FROM event_tasks et JOIN event_plans p ON p.id = et.event_plan_id
     WHERE et.assignee_id = ? AND et.status = 'blocked' AND p.status NOT IN ('closed','cancelled')
     ORDER BY et.due_date ASC"
);
$stmt->bind_param('i', $memberId);
$stmt->execute();
$blockedTasks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

foreach ($blockedTasks as &$bt) {
    $stmt = $conn->prepare(
        "SELECT dt.title, dt.status FROM event_task_dependencies etd
         JOIN event_tasks dt ON dt.id = etd.depends_on_task_id
         WHERE etd.task_id = ? AND dt.status NOT IN ('done','skipped')"
    );
    $stmt->bind_param('i', $bt['id']);
    $stmt->execute();
    $bt['_blocking'] = array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'title');
    $stmt->close();
}
unset($bt);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mis tareas - Planificador - AISC Madrid</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/css/styles3.css">
    <?php include __DIR__ . '/_partials/styles.php'; ?>
</head>

<body class="bg-light">
    <?php include __DIR__ . '/../../assets/nav_dashboard.php'; ?>

    <div class="container my-5 scroll-margin">
        <h2 class="mb-4"><i class="bi bi-list-check me-2"></i>Mis tareas</h2>

        <?php if ($flash): ?>
            <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
        <?php endif; ?>

        <?php
        $sections = ['overdue' => 'Atrasadas', 'this_week' => 'Esta semana', 'later' => 'Más adelante'];
        foreach ($sections as $key => $label):
            if (empty($groups[$key])) continue;
            ?>
            <h5 class="mt-4 <?= $key === 'overdue' ? 'text-danger' : '' ?>"><?= $label ?> (<?= count($groups[$key]) ?>)</h5>
            <?php foreach ($groups[$key] as $t): ?>
                <div class="card mb-2 shadow-sm">
                    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <div class="fw-semibold"><?= htmlspecialchars($t['title']) ?></div>
                            <div class="small text-muted"><?= htmlspecialchars($t['plan_title']) ?> · <span class="<?= ep_due_class($t['due_date'], $t['status']) ?>">Límite: <?= ep_fmt_date($t['due_date']) ?></span></div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="/dashboard/event_planner/plan.php?id=<?= (int) $t['plan_id'] ?>&task=<?= (int) $t['id'] ?>" class="btn btn-sm btn-outline-secondary">Ver</a>
                            <form method="post" action="/dashboard/event_planner/complete_task.php" class="d-flex gap-1">
                                <?= ep_csrf_field() ?>
                                <input type="hidden" name="plan_id" value="<?= (int) $t['plan_id'] ?>">
                                <input type="hidden" name="task_id" value="<?= (int) $t['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-check-lg"></i> Completar</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>

        <?php if (empty($groups['overdue']) && empty($groups['this_week']) && empty($groups['later'])): ?>
            <p class="text-muted">No tienes tareas activas ahora mismo.</p>
        <?php endif; ?>

        <?php if (!empty($blockedTasks)): ?>
            <button class="btn btn-outline-secondary mt-4" type="button" data-bs-toggle="collapse" data-bs-target="#epUpcoming">
                Próximamente (<?= count($blockedTasks) ?>) <i class="bi bi-chevron-down"></i>
            </button>
            <div class="collapse mt-2" id="epUpcoming">
                <?php foreach ($blockedTasks as $t): ?>
                    <div class="card mb-2">
                        <div class="card-body">
                            <div class="fw-semibold"><?= htmlspecialchars($t['title']) ?></div>
                            <div class="small text-muted"><?= htmlspecialchars($t['plan_title']) ?> · Límite: <?= ep_fmt_date($t['due_date']) ?></div>
                            <div class="small">
                                <?php if (!empty($t['_blocking'])): ?>
                                    Bloqueada por: <?= htmlspecialchars(implode(', ', $t['_blocking'])) ?>
                                <?php else: ?>
                                    Esperando a que se cumpla su condición de activación (gate).
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php include __DIR__ . '/../../assets/footer.php'; ?>
</body>

</html>
<?php $conn->close(); ?>
