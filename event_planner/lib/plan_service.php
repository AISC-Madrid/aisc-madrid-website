<?php
// Servicio de dominio: crear plan desde plantilla, completar/skip/reasignar tareas,
// tareas personalizadas, cambio de fecha y transiciones de estado. Los endpoints (páginas
// del dashboard) comprueban sesión y permisos; estas funciones asumen un $actorId ya
// autorizado, igual que el resto del repo (p. ej. team_members_list.php).

require_once __DIR__ . '/clock.php';
require_once __DIR__ . '/teams.php';
require_once __DIR__ . '/graph.php';
require_once __DIR__ . '/activity_log.php';
require_once __DIR__ . '/recompute.php';
require_once __DIR__ . '/notifications.php';

class EventPlannerValidationException extends RuntimeException
{
}

function ep_fetch_member(mysqli $conn, int $memberId): ?array
{
    $stmt = $conn->prepare("SELECT id, full_name, mail, role, board, active FROM members WHERE id = ?");
    $stmt->bind_param('i', $memberId);
    $stmt->execute();
    $member = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $member ?: null;
}

// offsetDays ya dado, o se deriva de una fecha absoluta respecto al día del evento.
function event_planner_resolve_offset_or_date(DateTimeImmutable $eventDate, ?int $offsetDays, ?string $absoluteDate, bool $nullable = false): array
{
    if ($offsetDays !== null) {
        return [$offsetDays, event_planner_add_days($eventDate, $offsetDays)->format('Y-m-d')];
    }
    if ($absoluteDate !== null && $absoluteDate !== '') {
        $abs = new DateTimeImmutable($absoluteDate, event_planner_timezone());
        $diff = (int) $eventDate->diff($abs)->format('%r%a');
        return [$diff, $abs->format('Y-m-d')];
    }
    if ($nullable) {
        return [null, null];
    }
    throw new EventPlannerValidationException('Falta la fecha límite de la tarea.');
}

