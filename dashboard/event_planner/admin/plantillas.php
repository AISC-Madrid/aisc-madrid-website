<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../_partials/helpers.php';

ep_require_board_or_admin($currentMember);

$errors = [];
$flash = ep_flash_take();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ep_require_valid_csrf();
    $action = $_POST['form_action'] ?? '';

    if ($action === 'save') {
        try {
            $id = !empty($_POST['id']) ? (int) $_POST['id'] : null;
            $data = [
                'key' => trim($_POST['key'] ?? ''),
                'title' => trim($_POST['title'] ?? ''),
                'description' => trim($_POST['description'] ?? '') ?: null,
                'team' => $_POST['team'] ?? 'EVENTOS',
                'default_slot' => $_POST['default_slot'] ?? 'EV_APOYO',
                'due_offset_days' => (int) ($_POST['due_offset_days'] ?? 0),
                'start_offset_days' => $_POST['start_offset_days'] !== '' ? (int) $_POST['start_offset_days'] : null,
                'gate' => $_POST['gate'] ?? 'none',
                'confirms_event' => isset($_POST['confirms_event']) ? 1 : 0,
                'sort_order' => (int) ($_POST['sort_order'] ?? 0),
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
            ];
            $dependsOn = array_map('intval', $_POST['depends_on'] ?? []);
            event_planner_save_template($conn, $id, $data, $dependsOn, (int) $currentMember['id']);
            ep_flash_set('success', 'Plantilla guardada. Los cambios solo afectan a eventos nuevos.');
            header('Location: /dashboard/event_planner/admin/plantillas.php');
            exit();
        } catch (EventPlannerValidationException|EventPlannerCycleException $e) {
            $errors[] = $e->getMessage();
        }
    } elseif ($action === 'toggle_active') {
        $id = (int) ($_POST['id'] ?? 0);
        $active = $_POST['active'] === '1';
        event_planner_set_template_active($conn, $id, $active);
        ep_flash_set('success', $active ? 'Plantilla activada.' : 'Plantilla desactivada.');
        header('Location: /dashboard/event_planner/admin/plantillas.php');
        exit();
    }
}

$templates = event_planner_list_templates($conn);
$editing = null;
if (isset($_GET['edit'])) {
    $editing = event_planner_get_template($conn, (int) $_GET['edit']);
}
$isNew = isset($_GET['new']);

