<?php
require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/_partials/helpers.php';

$planId = (int) ($_GET['id'] ?? 0);
$plan = ep_fetch_plan($conn, $planId);
if (!$plan) {
    http_response_code(404);
    exit('Evento no encontrado.');
}

$flash = ep_flash_take();
$deepLinkTaskId = (int) ($_GET['task'] ?? 0);

$stmt = $conn->prepare(
    "SELECT et.*, a.full_name AS assignee_name, cb.full_name AS completed_by_name, sb.full_name AS skipped_by_name
     FROM event_tasks et
     JOIN members a ON a.id = et.assignee_id
     LEFT JOIN members cb ON cb.id = et.completed_by
     LEFT JOIN members sb ON sb.id = et.skipped_by
     WHERE et.event_plan_id = ? ORDER BY et.sort_order, et.due_date"
);
$stmt->bind_param('i', $planId);
$stmt->execute();
$tasks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$tasksById = [];
foreach ($tasks as $t) {
    $tasksById[(int) $t['id']] = $t;
}

$stmt = $conn->prepare(
    "SELECT etd.task_id, etd.depends_on_task_id, dt.title AS dep_title, dt.status AS dep_status
     FROM event_task_dependencies etd
     JOIN event_tasks dt ON dt.id = etd.depends_on_task_id
     WHERE etd.task_id IN (SELECT id FROM event_tasks WHERE event_plan_id = ?)"
);
$stmt->bind_param('i', $planId);
$stmt->execute();
$depsByTask = [];
foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $row) {
    $depsByTask[(int) $row['task_id']][] = ['title' => $row['dep_title'], 'status' => $row['dep_status']];
}
$stmt->close();

$stmt = $conn->prepare("SELECT epm.slot, m.full_name, m.id FROM event_plan_members epm JOIN members m ON m.id = epm.member_id WHERE epm.event_plan_id = ? ORDER BY epm.slot");
$stmt->bind_param('i', $planId);
$stmt->execute();
$planMembers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$stmt = $conn->prepare("SELECT eci.*, m.full_name AS done_by_name FROM event_checklist_items eci LEFT JOIN members m ON m.id = eci.done_by WHERE eci.event_plan_id = ? ORDER BY eci.team, eci.sort_order");
$stmt->bind_param('i', $planId);
$stmt->execute();
$checklistItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$checklistByTeam = ['EVENTOS' => [], 'REDES' => [], 'WEB' => []];
foreach ($checklistItems as $ci) {
    $checklistByTeam[$ci['team']][] = $ci;
}

$stmt = $conn->prepare("SELECT eal.*, m.full_name AS actor_name FROM event_activity_log eal LEFT JOIN members m ON m.id = eal.actor_id WHERE eal.event_plan_id = ? ORDER BY eal.created_at DESC, eal.id DESC LIMIT 100");
$stmt->bind_param('i', $planId);
$stmt->execute();
$activity = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$counts = ['blocked' => 0, 'active' => 0, 'done' => 0, 'skipped' => 0];
foreach ($tasks as $t) {
    $counts[$t['status']]++;
}
$total = array_sum($counts);
$denominator = $total - $counts['skipped'];
$progress = $denominator > 0 ? round(100 * $counts['done'] / $denominator) : 100;

$byStatus = ['blocked' => [], 'active' => [], 'done' => [], 'skipped' => []];
$byTeam = ['EVENTOS' => [], 'REDES' => [], 'WEB' => []];
foreach ($tasks as $t) {
    $byStatus[$t['status']][] = $t;
    $byTeam[$t['team']][] = $t;
}

$membersByTeamJs = [];
foreach (['EVENTOS', 'REDES', 'WEB'] as $team) {
    $membersByTeamJs[$team] = event_planner_active_members_for_team($conn, $team);
}

// Línea temporal: rango desde la primera fecha (inicio o límite) hasta la última.
$timelineDates = [];
foreach ($tasks as $t) {
    if ($t['status'] === 'skipped') {
        continue;
    }
    $timelineDates[] = $t['start_date'] ?? $t['due_date'];
    $timelineDates[] = $t['due_date'];
}
$eventDateStr = event_planner_event_date($plan['starts_at'])->format('Y-m-d');
$timelineDates[] = $eventDateStr;
$timelineDates[] = event_planner_today()->format('Y-m-d');
sort($timelineDates);
$timelineMin = new DateTimeImmutable($timelineDates[0], event_planner_timezone());
$timelineMax = new DateTimeImmutable(end($timelineDates), event_planner_timezone());
$timelineSpanDays = max(1, (int) $timelineMin->diff($timelineMax)->days);