// ---------------------------------------------------------------------------
// Crear plan desde la plantilla
// ---------------------------------------------------------------------------
// $slotMembers: ['EV_CONTACTO'=>id, 'EV_APOYO'=>id, 'REDES_1'=>id, 'REDES_2'=>id, 'WEB'=>id]
// $includedTemplateKeys: keys de task_templates activas a incluir
// $assigneeOverrides: [templateKey => memberId] (cambios puntuales en el paso de revisión)
// $customTasks: [['key'=>tmpKey,'title'=>,'description'=>,'team'=>,'assignee_id'=>,
//   'due_offset_days'=>?int,'due_date'=>?string,'start_offset_days'=>?int,'start_date'=>?string,
//   'gate'=>,'depends_on'=>[keys]]]
function event_planner_create_plan(mysqli $conn, array $config, array $planData, array $slotMembers, array $includedTemplateKeys, array $assigneeOverrides, array $customTasks, int $actorId): int
{
    foreach (EVENT_PLANNER_SLOTS as $slot) {
        if (empty($slotMembers[$slot])) {
            throw new EventPlannerValidationException("Falta asignar el slot {$slot}.");
        }
        $team = EVENT_PLANNER_SLOT_TEAM[$slot];
        $candidates = array_column(event_planner_active_members_for_team($conn, $team), 'id');
        if (!in_array((int) $slotMembers[$slot], $candidates, true)) {
            throw new EventPlannerValidationException("El miembro elegido para {$slot} no es un miembro activo del equipo {$team}.");
        }
    }

    $eventDate = event_planner_event_date($planData['starts_at']);

    $stmt = $conn->prepare("SELECT * FROM task_templates WHERE is_active = 1");
    $stmt->execute();
    $templates = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $templatesByKey = [];
    foreach ($templates as $t) {
        $templatesByKey[$t['key']] = $t;
    }

    $stmt = $conn->prepare(
        "SELECT t.key AS tkey, d.key AS dkey FROM task_template_dependencies ttd
         JOIN task_templates t ON t.id = ttd.task_template_id
         JOIN task_templates d ON d.id = ttd.depends_on_template_id"
    );
    $stmt->execute();
    $depRows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $templateDeps = [];
    foreach ($depRows as $r) {
        $templateDeps[$r['tkey']][] = $r['dkey'];
    }

    $includedKeys = array_values(array_intersect($includedTemplateKeys, array_keys($templatesByKey)));
    $effectiveTemplateDeps = event_planner_resolve_effective_dependencies($templateDeps, $includedKeys);

    $customKeys = array_column($customTasks, 'key');
    $allNodeKeys = array_merge($includedKeys, $customKeys);
    $combinedDeps = $effectiveTemplateDeps;
    foreach ($customTasks as $ct) {
        foreach ($ct['depends_on'] ?? [] as $dep) {
            if (!in_array($dep, $allNodeKeys, true)) {
                throw new EventPlannerValidationException("Dependencia inválida en tarea personalizada: {$dep}");
            }
        }
        $combinedDeps[$ct['key']] = $ct['depends_on'] ?? [];
    }

    event_planner_topo_sort($allNodeKeys, $combinedDeps); // lanza EventPlannerCycleException si hay ciclo

    $conn->begin_transaction();

    $stmt = $conn->prepare(
        "INSERT INTO event_plans (title, description, speaker_name, speaker_company, speaker_contact, starts_at, duration_minutes, location, drive_folder_url, status, created_by)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft', ?)"
    );
    $stmt->bind_param(
        'ssssssissi',
        $planData['title'], $planData['description'], $planData['speaker_name'], $planData['speaker_company'],
        $planData['speaker_contact'], $planData['starts_at'], $planData['duration_minutes'], $planData['location'],
        $planData['drive_folder_url'], $actorId
    );
    $stmt->execute();
    $planId = (int) $conn->insert_id;
    $stmt->close();

    foreach ($slotMembers as $slot => $memberId) {
        $stmt = $conn->prepare("INSERT INTO event_plan_members (event_plan_id, slot, member_id) VALUES (?, ?, ?)");
        $memberIdInt = (int) $memberId;
        $stmt->bind_param('isi', $planId, $slot, $memberIdInt);
        $stmt->execute();
        $stmt->close();
    }

    $taskIdByKey = [];
    foreach ($includedKeys as $key) {
        $tpl = $templatesByKey[$key];
        $assigneeId = (int) ($assigneeOverrides[$key] ?? $slotMembers[$tpl['default_slot']]);
        $dueDate = event_planner_add_days($eventDate, (int) $tpl['due_offset_days'])->format('Y-m-d');
        $startDate = $tpl['start_offset_days'] !== null
            ? event_planner_add_days($eventDate, (int) $tpl['start_offset_days'])->format('Y-m-d')
            : null;

        $stmt = $conn->prepare(
            "INSERT INTO event_tasks (event_plan_id, template_id, `key`, title, description, team, assignee_id, due_offset_days, start_offset_days, due_date, start_date, gate, confirms_event, status, sort_order)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'blocked', ?)"
        );
        $stmt->bind_param(
            'iissssiiisssii',
            $planId, $tpl['id'], $key, $tpl['title'], $tpl['description'], $tpl['team'], $assigneeId,
            $tpl['due_offset_days'], $tpl['start_offset_days'], $dueDate, $startDate, $tpl['gate'],
            $tpl['confirms_event'], $tpl['sort_order']
        );
        $stmt->execute();
        $taskIdByKey[$key] = (int) $conn->insert_id;
        $stmt->close();
    }

    foreach ($customTasks as $ct) {
        [$dueOffset, $dueDate] = event_planner_resolve_offset_or_date($eventDate, $ct['due_offset_days'] ?? null, $ct['due_date'] ?? null);
        [$startOffset, $startDate] = event_planner_resolve_offset_or_date($eventDate, $ct['start_offset_days'] ?? null, $ct['start_date'] ?? null, true);
        $gate = $ct['gate'] ?? 'none';
        $assigneeId = (int) $ct['assignee_id'];

        $stmt = $conn->prepare(
            "INSERT INTO event_tasks (event_plan_id, template_id, `key`, title, description, team, assignee_id, due_offset_days, start_offset_days, due_date, start_date, gate, confirms_event, status, sort_order)
             VALUES (?, NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 'blocked', 999)"
        );
        $stmt->bind_param(
            'issssiiisss',
            $planId, $ct['key'], $ct['title'], $ct['description'] ?? null, $ct['team'], $assigneeId,
            $dueOffset, $startOffset, $dueDate, $startDate, $gate
        );
        $stmt->execute();
        $taskIdByKey[$ct['key']] = (int) $conn->insert_id;
        $stmt->close();
    }

    foreach ($combinedDeps as $key => $deps) {
        if (!isset($taskIdByKey[$key])) {
            continue;
        }
        foreach ($deps as $depKey) {
            if (!isset($taskIdByKey[$depKey])) {
                continue;
            }
            $stmt = $conn->prepare("INSERT INTO event_task_dependencies (task_id, depends_on_task_id) VALUES (?, ?)");
            $stmt->bind_param('ii', $taskIdByKey[$key], $taskIdByKey[$depKey]);
            $stmt->execute();
            $stmt->close();
        }
    }

    $stmt = $conn->prepare("SELECT team, title, sort_order FROM checklist_templates WHERE is_active = 1");
    $stmt->execute();
    $checklistItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    foreach ($checklistItems as $item) {
        $stmt = $conn->prepare("INSERT INTO event_checklist_items (event_plan_id, team, title, sort_order) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('issi', $planId, $item['team'], $item['title'], $item['sort_order']);
        $stmt->execute();
        $stmt->close();
    }

    event_planner_log($conn, $planId, $actorId, 'plan_created', ['title' => $planData['title']]);

    $conn->commit();

    ep_notify_plan_created($conn, $config, $planId);
    event_planner_recompute_plan($conn, $config, $planId);

    return $planId;
}

// ---------------------------------------------------------------------------
// Completar tarea
// ---------------------------------------------------------------------------
function event_planner_complete_task(mysqli $conn, array $config, int $taskId, int $actorId, ?string $notes): void
{
    $stmt = $conn->prepare("SELECT * FROM event_tasks WHERE id = ?");
    $stmt->bind_param('i', $taskId);
    $stmt->execute();
    $task = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$task) {
        throw new EventPlannerValidationException('Tarea no encontrada.');
    }
    if ($task['status'] !== 'active') {
        throw new EventPlannerValidationException('Solo se pueden completar tareas activas.');
    }

    $planId = (int) $task['event_plan_id'];
    $nowSql = event_planner_now()->format('Y-m-d H:i:s');

    $update = $conn->prepare("UPDATE event_tasks SET status = 'done', completed_at = ?, completed_by = ?, completion_notes = ? WHERE id = ? AND status = 'active'");
    $update->bind_param('sisi', $nowSql, $actorId, $notes, $taskId);
    $update->execute();
    $ok = $update->affected_rows === 1;
    $update->close();

    if (!$ok) {
        throw new EventPlannerValidationException('La tarea ya no estaba activa (puede que otra persona la completara).');
    }

    event_planner_log($conn, $planId, $actorId, 'task_completed', ['task_id' => $taskId, 'notes' => $notes], $taskId);

    if (!empty($task['confirms_event'])) {
        $plan = ep_fetch_plan($conn, $planId);
        if ($plan && $plan['status'] === 'draft') {
            event_planner_transition_plan_status($conn, $config, $planId, 'confirmed', $actorId, 'confirms_event task completed');
        }
    }

    event_planner_recompute_plan($conn, $config, $planId);
}

