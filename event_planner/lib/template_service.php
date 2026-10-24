<?php
// CRUD de plantillas de tareas y de checklist (admin, Fase 3). Los cambios aquí solo
// afectan a planes nuevos: los ya creados tienen sus propias filas en event_tasks.

require_once __DIR__ . '/graph.php';
require_once __DIR__ . '/activity_log.php';
require_once __DIR__ . '/plan_service.php'; // EventPlannerValidationException

function event_planner_list_templates(mysqli $conn): array
{
    $stmt = $conn->prepare("SELECT * FROM task_templates ORDER BY team, sort_order, title");
    $stmt->execute();
    $templates = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $stmt = $conn->prepare(
        "SELECT ttd.task_template_id, d.id AS dep_id, d.key AS dep_key, d.title AS dep_title
         FROM task_template_dependencies ttd JOIN task_templates d ON d.id = ttd.depends_on_template_id"
    );
    $stmt->execute();
    $depsByTemplate = [];
    foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $row) {
        $depsByTemplate[(int) $row['task_template_id']][] = ['id' => (int) $row['dep_id'], 'key' => $row['dep_key'], 'title' => $row['dep_title']];
    }
    $stmt->close();

    foreach ($templates as &$t) {
        $t['_deps'] = $depsByTemplate[(int) $t['id']] ?? [];
    }
    unset($t);

    return $templates;
}

function event_planner_get_template(mysqli $conn, int $id): ?array
{
    $stmt = $conn->prepare("SELECT * FROM task_templates WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $t = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$t) {
        return null;
    }

    $stmt = $conn->prepare("SELECT depends_on_template_id FROM task_template_dependencies WHERE task_template_id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $t['_dep_ids'] = array_map('intval', array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'depends_on_template_id'));
    $stmt->close();

    return $t;
}

// Valida que el grafo completo de plantillas activas no tenga ciclos. Lanza
// EventPlannerCycleException si los hay (y dice en qué consiste).
function event_planner_validate_template_graph(mysqli $conn): void
{
    $stmt = $conn->prepare("SELECT `key` FROM task_templates WHERE is_active = 1");
    $stmt->execute();
    $keys = array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'key');
    $stmt->close();

    $stmt = $conn->prepare(
        "SELECT t.key AS tkey, d.key AS dkey FROM task_template_dependencies ttd
         JOIN task_templates t ON t.id = ttd.task_template_id
         JOIN task_templates d ON d.id = ttd.depends_on_template_id
         WHERE t.is_active = 1 AND d.is_active = 1"
    );
    $stmt->execute();
    $deps = [];
    foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $row) {
        $deps[$row['tkey']][] = $row['dkey'];
    }
    $stmt->close();

    event_planner_topo_sort($keys, $deps);
}

