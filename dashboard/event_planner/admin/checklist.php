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
            event_planner_save_checklist_template($conn, $id, [
                'team' => $_POST['team'] ?? 'EVENTOS',
                'title' => trim($_POST['title'] ?? ''),
                'sort_order' => (int) ($_POST['sort_order'] ?? 0),
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
            ]);
            ep_flash_set('success', 'Ítem de checklist guardado.');
        } catch (EventPlannerValidationException $e) {
            $errors[] = $e->getMessage();
        }
        if (empty($errors)) {
            header('Location: /dashboard/event_planner/admin/checklist.php');
            exit();
        }
    } elseif ($action === 'delete') {
        event_planner_delete_checklist_template($conn, (int) ($_POST['id'] ?? 0));
        ep_flash_set('success', 'Ítem eliminado.');
        header('Location: /dashboard/event_planner/admin/checklist.php');
        exit();
    }
}

$items = event_planner_list_checklist_templates($conn);
$editing = null;
foreach ($items as $i) {
    if (isset($_GET['edit']) && (int) $_GET['edit'] === (int) $i['id']) {
        $editing = $i;
    }
}
$isNew = isset($_GET['new']);

$byTeam = ['EVENTOS' => [], 'REDES' => [], 'WEB' => []];
foreach ($items as $i) {
    $byTeam[$i['team']][] = $i;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Checklist del día - Planificador - AISC Madrid</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/css/styles3.css">
    <?php include __DIR__ . '/../_partials/styles.php'; ?>
</head>

<body class="bg-light">
    <?php include __DIR__ . '/../../../assets/nav_dashboard.php'; ?>

    <div class="container my-5 scroll-margin">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="m-0"><i class="bi bi-check2-square me-2"></i>Checklist del día del evento</h2>
            <div class="d-flex gap-2">
                <a href="/dashboard/event_planner/admin/plantillas.php" class="btn btn-outline-secondary">Plantillas de tareas</a>
                <a href="?new=1" class="btn btn-custom"><i class="bi bi-plus-lg"></i> Nuevo ítem</a>
            </div>
        </div>
        <div class="alert alert-warning">Los cambios aquí solo afectan a eventos que se creen a partir de ahora.</div>

        <?php if ($flash): ?><div class="alert alert-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div><?php endif; ?>
        <?php if (!empty($errors)): ?><div class="alert alert-danger"><?php foreach ($errors as $e): ?><?= htmlspecialchars($e) ?><?php endforeach; ?></div><?php endif; ?>

        <?php if ($editing || $isNew): ?>
            <?php $it = $editing ?? ['id' => null, 'team' => 'EVENTOS', 'title' => '', 'sort_order' => 0, 'is_active' => 1]; ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header"><?= $editing ? 'Editar ítem' : 'Nuevo ítem' ?></div>
                <form method="post" class="card-body row g-3">
                    <?= ep_csrf_field() ?>
                    <input type="hidden" name="form_action" value="save">
                    <?php if ($it['id']): ?><input type="hidden" name="id" value="<?= (int) $it['id'] ?>"><?php endif; ?>
                    <div class="col-md-3">
                        <label class="form-label small">Equipo</label>
                        <select name="team" class="form-select">
                            <?php foreach (['EVENTOS', 'REDES', 'WEB'] as $team): ?>
                                <option value="<?= $team ?>" <?= $it['team'] === $team ? 'selected' : '' ?>><?= ep_team_label($team) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-7">
                        <label class="form-label small">Título</label>
                        <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($it['title']) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Orden</label>
                        <input type="number" name="sort_order" class="form-control" value="<?= (int) $it['sort_order'] ?>">
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" id="ia2" class="form-check-input" <?= !empty($it['is_active']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="ia2">Activo</label>
                        </div>
                    </div>
                    <div class="col-12 d-flex justify-content-between">
                        <a href="/dashboard/event_planner/admin/checklist.php" class="btn btn-outline-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-custom">Guardar</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <?php foreach (['EVENTOS', 'REDES', 'WEB'] as $team): ?>
            <h5 class="mt-4"><?= ep_team_badge($team) ?></h5>
            <table class="table table-sm align-middle">
                <tbody>
                    <?php foreach ($byTeam[$team] as $it): ?>
                        <tr class="<?= $it['is_active'] ? '' : 'text-muted' ?>">
                            <td><?= htmlspecialchars($it['title']) ?></td>
                            <td><?= $it['is_active'] ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">Inactivo</span>' ?></td>
                            <td class="text-end">
                                <a href="?edit=<?= (int) $it['id'] ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                <form method="post" class="d-inline" onsubmit="return confirm('¿Eliminar este ítem de checklist?');">
                                    <?= ep_csrf_field() ?>
                                    <input type="hidden" name="form_action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int) $it['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endforeach; ?>
    </div>

    <?php include __DIR__ . '/../../../assets/footer.php'; ?>
</body>

</html>
<?php $conn->close(); ?>