// ---------------------------------------------------------------------------
// Marcar "No aplica" (junta/admin)
// ---------------------------------------------------------------------------
function event_planner_skip_task(mysqli $conn, array $config, int $taskId, int $actorId): void
{
    $stmt = $conn->prepare("SELECT * FROM event_tasks WHERE id = ?");
    $stmt->bind_param('i', $taskId);
    $stmt->execute();
    $task = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$task) {
        throw new EventPlannerValidationException('Tarea no encontrada.');
    }
    if ($task['status'] === 'done') {
        throw new EventPlannerValidationException('Una tarea completada no se puede marcar como No aplica.');
    }
    if ($task['status'] === 'skipped') {
        return;
    }

    $planId = (int) $task['event_plan_id'];
    $nowSql = event_planner_now()->format('Y-m-d H:i:s');

    $update = $conn->prepare("UPDATE event_tasks SET status = 'skipped', skipped_at = ?, skipped_by = ? WHERE id = ? AND status IN ('blocked','active')");
    $update->bind_param('sii', $nowSql, $actorId, $taskId);
    $update->execute();
    $ok = $update->affected_rows === 1;
    $update->close();

    if (!$ok) {
        throw new EventPlannerValidationException('No se pudo marcar como No aplica (conflicto concurrente).');
    }

    event_planner_log($conn, $planId, $actorId, 'task_skipped', ['task_id' => $taskId], $taskId);
    event_planner_recompute_plan($conn, $config, $planId);
}