function ep_timeline_pct(string $date, DateTimeImmutable $min, int $spanDays): float
{
    $d = new DateTimeImmutable($date, event_planner_timezone());
    $offset = (int) $min->diff($d)->format('%r%a');
    return max(0, min(100, ($offset / $spanDays) * 100));
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($plan['title']) ?> - Planificador - AISC Madrid</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/css/styles3.css">
    <?php include __DIR__ . '/_partials/styles.php'; ?>
</head>

<body class="bg-light">
    <?php include __DIR__ . '/../../assets/nav_dashboard.php'; ?>

    <div class="container-fluid my-5 scroll-margin" style="max-width: 1100px;">
        <?php if ($flash): ?>
            <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show">
                <?= htmlspecialchars($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Cabecera -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                            <h3 class="mb-0"><?= htmlspecialchars($plan['title']) ?></h3>
                            <?= ep_plan_status_badge($plan['status']) ?>
                        </div>
                        <p class="ep-event-meta mb-0"><i class="bi bi-mic-fill"></i> <?= htmlspecialchars($plan['speaker_name']) ?><?= $plan['speaker_company'] ? ' · ' . htmlspecialchars($plan['speaker_company']) : '' ?></p>
                        <p class="ep-event-meta mb-0"><i class="bi bi-calendar3"></i> <?= ep_fmt_datetime_madrid($plan['starts_at']) ?> · <?= (int) $plan['duration_minutes'] ?> min</p>
                        <?php if ($plan['location']): ?><p class="ep-event-meta mb-0"><i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($plan['location']) ?></p><?php endif; ?>
                        <?php if ($plan['drive_folder_url']): ?><p class="mb-0 mt-1"><a href="<?= htmlspecialchars($plan['drive_folder_url']) ?>" target="_blank"><i class="bi bi-folder2-open me-1"></i>Carpeta de Drive</a></p><?php endif; ?>
                        <?php if ($plan['public_event_id']): ?><p class="mb-0"><a href="/events/evento.php?id=<?= (int) $plan['public_event_id'] ?>" target="_blank"><i class="bi bi-link-45deg me-1"></i>Ver evento público</a></p><?php endif; ?>
                        <p class="mb-0 mt-2"><a href="/dashboard/event_planner/report_pdf.php?id=<?= $planId ?>" class="btn btn-sm btn-outline-secondary" target="_blank"><i class="bi bi-file-earmark-pdf me-1"></i>Descargar informe PDF</a></p>
                    </div>
                    <?php if ($isBoardOrAdmin): ?>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">Acciones</button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#epEditModal">Editar datos</a></li>
                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#epDateModal">Cambiar fecha/hora</a></li>
                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#epLinkModal">Enlazar con evento público</a></li>
                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#epAddTaskModal">Añadir tarea</a></li>
                                <li><a class="dropdown-item" href="/dashboard/event_planner/admin/plantillas.php">Plantillas de tareas</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <?php if ($plan['status'] === 'draft'): ?>
                                    <li><form method="post" action="/dashboard/event_planner/transition_status.php"><?= ep_csrf_field() ?><input type="hidden" name="plan_id" value="<?= $planId ?>"><input type="hidden" name="new_status" value="confirmed"><button class="dropdown-item" type="submit">Confirmar evento</button></form></li>
                                <?php endif; ?>
                                <?php if ($plan['status'] === 'held'): ?>
                                    <li><form method="post" action="/dashboard/event_planner/transition_status.php"><?= ep_csrf_field() ?><input type="hidden" name="plan_id" value="<?= $planId ?>"><input type="hidden" name="new_status" value="closed"><button class="dropdown-item" type="submit">Cerrar evento</button></form></li>
                                <?php endif; ?>
                                <?php if (!in_array($plan['status'], ['closed', 'cancelled'], true)): ?>
                                    <li><form method="post" action="/dashboard/event_planner/transition_status.php" onsubmit="return confirm('¿Cancelar este evento? Se avisará a todos los asignados.');"><?= ep_csrf_field() ?><input type="hidden" name="plan_id" value="<?= $planId ?>"><input type="hidden" name="new_status" value="cancelled"><button class="dropdown-item text-danger" type="submit">Cancelar evento</button></form></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="progress ep-progress-thin mb-2"><div class="progress-bar" style="width: <?= $progress ?>%"></div></div>
                <p class="small text-muted mb-3"><?= $progress ?>% · <?= $counts['done'] ?> completadas, <?= $counts['active'] ?> activas, <?= $counts['blocked'] ?> bloqueadas, <?= $counts['skipped'] ?> no aplica</p>

                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($planMembers as $pm): ?>
                        <span class="badge text-bg-light border"><i class="bi bi-person-fill me-1"></i><?= htmlspecialchars($pm['slot']) ?>: <?= htmlspecialchars($pm['full_name']) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Tablero -->
        <ul class="nav nav-pills mb-3" id="epBoardTabs">
            <li class="nav-item"><button class="nav-link active" data-ep-board="status">Por estado</button></li>
            <li class="nav-item"><button class="nav-link" data-ep-board="team">Por equipo</button></li>
        </ul>

        <div id="epBoardStatus">
            <div class="row g-3">
                <?php foreach (['blocked' => 'Bloqueada', 'active' => 'Activa', 'done' => 'Completada'] as $status => $label): ?>
                    <div class="col-md-4">
                        <div class="ep-kanban-head">
                            <span class="fw-semibold"><i class="bi <?= ep_status_icon($status) ?> me-1"></i><?= $label ?></span>
                            <span class="ep-kanban-count"><?= count($byStatus[$status]) ?></span>
                        </div>
                        <div class="ep-kanban-col">
                            <?php foreach ($byStatus[$status] as $t): ?>
                                <?php include __DIR__ . '/_partials/task_card.php'; ?>
                            <?php endforeach; ?>
                            <?php if (empty($byStatus[$status])): ?><p class="text-muted small text-center mb-0 py-2">Sin tareas</p><?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if (!empty($byStatus['skipped'])): ?>
                <div class="mt-3">
                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#epSkippedCol"><i class="bi bi-slash-circle me-1"></i>No aplica (<?= count($byStatus['skipped']) ?>)</button>
                    <div class="collapse ep-kanban-col mt-2" id="epSkippedCol">
                        <?php foreach ($byStatus['skipped'] as $t): ?>
                            <?php include __DIR__ . '/_partials/task_card.php'; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div id="epBoardTeam" style="display:none;">
            <div class="row g-3">
                <?php foreach (['EVENTOS', 'REDES', 'WEB'] as $team): ?>
                    <div class="col-md-4">
                        <div class="ep-kanban-head">
                            <?= ep_team_badge($team) ?>
                            <span class="ep-kanban-count"><?= count($byTeam[$team]) ?></span>
                        </div>
                        <div class="ep-kanban-col">
                            <?php foreach ($byTeam[$team] as $t): ?>
                                <?php include __DIR__ . '/_partials/task_card.php'; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Línea temporal -->
        <div class="card shadow-sm my-4">
            <div class="card-header"><i class="bi bi-clock-history me-1"></i>Línea temporal</div>
            <div class="card-body">
                <div class="ep-timeline-legend">
                    <span><span class="dot" style="background:#6c757d"></span>Bloqueada</span>
                    <span><span class="dot" style="background:#0d6efd"></span>Activa</span>
                    <span><span class="dot" style="background:#198754"></span>Completada</span>
                    <span><span class="dot" style="background:#212529;opacity:.5"></span>Hoy</span>
                    <span><span class="dot" style="background:var(--ep-primary)"></span>Día del evento</span>
                </div>
                <?php foreach (['EVENTOS', 'REDES', 'WEB'] as $team): ?>
                    <div class="mb-3">
                        <small class="text-muted fw-semibold"><i class="bi <?= ep_team_icon($team) ?> me-1"></i><?= ep_team_label($team) ?></small>
                        <div class="ep-timeline-row">
                            <div class="ep-timeline-track"></div>
                            <?php
                            $todayPct = ep_timeline_pct(event_planner_today()->format('Y-m-d'), $timelineMin, $timelineSpanDays);
                            $eventPct = ep_timeline_pct($eventDateStr, $timelineMin, $timelineSpanDays);
                            ?>
                            <div class="ep-timeline-marker" style="left: <?= $todayPct ?>%;" title="Hoy"></div>
                            <div class="ep-timeline-marker ep-timeline-event" style="left: <?= $eventPct ?>%;" title="Día del evento"></div>
                            <?php foreach ($byTeam[$team] as $t): if ($t['status'] === 'skipped') continue; ?>
                                <?php
                                $duePct = ep_timeline_pct($t['due_date'], $timelineMin, $timelineSpanDays);
                                $color = match ($t['status']) { 'blocked' => '#6c757d', 'active' => '#0d6efd', 'done' => '#198754', default => '#adb5bd' };
                                ?>
                                <?php if ($t['start_date']): ?>
                                    <?php $startPct = ep_timeline_pct($t['start_date'], $timelineMin, $timelineSpanDays); ?>
                                    <div class="ep-timeline-bar" style="left: <?= min($startPct, $duePct) ?>%; width: <?= max(1, abs($duePct - $startPct)) ?>%; background: <?= $color ?>;" title="<?= htmlspecialchars($t['title']) ?>"></div>
                                <?php else: ?>
                                    <div class="ep-timeline-dot" style="left: <?= $duePct ?>%; background: <?= $color ?>;" title="<?= htmlspecialchars($t['title']) ?>"></div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Checklist del día del evento -->
        <div class="card shadow-sm mb-4">
            <div class="card-header"><i class="bi bi-check2-square me-1"></i>Checklist del día del evento</div>
            <div class="card-body">
                <div class="row">
                    <?php foreach (['EVENTOS', 'REDES', 'WEB'] as $team): ?>
                        <div class="col-md-4">
                            <h6><?= ep_team_badge($team, true) ?></h6>
                            <?php foreach ($checklistByTeam[$team] as $ci): ?>
                                <form method="post" action="/dashboard/event_planner/toggle_checklist.php" class="form-check ep-checklist-item mb-1">
                                    <?= ep_csrf_field() ?>
                                    <input type="hidden" name="plan_id" value="<?= $planId ?>">
                                    <input type="hidden" name="item_id" value="<?= (int) $ci['id'] ?>">
                                    <input type="hidden" name="done" value="<?= $ci['done'] ? '0' : '1' ?>">
                                    <input class="form-check-input" type="checkbox" <?= $ci['done'] ? 'checked' : '' ?> onchange="this.form.submit()">
                                    <label class="form-check-label <?= $ci['done'] ? 'text-decoration-line-through text-muted' : '' ?>"><?= htmlspecialchars($ci['title']) ?></label>
                                    <?php if ($ci['done']): ?><div class="form-text"><i class="bi bi-check-circle-fill text-success me-1"></i>por <?= htmlspecialchars($ci['done_by_name'] ?? '?') ?></div><?php endif; ?>
                                </form>
                            <?php endforeach; ?>
                            <?php if (empty($checklistByTeam[$team])): ?><p class="text-muted small">Sin ítems.</p><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Actividad -->
        <div class="card shadow-sm mb-5">
            <div class="card-header"><i class="bi bi-activity me-1"></i>Actividad</div>
            <div class="card-body">
                <?php foreach ($activity as $log): ?>
                    <div class="ep-activity-item">
                        <span class="ep-activity-icon"><i class="bi <?= ep_activity_icon($log['action']) ?>"></i></span>
                        <div>
                            <div class="small"><strong><?= htmlspecialchars($log['actor_name'] ?? 'Sistema') ?></strong> — <?= htmlspecialchars(ep_activity_label($log['action'])) ?></div>
                            <div class="text-muted" style="font-size:.78rem;"><?= (new DateTimeImmutable($log['created_at'], new DateTimeZone('UTC')))->setTimezone(event_planner_timezone())->format('d/m/Y H:i') ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($activity)): ?><p class="text-muted small mb-0">Sin actividad todavía.</p><?php endif; ?>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/_partials/task_modal.php'; ?>
    <?php if ($isBoardOrAdmin): ?>
        <?php include __DIR__ . '/_partials/board_modals.php'; ?>
    <?php endif; ?>

    <?php include __DIR__ . '/../../assets/footer.php'; ?>

    <script>
        var EP_MEMBERS_BY_TEAM = <?= json_encode($membersByTeamJs, JSON_UNESCAPED_UNICODE) ?>;
        var EP_IS_BOARD = <?= $isBoardOrAdmin ? 'true' : 'false' ?>;
        var EP_CURRENT_MEMBER_ID = <?= (int) $currentMember['id'] ?>;
        var EP_DEEP_LINK_TASK = <?= $deepLinkTaskId ?: 'null' ?>;

        document.querySelectorAll('#epBoardTabs .nav-link').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.querySelectorAll('#epBoardTabs .nav-link').forEach(function (b) { b.classList.remove('active'); });
                btn.classList.add('active');
                document.getElementById('epBoardStatus').style.display = btn.dataset.epBoard === 'status' ? '' : 'none';
                document.getElementById('epBoardTeam').style.display = btn.dataset.epBoard === 'team' ? '' : 'none';
            });
        });

        var epModalEl = document.getElementById('epTaskModal');
        var epModal = new bootstrap.Modal(epModalEl);

        function epOpenTask(card) {
            var d = card.dataset;
            document.getElementById('epModalTitle').textContent = d.title;
            document.getElementById('epModalTeamBadge').innerHTML = d.teamBadge;
            document.getElementById('epModalStatusBadge').innerHTML = d.statusBadge;
            document.getElementById('epModalDescription').textContent = d.description || '(sin descripción)';
            document.getElementById('epModalDue').textContent = d.due;
            document.getElementById('epModalStart').textContent = d.start || '—';
            document.getElementById('epModalAssignee').textContent = d.assigneeName;

            var deps = JSON.parse(d.deps || '[]');
            var depsEl = document.getElementById('epModalDeps');
            depsEl.innerHTML = '';
            if (deps.length === 0) {
                depsEl.innerHTML = '<li class="text-muted">Sin dependencias</li>';
            } else {
                deps.forEach(function (dep) {
                    var li = document.createElement('li');
                    li.textContent = dep.title + ' — ' + dep.status;
                    depsEl.appendChild(li);
                });
            }

            var notesEl = document.getElementById('epModalNotes');
            if (d.notes) {
                notesEl.textContent = 'Notas: ' + d.notes;
                notesEl.style.display = '';
            } else {
                notesEl.style.display = 'none';
            }

            var completedEl = document.getElementById('epModalCompletedBy');
            if (d.completedBy) {
                completedEl.textContent = 'Completada por ' + d.completedBy + ' el ' + d.completedAt;
                completedEl.style.display = '';
            } else {
                completedEl.style.display = 'none';
            }

            ['epFormComplete', 'epFormSkip', 'epFormReassign'].forEach(function (id) {
                var form = document.getElementById(id);
                if (form) form.querySelector('input[name=task_id]').value = d.id;
            });

            var completeForm = document.getElementById('epFormComplete');
            if (completeForm) completeForm.style.display = (d.status === 'active') ? '' : 'none';

            var skipForm = document.getElementById('epFormSkip');
            if (skipForm) skipForm.style.display = (EP_IS_BOARD && d.status !== 'done') ? '' : 'none';

            var reassignForm = document.getElementById('epFormReassign');
            if (reassignForm) reassignForm.style.display = (EP_IS_BOARD && d.status !== 'done') ? '' : 'none';

            var select = document.getElementById('epReassignSelect');
            if (select) {
                select.innerHTML = '';
                (EP_MEMBERS_BY_TEAM[d.team] || []).forEach(function (m) {
                    var opt = document.createElement('option');
                    opt.value = m.id;
                    opt.textContent = m.full_name;
                    if (String(m.id) === d.assigneeId) opt.selected = true;
                    select.appendChild(opt);
                });
            }

            epModal.show();
        }

        document.querySelectorAll('.ep-task-card').forEach(function (card) {
            card.addEventListener('click', function () { epOpenTask(card); });
        });

        if (EP_DEEP_LINK_TASK) {
            var target = document.querySelector('.ep-task-card[data-id="' + EP_DEEP_LINK_TASK + '"]');
            if (target) epOpenTask(target);
        }
    </script>
</body>

</html>
<?php $conn->close(); ?>