$byTeam = ['EVENTOS' => [], 'REDES' => [], 'WEB' => []];
foreach ($templates as $t) {
    $byTeam[$t['team']][] = $t;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Plantillas de tareas - Planificador - AISC Madrid</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/css/styles3.css">
    <?php include __DIR__ . '/../_partials/styles.php'; ?>
</head>

<body class="bg-light">
    <?php include __DIR__ . '/../../../assets/nav_dashboard.php'; ?>

    <div class="container my-5 scroll-margin">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="m-0"><i class="bi bi-diagram-3 me-2"></i>Plantillas de tareas</h2>
            <div class="d-flex gap-2">
                <a href="/dashboard/event_planner/admin/checklist.php" class="btn btn-outline-secondary">Checklist del día</a>
                <a href="/dashboard/event_planner/admin/plantillas.php?new=1" class="btn btn-custom"><i class="bi bi-plus-lg"></i> Nueva plantilla</a>
            </div>
        </div>
        <div class="alert alert-warning">Los cambios aquí solo afectan a eventos que se creen a partir de ahora. Los eventos ya creados no cambian.</div>

        <?php if ($flash): ?>
            <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
        <?php endif; ?>

        <?php if ($editing || $isNew): ?>
            <?php
            $t = $editing ?? [
                'id' => null, 'key' => '', 'title' => '', 'description' => '', 'team' => 'EVENTOS',
                'default_slot' => 'EV_APOYO', 'due_offset_days' => 0, 'start_offset_days' => null,
                'gate' => 'none', 'confirms_event' => 0, 'sort_order' => 0, 'is_active' => 1, '_dep_ids' => [],
            ];
            ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header"><?= $editing ? 'Editar plantilla' : 'Nueva plantilla' ?></div>
                <form method="post" class="card-body">
                    <?= ep_csrf_field() ?>
                    <input type="hidden" name="form_action" value="save">
                    <?php if ($t['id']): ?><input type="hidden" name="id" value="<?= (int) $t['id'] ?>"><?php endif; ?>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small">Clave (key, única)</label>
                            <input type="text" name="key" class="form-control" required pattern="[a-z0-9_]+" value="<?= htmlspecialchars($t['key']) ?>">
                        </div>
                        <div class="col-md-9">
                            <label class="form-label small">Título</label>
                            <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($t['title']) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Descripción</label>
                            <textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($t['description'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Equipo</label>
                            <select name="team" class="form-select">
                                <?php foreach (['EVENTOS', 'REDES', 'WEB'] as $team): ?>
                                    <option value="<?= $team ?>" <?= $t['team'] === $team ? 'selected' : '' ?>><?= ep_team_label($team) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Slot por defecto</label>
                            <select name="default_slot" class="form-select">
                                <?php foreach (EVENT_PLANNER_SLOTS as $slot): ?>
                                    <option value="<?= $slot ?>" <?= $t['default_slot'] === $slot ? 'selected' : '' ?>><?= $slot ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Límite (días)</label>
                            <input type="number" name="due_offset_days" class="form-control" required value="<?= (int) $t['due_offset_days'] ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Inicio (días, opcional)</label>
                            <input type="number" name="start_offset_days" class="form-control" value="<?= $t['start_offset_days'] !== null ? (int) $t['start_offset_days'] : '' ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Orden</label>
                            <input type="number" name="sort_order" class="form-control" value="<?= (int) $t['sort_order'] ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Gate</label>
                            <select name="gate" class="form-select">
                                <option value="none" <?= $t['gate'] === 'none' ? 'selected' : '' ?>>Sin gate</option>
                                <option value="confirmed" <?= $t['gate'] === 'confirmed' ? 'selected' : '' ?>>Confirmado</option>
                                <option value="held" <?= $t['gate'] === 'held' ? 'selected' : '' ?>>Celebrado</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <div class="form-check">
                                <input type="checkbox" name="confirms_event" id="ce" class="form-check-input" <?= !empty($t['confirms_event']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="ce">Confirma el evento al completarse</label>
                            </div>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" id="ia" class="form-check-input" <?= !empty($t['is_active']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="ia">Activa</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Depende de</label>
                            <select name="depends_on[]" class="form-select" multiple size="8">
                                <?php foreach ($templates as $opt): ?>
                                    <?php if ($t['id'] && (int) $opt['id'] === (int) $t['id']) continue; ?>
                                    <option value="<?= (int) $opt['id'] ?>" <?= in_array((int) $opt['id'], $t['_dep_ids'] ?? [], true) ? 'selected' : '' ?>><?= ep_team_label($opt['team']) ?>: <?= htmlspecialchars($opt['title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-3">
                        <a href="/dashboard/event_planner/admin/plantillas.php" class="btn btn-outline-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-custom">Guardar</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <?php foreach (['EVENTOS', 'REDES', 'WEB'] as $team): ?>
            <h5 class="mt-4"><?= ep_team_badge($team) ?></h5>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead class="table-light">
                        <tr><th>Clave</th><th>Título</th><th>Límite</th><th>Inicio</th><th>Gate</th><th>Depende de</th><th>Estado</th><th></th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($byTeam[$team] as $t): ?>
                            <tr class="<?= $t['is_active'] ? '' : 'text-muted' ?>">
                                <td><code><?= htmlspecialchars($t['key']) ?></code></td>
                                <td><?= htmlspecialchars($t['title']) ?><?= $t['confirms_event'] ? ' <span class="badge bg-info">confirma</span>' : '' ?></td>
                                <td><?= (int) $t['due_offset_days'] ?></td>
                                <td><?= $t['start_offset_days'] !== null ? (int) $t['start_offset_days'] : '—' ?></td>
                                <td><?= htmlspecialchars($t['gate']) ?></td>
                                <td class="small"><?= htmlspecialchars(implode(', ', array_column($t['_deps'], 'title'))) ?></td>
                                <td><?= $t['is_active'] ? '<span class="badge bg-success">Activa</span>' : '<span class="badge bg-secondary">Inactiva</span>' ?></td>
                                <td class="text-end">
                                    <a href="?edit=<?= (int) $t['id'] ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                    <form method="post" class="d-inline">
                                        <?= ep_csrf_field() ?>
                                        <input type="hidden" name="form_action" value="toggle_active">
                                        <input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
                                        <input type="hidden" name="active" value="<?= $t['is_active'] ? '0' : '1' ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-secondary"><?= $t['is_active'] ? 'Desactivar' : 'Activar' ?></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
    </div>

    <?php include __DIR__ . '/../../../assets/footer.php'; ?>
</body>

</html>
<?php $conn->close(); ?>