// ---------------------------------------------------------------------------
// Reasignar tarea (junta/admin)
// ---------------------------------------------------------------------------
function event_planner_reassign_task(mysqli $conn, array $config, int $taskId, int $newMemberId, int $actorId): void
{
    $stmt = $conn->prepare("SELECT * FROM event_tasks WHERE id = ?");
    $stmt->bind_param('i', $taskId);
    $stmt->execute();
    $task = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$task) {
        throw new EventPlannerValidationException('Tarea no encontrada.');
    }
    if ($task['status'] === 'done') {
        throw new EventPlannerValidationException('No se puede reasignar una tarea completada.');
    }

    $oldMemberId = (int) $task['assignee_id'];
    if ($oldMemberId === $newMemberId) {
        return;
    }
    if (!event_planner_member_is_active($conn, $newMemberId)) {
        throw new EventPlannerValidationException('El nuevo asignado no es un miembro activo.');
    }

    $planId = (int) $task['event_plan_id'];
    $update = $conn->prepare("UPDATE event_tasks SET assignee_id = ? WHERE id = ?");
    $update->bind_param('ii', $newMemberId, $taskId);
    $update->execute();
    $update->close();

    event_planner_log($conn, $planId, $actorId, 'task_reassigned', ['task_id' => $taskId, 'from' => $oldMemberId, 'to' => $newMemberId], $taskId);

    $fromMember = ep_fetch_member($conn, $oldMemberId);
    $toMember = ep_fetch_member($conn, $newMemberId);
    $task['assignee_id'] = $newMemberId;
    if ($fromMember && $toMember) {
        ep_notify_task_reassigned($conn, $config, $planId, $task, $fromMember, $toMember);
    }
}

// ---------------------------------------------------------------------------
// Añadir tarea personalizada tras la creación (junta/admin)
// ---------------------------------------------------------------------------
function event_planner_add_custom_task(mysqli $conn, array $config, int $planId, array $data, int $actorId): int
{
    $plan = ep_fetch_plan($conn, $planId);
    if (!$plan) {
        throw new EventPlannerValidationException('Plan no encontrado.');
    }

    $eventDate = event_planner_event_date($plan['starts_at']);
    [$dueOffset, $dueDate] = event_planner_resolve_offset_or_date($eventDate, $data['due_offset_days'] ?? null, $data['due_date'] ?? null);
    [$startOffset, $startDate] = event_planner_resolve_offset_or_date($eventDate, $data['start_offset_days'] ?? null, $data['start_date'] ?? null, true);
    $gate = $data['gate'] ?? 'none';
    $assigneeId = (int) $data['assignee_id'];

    if (!event_planner_member_is_active($conn, $assigneeId)) {
        throw new EventPlannerValidationException('El asignado no es un miembro activo.');
    }

    $key = 'custom_' . bin2hex(random_bytes(4));

    $conn->begin_transaction();

    $stmt = $conn->prepare(
        "INSERT INTO event_tasks (event_plan_id, template_id, `key`, title, description, team, assignee_id, due_offset_days, start_offset_days, due_date, start_date, gate, confirms_event, status, sort_order)
         VALUES (?, NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 'blocked', 999)"
    );
    $stmt->bind_param(
        'issssiiisss',
        $planId, $key, $data['title'], $data['description'] ?? null, $data['team'], $assigneeId,
        $dueOffset, $startOffset, $dueDate, $startDate, $gate
    );
    $stmt->execute();
    $taskId = (int) $conn->insert_id;
    $stmt->close();

    $dependsOn = $data['depends_on'] ?? [];
    if (!empty($dependsOn)) {
        $placeholders = implode(',', array_fill(0, count($dependsOn), '?'));
        $stmt = $conn->prepare("SELECT id FROM event_tasks WHERE event_plan_id = ? AND id IN ($placeholders)");
        $types = 'i' . str_repeat('i', count($dependsOn));
        $params = array_merge([$planId], array_map('intval', $dependsOn));
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $validDeps = array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'id');
        $stmt->close();

        foreach ($validDeps as $depId) {
            $stmt = $conn->prepare("INSERT INTO event_task_dependencies (task_id, depends_on_task_id) VALUES (?, ?)");
            $stmt->bind_param('ii', $taskId, $depId);
            $stmt->execute();
            $stmt->close();
        }
    }

    event_planner_log($conn, $planId, $actorId, 'custom_task_added', ['task_id' => $taskId, 'title' => $data['title']], $taskId);

    $conn->commit();

    event_planner_recompute_plan($conn, $config, $planId);

    return $taskId;
}