// Crea o actualiza una plantilla + sus dependencias en una transacción; si el resultado
// tiene un ciclo, hace ROLLBACK y deja el error subir (EventPlannerCycleException).
function event_planner_save_template(mysqli $conn, ?int $id, array $data, array $dependsOnIds, int $actorId): int
{
    if (trim($data['key'] ?? '') === '' || trim($data['title'] ?? '') === '') {
        throw new EventPlannerValidationException('Faltan la clave o el título de la plantilla.');
    }

    $conn->begin_transaction();

    if ($id) {
        $stmt = $conn->prepare(
            "UPDATE task_templates SET `key`=?, title=?, description=?, team=?, default_slot=?, due_offset_days=?, start_offset_days=?, gate=?, confirms_event=?, sort_order=?, is_active=? WHERE id=?"
        );
        $stmt->bind_param(
            'sssssiisiiii',
            $data['key'], $data['title'], $data['description'], $data['team'], $data['default_slot'],
            $data['due_offset_days'], $data['start_offset_days'], $data['gate'], $data['confirms_event'],
            $data['sort_order'], $data['is_active'], $id
        );
        $stmt->execute();
        $stmt->close();
        $templateId = $id;
        event_planner_log_template_action($conn, $actorId, 'template_updated', $templateId);
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO task_templates (`key`, title, description, team, default_slot, due_offset_days, start_offset_days, gate, confirms_event, sort_order, is_active) VALUES (?,?,?,?,?,?,?,?,?,?,?)"
        );
        $stmt->bind_param(
            'sssssiisiii',
            $data['key'], $data['title'], $data['description'], $data['team'], $data['default_slot'],
            $data['due_offset_days'], $data['start_offset_days'], $data['gate'], $data['confirms_event'],
            $data['sort_order'], $data['is_active']
        );
        $stmt->execute();
        $templateId = (int) $conn->insert_id;
        $stmt->close();
        event_planner_log_template_action($conn, $actorId, 'template_created', $templateId);
    }

    $del = $conn->prepare("DELETE FROM task_template_dependencies WHERE task_template_id = ?");
    $del->bind_param('i', $templateId);
    $del->execute();
    $del->close();

    foreach (array_unique(array_map('intval', $dependsOnIds)) as $depId) {
        if ($depId === $templateId) {
            continue; // una plantilla no puede depender de sí misma
        }
        $ins = $conn->prepare("INSERT INTO task_template_dependencies (task_template_id, depends_on_template_id) VALUES (?, ?)");
        $ins->bind_param('ii', $templateId, $depId);
        $ins->execute();
        $ins->close();
    }

    try {
        event_planner_validate_template_graph($conn);
    } catch (EventPlannerCycleException $e) {
        $conn->rollback();
        throw $e;
    }

    $conn->commit();

    return $templateId;
}

function event_planner_set_template_active(mysqli $conn, int $id, bool $active): void
{
    $stmt = $conn->prepare("UPDATE task_templates SET is_active = ? WHERE id = ?");
    $flag = $active ? 1 : 0;
    $stmt->bind_param('ii', $flag, $id);
    $stmt->execute();
    $stmt->close();
}

function event_planner_log_template_action(mysqli $conn, int $actorId, string $action, int $templateId): void
{
    // Las plantillas no pertenecen a un plan; se registra con event_plan_id=0 no es posible
    // (FK), así que esto es un log aparte, más simple: error_log basta para auditoría ligera.
    error_log("EventPlanner admin: member {$actorId} -> {$action} (template {$templateId})");
}

// ---------------------------------------------------------------------------
// Checklist (sin dependencias; borrado real seguro, no afecta a planes ya creados)
// ---------------------------------------------------------------------------
function event_planner_list_checklist_templates(mysqli $conn): array
{
    $stmt = $conn->prepare("SELECT * FROM checklist_templates ORDER BY team, sort_order, title");
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $items;
}

function event_planner_save_checklist_template(mysqli $conn, ?int $id, array $data): int
{
    if (trim($data['title'] ?? '') === '') {
        throw new EventPlannerValidationException('Falta el título del ítem de checklist.');
    }

    if ($id) {
        $stmt = $conn->prepare("UPDATE checklist_templates SET team=?, title=?, sort_order=?, is_active=? WHERE id=?");
        $stmt->bind_param('ssiii', $data['team'], $data['title'], $data['sort_order'], $data['is_active'], $id);
        $stmt->execute();
        $stmt->close();
        return $id;
    }

    $stmt = $conn->prepare("INSERT INTO checklist_templates (team, title, sort_order, is_active) VALUES (?,?,?,?)");
    $stmt->bind_param('ssii', $data['team'], $data['title'], $data['sort_order'], $data['is_active']);
    $stmt->execute();
    $newId = (int) $conn->insert_id;
    $stmt->close();
    return $newId;
}

function event_planner_delete_checklist_template(mysqli $conn, int $id): void
{
    $stmt = $conn->prepare("DELETE FROM checklist_templates WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
}
