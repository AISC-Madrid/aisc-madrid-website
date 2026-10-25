<?php
require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/_partials/helpers.php';

ep_require_board_or_admin($currentMember);

if (!isset($_SESSION['ep_wizard'])) {
    $_SESSION['ep_wizard'] = ['plan' => [], 'slots' => []];
}
$wizard = &$_SESSION['ep_wizard'];

$step = 1;
$errors = [];

function ep_wizard_plan_from_post(): array
{
    return [
        'title' => trim($_POST['title'] ?? ''),
        'speaker_name' => trim($_POST['speaker_name'] ?? ''),
        'speaker_company' => trim($_POST['speaker_company'] ?? ''),
        'speaker_contact' => trim($_POST['speaker_contact'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'event_date' => trim($_POST['event_date'] ?? ''),
        'event_time' => trim($_POST['event_time'] ?? ''),
        'duration_minutes' => (int) ($_POST['duration_minutes'] ?? 60),
        'location' => trim($_POST['location'] ?? ''),
        'drive_folder_url' => trim($_POST['drive_folder_url'] ?? ''),
    ];
}

function ep_wizard_validate_plan(array $p): array
{
    $errors = [];
    if ($p['title'] === '') $errors[] = 'Falta el título del evento.';
    if ($p['speaker_name'] === '') $errors[] = 'Falta el nombre del ponente.';
    if ($p['event_date'] === '' || $p['event_time'] === '') $errors[] = 'Falta la fecha u hora del evento.';
    if ($p['duration_minutes'] <= 0) $errors[] = 'La duración debe ser mayor que 0.';
    return $errors;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ep_require_valid_csrf();
    $action = $_POST['wizard_action'] ?? '';

    if ($action === 'step1_next') {
        $plan = ep_wizard_plan_from_post();
        $errors = ep_wizard_validate_plan($plan);
        if (empty($errors)) {
            $wizard['plan'] = $plan;
            $step = 2;
        } else {
            $step = 1;
        }
    } elseif ($action === 'step2_back') {
        $step = 1;
    } elseif ($action === 'step2_next') {
        $slots = [];
        foreach (EVENT_PLANNER_SLOTS as $slot) {
            $slots[$slot] = (int) ($_POST['slot_' . $slot] ?? 0);
        }
        foreach (EVENT_PLANNER_SLOTS as $slot) {
            $team = EVENT_PLANNER_SLOT_TEAM[$slot];
            $candidates = array_column(event_planner_active_members_for_team($conn, $team), 'id');
            if (!in_array($slots[$slot], $candidates, true)) {
                $errors[] = "Elige un miembro activo del equipo " . ep_team_label($team) . " para el slot {$slot}.";
            }
        }
        if (empty($errors)) {
            $wizard['slots'] = $slots;
            $step = 3;
        } else {
            $step = 2;
        }
    } elseif ($action === 'step3_back') {
        $step = 2;
    } elseif ($action === 'step3_create') {
        $step = 3;
        try {
            if (empty($wizard['plan']) || empty($wizard['slots'])) {
                throw new EventPlannerValidationException('La sesión del asistente ha caducado. Empieza de nuevo.');
            }
            $p = $wizard['plan'];
            $startsAtUtc = event_planner_local_to_utc($p['event_date'] . ' ' . $p['event_time']);

            $includedKeys = array_keys($_POST['include'] ?? []);
            $overrides = [];
            foreach ($_POST['assignee'] ?? [] as $key => $memberId) {
                if ((int) $memberId > 0) {
                    $overrides[$key] = (int) $memberId;
                }
            }

            $customTasks = [];
            foreach ($_POST['custom_title'] ?? [] as $idx => $title) {
                $title = trim($title);
                if ($title === '') {
                    continue;
                }
                $dependsOn = [];
                foreach ($_POST['custom_depends_on'][$idx] ?? [] as $dep) {
                    if (str_starts_with($dep, 'tpl:')) {
                        $dependsOn[] = substr($dep, 4);
                    }
                }
                $dueMode = $_POST['custom_due_mode'][$idx] ?? 'offset';
                $startMode = $_POST['custom_start_mode'][$idx] ?? 'none';
                $customTasks[] = [
                    'key' => 'custom_' . $idx,
                    'title' => $title,
                    'description' => trim($_POST['custom_description'][$idx] ?? ''),
                    'team' => $_POST['custom_team'][$idx] ?? 'EVENTOS',
                    'assignee_id' => (int) ($_POST['custom_assignee'][$idx] ?? 0),
                    'due_offset_days' => $dueMode === 'offset' ? (int) ($_POST['custom_due_offset'][$idx] ?? 0) : null,
                    'due_date' => $dueMode === 'date' ? ($_POST['custom_due_date'][$idx] ?? null) : null,
                    'start_offset_days' => $startMode === 'offset' ? (int) ($_POST['custom_start_offset'][$idx] ?? 0) : null,
                    'start_date' => $startMode === 'date' ? ($_POST['custom_start_date'][$idx] ?? null) : null,
                    'gate' => $_POST['custom_gate'][$idx] ?? 'none',
                    'depends_on' => $dependsOn,
                ];
            }

            $planData = [
                'title' => $p['title'], 'description' => $p['description'] ?: null,
                'speaker_name' => $p['speaker_name'], 'speaker_company' => $p['speaker_company'] ?: null,
                'speaker_contact' => $p['speaker_contact'] ?: null, 'starts_at' => $startsAtUtc,
                'duration_minutes' => $p['duration_minutes'], 'location' => $p['location'] ?: null,
                'drive_folder_url' => $p['drive_folder_url'] ?: null,
            ];

            $planId = event_planner_create_plan($conn, $config, $planData, $wizard['slots'], $includedKeys, $overrides, $customTasks, (int) $currentMember['id']);

            unset($_SESSION['ep_wizard']);
            header('Location: /dashboard/event_planner/plan.php?id=' . $planId . '&created=1');
            exit();
        } catch (EventPlannerValidationException|EventPlannerCycleException $e) {
            $errors[] = $e->getMessage();
        }
    }
}

// Datos para render
$slotOptions = [];
foreach (EVENT_PLANNER_SLOTS as $slot) {
    $slotOptions[$slot] = event_planner_active_members_for_team($conn, EVENT_PLANNER_SLOT_TEAM[$slot]);
}

$templates = [];
$eventDateForPreview = null;
if ($step === 3 && !empty($wizard['plan']['event_date'])) {
    $eventDateForPreview = new DateTimeImmutable($wizard['plan']['event_date'], event_planner_timezone());
    $stmt = $conn->prepare("SELECT * FROM task_templates WHERE is_active = 1 ORDER BY sort_order");
    $stmt->execute();
    $templates = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    foreach ($templates as &$tpl) {
        $tpl['_due_date'] = event_planner_add_days($eventDateForPreview, (int) $tpl['due_offset_days'])->format('Y-m-d');
        $tpl['_start_date'] = $tpl['start_offset_days'] !== null
            ? event_planner_add_days($eventDateForPreview, (int) $tpl['start_offset_days'])->format('Y-m-d')
            : null;
        $tpl['_default_assignee_id'] = (int) ($wizard['slots'][$tpl['default_slot']] ?? 0);
    }
    unset($tpl);
}

$checklistPreview = [];
if ($step === 3) {
    $stmt = $conn->prepare("SELECT team, title FROM checklist_templates WHERE is_active = 1 ORDER BY team, sort_order");
    $stmt->execute();
    foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $row) {
        $checklistPreview[$row['team']][] = $row['title'];
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nuevo evento - Planificador - AISC Madrid</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/css/styles3.css">
    <?php include __DIR__ . '/_partials/styles.php'; ?>
</head>

<body class="bg-light">
    <?php include __DIR__ . '/../../assets/nav_dashboard.php'; ?>

    <div class="container my-5 scroll-margin">
        <h2 class="mb-4"><i class="bi bi-plus-lg me-2"></i>Nuevo evento</h2>

        <div class="ep-stepper">
            <?php
            $stepDefs = [1 => ['Datos', 'bi-card-text'], 2 => ['Personas', 'bi-people-fill'], 3 => ['Revisar tareas', 'bi-list-check']];
            foreach ($stepDefs as $num => [$label, $icon]):
                $state = $num === $step ? 'is-active' : ($num < $step ? 'is-done' : '');
                ?>
                <div class="ep-step <?= $state ?>">
                    <span class="ep-step-circle"><?php if ($num < $step): ?><i class="bi bi-check-lg"></i><?php else: ?><?= $num ?><?php endif; ?></span>
                    <span class="ep-step-label"><?= $label ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($step === 1): ?>
            <?php $p = array_merge(['title' => '', 'speaker_name' => '', 'speaker_company' => '', 'speaker_contact' => '', 'description' => '', 'event_date' => '', 'event_time' => '18:00', 'duration_minutes' => 60, 'location' => '', 'drive_folder_url' => ''], $wizard['plan']); ?>
            <form method="post" class="card card-body shadow-sm">
                <?= ep_csrf_field() ?>
                <input type="hidden" name="wizard_action" value="step1_next">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Título del evento *</label>
                        <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($p['title']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Ponente *</label>
                        <input type="text" name="speaker_name" class="form-control" required value="<?= htmlspecialchars($p['speaker_name']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Empresa del ponente</label>
                        <input type="text" name="speaker_company" class="form-control" value="<?= htmlspecialchars($p['speaker_company']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contacto del ponente</label>
                        <input type="text" name="speaker_contact" class="form-control" value="<?= htmlspecialchars($p['speaker_contact']) ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Descripción</label>
                        <textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($p['description']) ?></textarea>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fecha *</label>
                        <input type="date" name="event_date" class="form-control" required value="<?= htmlspecialchars($p['event_date']) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Hora *</label>
                        <input type="time" name="event_time" class="form-control" required value="<?= htmlspecialchars($p['event_time']) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Duración (min) *</label>
                        <input type="number" name="duration_minutes" class="form-control" required min="1" value="<?= (int) $p['duration_minutes'] ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Sala / lugar</label>
                        <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($p['location']) ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Link de la carpeta de Drive (opcional)</label>
                        <input type="url" name="drive_folder_url" class="form-control" value="<?= htmlspecialchars($p['drive_folder_url']) ?>">
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-custom">Siguiente <i class="bi bi-arrow-right"></i></button>
                </div>
            </form>

        <?php elseif ($step === 2): ?>
            <form method="post" class="card card-body shadow-sm">
                <?= ep_csrf_field() ?>
                <input type="hidden" name="wizard_action" value="step2_next">
                <p class="text-muted">Elige una persona activa de cada equipo para cada slot.</p>
                <div class="row g-3">
                    <?php
                    $slotLabels = [
                        'EV_CONTACTO' => 'Eventos — Contacto con el ponente',
                        'EV_APOYO' => 'Eventos — Apoyo (sala, material...)',
                        'REDES_1' => 'Redes — Carteles y publicación',
                        'REDES_2' => 'Redes — Comunidad y reel',
                        'WEB' => 'Web',
                    ];
                    foreach (EVENT_PLANNER_SLOTS as $slot):
                        $current = (int) ($wizard['slots'][$slot] ?? 0);
                        ?>
                        <div class="col-md-6">
                            <label class="form-label"><?= htmlspecialchars($slotLabels[$slot]) ?> *</label>
                            <select name="slot_<?= $slot ?>" class="form-select" required>
                                <option value="">— Elegir —</option>
                                <?php foreach ($slotOptions[$slot] as $m): ?>
                                    <option value="<?= $m['id'] ?>" <?= $current === (int) $m['id'] ? 'selected' : '' ?>><?= htmlspecialchars($m['full_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (empty($slotOptions[$slot])): ?>
                                <div class="form-text text-danger">No hay miembros activos en este equipo.</div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="d-flex justify-content-between mt-3">
                    <button type="submit" name="wizard_action" value="step2_back" formnovalidate class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Atrás</button>
                    <button type="submit" class="btn btn-custom">Siguiente <i class="bi bi-arrow-right"></i></button>
                </div>
            </form>

        <?php elseif ($step === 3): ?>
            <?php
            $hasPastDue = false;
            foreach ($templates as $tpl) {
                if ($tpl['_due_date'] < event_planner_today()->format('Y-m-d')) {
                    $hasPastDue = true;
                    break;
                }
            }
            ?>
            <?php if ($hasPastDue): ?>
                <div class="alert alert-warning">Alguna fecha límite calculada ya está en el pasado. Revisa la fecha del evento o desmarca esas tareas.</div>
            <?php endif; ?>

            <form method="post" id="ep-step3-form">
                <?= ep_csrf_field() ?>
                <input type="hidden" name="wizard_action" id="ep-wizard-action" value="step3_create">

                <?php
                $byTeam = ['EVENTOS' => [], 'REDES' => [], 'WEB' => []];
                foreach ($templates as $tpl) {
                    $byTeam[$tpl['team']][] = $tpl;
                }
                foreach ($byTeam as $team => $tasks):
                    if (empty($tasks)) continue;
                    ?>
                    <div class="card mb-3 shadow-sm">
                        <div class="card-header"><?= ep_team_badge($team) ?></div>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Incluir</th><th>Tarea</th><th>Asignado</th><th>Límite</th><th>Inicio</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tasks as $tpl): ?>
                                        <tr>
                                            <td><input type="checkbox" class="form-check-input" name="include[<?= htmlspecialchars($tpl['key']) ?>]" value="1" checked></td>
                                            <td><?= htmlspecialchars($tpl['title']) ?></td>
                                            <td>
                                                <select name="assignee[<?= htmlspecialchars($tpl['key']) ?>]" class="form-select form-select-sm">
                                                    <?php foreach (event_planner_active_members_for_team($conn, $team) as $m): ?>
                                                        <option value="<?= $m['id'] ?>" <?= (int) $m['id'] === $tpl['_default_assignee_id'] ? 'selected' : '' ?>><?= htmlspecialchars($m['full_name']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td><?= ep_fmt_date($tpl['_due_date']) ?></td>
                                            <td><?= $tpl['_start_date'] ? ep_fmt_date($tpl['_start_date']) : '—' ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="card mb-3 shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        Tareas personalizadas
                        <button type="button" class="btn btn-sm btn-outline-primary" id="ep-add-custom-task"><i class="bi bi-plus-lg"></i> Añadir</button>
                    </div>
                    <div class="card-body" id="ep-custom-tasks"></div>
                </div>

                <div class="card mb-3 shadow-sm">
                    <div class="card-header">Checklist del día del evento (vista previa)</div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($checklistPreview as $team => $items): ?>
                                <div class="col-md-4">
                                    <h6><?= ep_team_label($team) ?></h6>
                                    <ul class="small">
                                        <?php foreach ($items as $i): ?><li><?= htmlspecialchars($i) ?></li><?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <button type="submit" name="wizard_action" value="step3_back" formnovalidate class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Atrás</button>
                    <button type="submit" class="btn btn-custom"><i class="bi bi-check-lg"></i> Crear evento</button>
                </div>
            </form>

            <template id="ep-custom-task-template">
                <div class="row g-2 border rounded p-2 mb-2 ep-custom-task-row">
                    <div class="col-md-4"><input type="text" name="custom_title[__IDX__]" class="form-control form-control-sm" placeholder="Título"></div>
                    <div class="col-md-2">
                        <select name="custom_team[__IDX__]" class="form-select form-select-sm">
                            <option value="EVENTOS">Eventos</option>
                            <option value="REDES">Redes</option>
                            <option value="WEB">Web</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="custom_assignee[__IDX__]" class="form-select form-select-sm">
                            <?php foreach (array_merge($slotOptions['EV_CONTACTO'], $slotOptions['REDES_1'], $slotOptions['WEB']) as $m): ?>
                                <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="custom_gate[__IDX__]" class="form-select form-select-sm">
                            <option value="none">Sin gate</option>
                            <option value="confirmed">Confirmado</option>
                            <option value="held">Celebrado</option>
                        </select>
                    </div>
                    <div class="col-md-6"><textarea name="custom_description[__IDX__]" class="form-control form-control-sm" placeholder="Descripción" rows="1"></textarea></div>
                    <div class="col-md-3">
                        <label class="form-label small mb-0">Límite (días desde el evento)</label>
                        <input type="hidden" name="custom_due_mode[__IDX__]" value="offset">
                        <input type="number" name="custom_due_offset[__IDX__]" class="form-control form-control-sm" value="0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-0">Inicio (opcional, días)</label>
                        <input type="hidden" name="custom_start_mode[__IDX__]" value="none" class="ep-start-mode">
                        <input type="number" name="custom_start_offset[__IDX__]" class="form-control form-control-sm ep-start-offset" placeholder="(vacío = sin inicio fijo)">
                    </div>
                    <div class="col-12">
                        <label class="form-label small mb-0">Depende de</label>
                        <select name="custom_depends_on[__IDX__][]" class="form-select form-select-sm" multiple size="4">
                            <?php foreach ($templates as $tpl): ?>
                                <option value="tpl:<?= htmlspecialchars($tpl['key']) ?>"><?= ep_team_label($tpl['team']) ?>: <?= htmlspecialchars($tpl['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 text-end">
                        <button type="button" class="btn btn-sm btn-outline-danger ep-remove-custom-task">Quitar</button>
                    </div>
                </div>
            </template>

            <script>
                (function () {
                    var idx = 0;
                    var container = document.getElementById('ep-custom-tasks');
                    var tpl = document.getElementById('ep-custom-task-template');

                    document.getElementById('ep-add-custom-task').addEventListener('click', function () {
                        var html = tpl.innerHTML.split('__IDX__').join(String(idx));
                        var wrapper = document.createElement('div');
                        wrapper.innerHTML = html;
                        container.appendChild(wrapper);
                        idx++;
                    });

                    container.addEventListener('click', function (e) {
                        if (e.target.classList.contains('ep-remove-custom-task')) {
                            e.target.closest('.ep-custom-task-row').remove();
                        }
                    });

                    // Si se rellena el offset de inicio, marcamos el modo como "offset" (si se deja
                    // vacío, el servidor interpreta que la tarea no tiene fecha de inicio fija).
                    container.addEventListener('input', function (e) {
                        if (e.target.classList.contains('ep-start-offset')) {
                            var modeInput = e.target.closest('.ep-custom-task-row').querySelector('.ep-start-mode');
                            modeInput.value = e.target.value === '' ? 'none' : 'offset';
                        }
                    });
                })();
            </script>
        <?php endif; ?>
    </div>

    <?php include __DIR__ . '/../../assets/footer.php'; ?>
</body>

</html>
<?php $conn->close(); ?>