// ---------------------------------------------------------------------------
// Cambiar fecha/hora del evento (recalcula fechas de tareas no completadas)
// ---------------------------------------------------------------------------
function event_planner_change_event_datetime(mysqli $conn, array $config, int $planId, string $newStartsAtUtc, int $newDurationMinutes, int $actorId): void
{
    $plan = ep_fetch_plan($conn, $planId);
    if (!$plan) {
        throw new EventPlannerValidationException('Plan no encontrado.');
    }
    if (in_array($plan['status'], ['closed', 'cancelled'], true)) {
        throw new EventPlannerValidationException('No se puede cambiar la fecha de un evento cerrado o cancelado.');
    }

    $newEventDate = event_planner_event_date($newStartsAtUtc);

    $stmt = $conn->prepare("SELECT * FROM event_tasks WHERE event_plan_id = ? AND status NOT IN ('done','skipped')");
    $stmt->bind_param('i', $planId);
    $stmt->execute();
    $openTasks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $conn->begin_transaction();

    $changesByMember = [];
    foreach ($openTasks as $task) {
        $newDue = event_planner_add_days($newEventDate, (int) $task['due_offset_days'])->format('Y-m-d');
        $newStart = $task['start_offset_days'] !== null
            ? event_planner_add_days($newEventDate, (int) $task['start_offset_days'])->format('Y-m-d')
            : null;

        if ($newDue !== $task['due_date'] || $newStart !== $task['start_date']) {
            $update = $conn->prepare("UPDATE event_tasks SET due_date = ?, start_date = ? WHERE id = ?");
            $update->bind_param('ssi', $newDue, $newStart, $task['id']);
            $update->execute();
            $update->close();

            if ($newDue !== $task['due_date']) {
                $changesByMember[(int) $task['assignee_id']]['changes'][] = [
                    'title' => $task['title'], 'old_due' => $task['due_date'], 'new_due' => $newDue,
                ];
            }
        }
    }

    $update = $conn->prepare("UPDATE event_plans SET starts_at = ?, duration_minutes = ? WHERE id = ?");
    $update->bind_param('sii', $newStartsAtUtc, $newDurationMinutes, $planId);
    $update->execute();
    $update->close();

    event_planner_log($conn, $planId, $actorId, 'event_datetime_changed', [
        'old_starts_at' => $plan['starts_at'], 'new_starts_at' => $newStartsAtUtc,
    ]);

    $conn->commit();

    if (!empty($changesByMember)) {
        $ids = array_keys($changesByMember);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $conn->prepare("SELECT id, mail FROM members WHERE id IN ($placeholders)");
        $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
        $stmt->execute();
        $mails = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        foreach ($mails as $m) {
            $changesByMember[(int) $m['id']]['mail'] = $m['mail'];
        }
        ep_notify_event_datetime_changed($conn, $config, $planId, $changesByMember, $newStartsAtUtc);
    }

    event_planner_recompute_plan($conn, $config, $planId);
}

