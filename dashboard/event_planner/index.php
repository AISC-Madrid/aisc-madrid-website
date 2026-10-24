<?php
require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/_partials/helpers.php';

$tab = ($_GET['tab'] ?? 'active') === 'archived' ? 'archived' : 'active';
$statuses = $tab === 'archived' ? ['closed', 'cancelled'] : ['draft', 'confirmed', 'held'];

$placeholders = implode(',', array_fill(0, count($statuses), '?'));
$stmt = $conn->prepare("SELECT * FROM event_plans WHERE status IN ($placeholders) ORDER BY starts_at ASC");
$stmt->bind_param(str_repeat('s', count($statuses)), ...$statuses);
$stmt->execute();
$plans = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

foreach ($plans as &$plan) {
    $planId = (int) $plan['id'];

    $stmt = $conn->prepare("SELECT status, COUNT(*) AS n FROM event_tasks WHERE event_plan_id = ? GROUP BY status");
    $stmt->bind_param('i', $planId);
    $stmt->execute();
    $counts = ['blocked' => 0, 'active' => 0, 'done' => 0, 'skipped' => 0];
    foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $row) {
        $counts[$row['status']] = (int) $row['n'];
    }
    $stmt->close();
    $total = array_sum($counts);
    $denominator = $total - $counts['skipped'];
    $plan['_progress'] = $denominator > 0 ? round(100 * $counts['done'] / $denominator) : 100;
    $plan['_pending'] = $counts['blocked'] + $counts['active'];

    $today = event_planner_today()->format('Y-m-d');
    $stmt = $conn->prepare("SELECT COUNT(*) AS n FROM event_tasks WHERE event_plan_id = ? AND status NOT IN ('done','skipped') AND due_date < ?");
    $stmt->bind_param('is', $planId, $today);
    $stmt->execute();
    $plan['_overdue'] = (int) $stmt->get_result()->fetch_assoc()['n'];
    $stmt->close();

    $stmt = $conn->prepare("SELECT title, due_date FROM event_tasks WHERE event_plan_id = ? AND status NOT IN ('done','skipped') ORDER BY due_date ASC LIMIT 3");
    $stmt->bind_param('i', $planId);
    $stmt->execute();
    $plan['_next_due'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $stmt = $conn->prepare("SELECT m.full_name FROM event_plan_members epm JOIN members m ON m.id = epm.member_id WHERE epm.event_plan_id = ? ORDER BY epm.slot");
    $stmt->bind_param('i', $planId);
    $stmt->execute();
    $plan['_assignees'] = array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'full_name');
    $stmt->close();
}
unset($plan);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Planificador de eventos - AISC Madrid</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <?php include __DIR__ . '/_partials/styles.php'; ?>
</head>

<body class="bg-light">
    <?php include __DIR__ . '/../../assets/nav_dashboard.php'; ?>

    <div class="container my-5 scroll-margin">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h2 class="m-0"><i class="bi bi-kanban me-2"></i>Planificador de eventos</h2>
                <p class="text-muted mb-0">Tareas de Eventos, Redes y Web por evento.</p>
            </div>
            <?php if ($isBoardOrAdmin): ?>
                <a href="/dashboard/event_planner/new.php" class="btn btn-custom">
                    <i class="bi bi-plus-lg me-1"></i> Nuevo evento
                </a>
            <?php endif; ?>
        </div>

        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link <?= $tab === 'active' ? 'active' : '' ?>" href="?tab=active">Activos</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $tab === 'archived' ? 'active' : '' ?>" href="?tab=archived">Archivados</a>
            </li>
        </ul>

        <?php if (empty($plans)): ?>
            <p class="text-muted">No hay eventos <?= $tab === 'archived' ? 'archivados' : 'activos' ?>.</p>
        <?php endif; ?>

        <div class="row g-3">
            <?php foreach ($plans as $plan): ?>
                <div class="col-12 col-md-6 col-lg-4">
                    <a href="/dashboard/event_planner/plan.php?id=<?= (int) $plan['id'] ?>" class="text-decoration-none text-reset">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <h5 class="card-title mb-0"><?= htmlspecialchars($plan['title']) ?></h5>
                                    <span class="badge bg-dark"><?= ep_plan_status_label($plan['status']) ?></span>
                                </div>
                                <p class="text-muted small mb-2"><?= htmlspecialchars($plan['speaker_name']) ?> · <?= ep_fmt_datetime_madrid($plan['starts_at']) ?></p>

                                <div class="progress ep-progress-thin mb-1">
                                    <div class="progress-bar bg-success" style="width: <?= $plan['_progress'] ?>%"></div>
                                </div>
                                <p class="small text-muted mb-2">
                                    <?= $plan['_pending'] ?> pendientes
                                    <?php if ($plan['_overdue'] > 0): ?>
                                        · <span class="text-danger fw-bold"><?= $plan['_overdue'] ?> atrasadas</span>
                                    <?php endif; ?>
                                </p>

                                <?php if (!empty($plan['_next_due'])): ?>
                                    <ul class="small mb-2 ps-3">
                                        <?php foreach ($plan['_next_due'] as $t): ?>
                                            <li><?= htmlspecialchars($t['title']) ?> — <?= ep_fmt_date($t['due_date']) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>

                                <p class="small text-muted mb-0"><i class="bi bi-people me-1"></i><?= htmlspecialchars(implode(', ', $plan['_assignees'])) ?></p>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php include __DIR__ . '/../../assets/footer.php'; ?>
</body>

</html>
<?php $conn->close(); ?>