// ---------------------------------------------------------------------------
// Transiciones de estado del plan
// ---------------------------------------------------------------------------
function event_planner_transition_plan_status(mysqli $conn, array $config, int $planId, string $newStatus, ?int $actorId, string $reason = ''): void
{
    $allowedTransitions = [
        'draft' => ['confirmed', 'cancelled'],
        'confirmed' => ['held', 'cancelled'],
        'held' => ['closed', 'cancelled'],
        'closed' => [],
        'cancelled' => [],
    ];

    $plan = ep_fetch_plan($conn, $planId);
    if (!$plan) {
        throw new EventPlannerValidationException('Plan no encontrado.');
    }
    if ($plan['status'] === $newStatus) {
        return;
    }
    if (!in_array($newStatus, $allowedTransitions[$plan['status']] ?? [], true)) {
        throw new EventPlannerValidationException("Transición no permitida: {$plan['status']} -> {$newStatus}");
    }

    $column = match ($newStatus) {
        'confirmed' => 'confirmed_at',
        'held' => 'held_at',
        'closed' => 'closed_at',
        'cancelled' => 'cancelled_at',
    };

    $nowSql = event_planner_now()->format('Y-m-d H:i:s');
    $oldStatus = $plan['status'];
    $stmt = $conn->prepare("UPDATE event_plans SET status = ?, {$column} = ? WHERE id = ? AND status = ?");
    $stmt->bind_param('ssis', $newStatus, $nowSql, $planId, $oldStatus);
    $stmt->execute();
    $ok = $stmt->affected_rows === 1;
    $stmt->close();

    if (!$ok) {
        throw new EventPlannerValidationException('No se pudo cambiar el estado (conflicto concurrente).');
    }

    event_planner_log($conn, $planId, $actorId, 'plan_status_changed', ['from' => $oldStatus, 'to' => $newStatus, 'reason' => $reason]);

    if ($newStatus === 'cancelled') {
        ep_notify_plan_cancelled($conn, $config, $planId, $plan);
        return;
    }

    event_planner_recompute_plan($conn, $config, $planId);
}

function event_planner_cancel_plan(mysqli $conn, array $config, int $planId, int $actorId): void
{
    event_planner_transition_plan_status($conn, $config, $planId, 'cancelled', $actorId, 'cancelled_by_board');
}

function event_planner_update_plan_details(mysqli $conn, int $planId, array $data, int $actorId): void
{
    $plan = ep_fetch_plan($conn, $planId);
    if (!$plan) {
        throw new EventPlannerValidationException('Plan no encontrado.');
    }
    if (trim($data['title'] ?? '') === '') {
        throw new EventPlannerValidationException('El título no puede estar vacío.');
    }

    $stmt = $conn->prepare(
        "UPDATE event_plans SET title = ?, description = ?, speaker_name = ?, speaker_company = ?, speaker_contact = ?, location = ?, drive_folder_url = ? WHERE id = ?"
    );
    $title = trim($data['title']);
    $description = $data['description'] ?: null;
    $speakerName = trim($data['speaker_name'] ?? $plan['speaker_name']);
    $speakerCompany = $data['speaker_company'] ?: null;
    $speakerContact = $data['speaker_contact'] ?: null;
    $location = $data['location'] ?: null;
    $driveFolderUrl = $data['drive_folder_url'] ?: null;
    $stmt->bind_param('sssssssi', $title, $description, $speakerName, $speakerCompany, $speakerContact, $location, $driveFolderUrl, $planId);
    $stmt->execute();
    $stmt->close();

    event_planner_log($conn, $planId, $actorId, 'plan_details_updated', []);
}

function event_planner_link_public_event(mysqli $conn, int $planId, int $publicEventId, int $actorId): void
{
    $stmt = $conn->prepare("UPDATE event_plans SET public_event_id = ? WHERE id = ?");
    $stmt->bind_param('ii', $publicEventId, $planId);
    $stmt->execute();
    $stmt->close();
    event_planner_log($conn, $planId, $actorId, 'public_event_linked', ['public_event_id' => $publicEventId]);
}
