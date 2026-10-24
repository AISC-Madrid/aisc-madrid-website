<?php
// Modales de acciones de la junta/admin. Requiere $plan, $planId, $tasks, $membersByTeamJs
// en el scope de quien incluye (plan.php).
$eventDateLocal = event_planner_event_date($plan['starts_at']);
$eventTimeLocal = (new DateTimeImmutable($plan['starts_at'], new DateTimeZone('UTC')))->setTimezone(event_planner_timezone());
?>
<!-- Editar datos -->
<div class="modal fade" id="epEditModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" action="/dashboard/event_planner/update_plan_details.php" class="modal-content">
            <?= ep_csrf_field() ?>
            <input type="hidden" name="plan_id" value="<?= $planId ?>">
            <div class="modal-header"><h5 class="modal-title">Editar datos del evento</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-2"><label class="form-label small">Título</label><input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($plan['title']) ?>"></div>
                <div class="mb-2"><label class="form-label small">Descripción</label><textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($plan['description'] ?? '') ?></textarea></div>
                <div class="mb-2"><label class="form-label small">Ponente</label><input type="text" name="speaker_name" class="form-control" required value="<?= htmlspecialchars($plan['speaker_name']) ?>"></div>
                <div class="mb-2"><label class="form-label small">Empresa</label><input type="text" name="speaker_company" class="form-control" value="<?= htmlspecialchars($plan['speaker_company'] ?? '') ?>"></div>
                <div class="mb-2"><label class="form-label small">Contacto</label><input type="text" name="speaker_contact" class="form-control" value="<?= htmlspecialchars($plan['speaker_contact'] ?? '') ?>"></div>
                <div class="mb-2"><label class="form-label small">Sala / lugar</label><input type="text" name="location" class="form-control" value="<?= htmlspecialchars($plan['location'] ?? '') ?>"></div>
                <div class="mb-2"><label class="form-label small">Carpeta de Drive</label><input type="url" name="drive_folder_url" class="form-control" value="<?= htmlspecialchars($plan['drive_folder_url'] ?? '') ?>"></div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-custom">Guardar</button></div>
        </form>
    </div>
</div>

<!-- Cambiar fecha/hora -->
<div class="modal fade" id="epDateModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" action="/dashboard/event_planner/change_datetime.php" class="modal-content" onsubmit="return confirm('Esto recalculará las fechas de las tareas abiertas y avisará a los asignados afectados. ¿Continuar?');">
            <?= ep_csrf_field() ?>
            <input type="hidden" name="plan_id" value="<?= $planId ?>">
            <div class="modal-header"><h5 class="modal-title">Cambiar fecha/hora del evento</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-2"><label class="form-label small">Fecha</label><input type="date" name="event_date" class="form-control" required value="<?= $eventDateLocal->format('Y-m-d') ?>"></div>
                <div class="mb-2"><label class="form-label small">Hora</label><input type="time" name="event_time" class="form-control" required value="<?= $eventTimeLocal->format('H:i') ?>"></div>
                <div class="mb-2"><label class="form-label small">Duración (min)</label><input type="number" name="duration_minutes" class="form-control" required min="1" value="<?= (int) $plan['duration_minutes'] ?>"></div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-custom">Guardar y recalcular</button></div>
        </form>
    </div>
</div>

<!-- Enlazar con evento público -->
<div class="modal fade" id="epLinkModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" action="/dashboard/event_planner/link_public_event.php" class="modal-content">
            <?= ep_csrf_field() ?>
            <input type="hidden" name="plan_id" value="<?= $planId ?>">
            <div class="modal-header"><h5 class="modal-title">Enlazar con evento público</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <label class="form-label small">ID del evento público (tabla <code>events</code>)</label>
                <input type="number" name="public_event_id" class="form-control" required value="<?= (int) ($plan['public_event_id'] ?? 0) ?>">
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-custom">Enlazar</button></div>
        </form>
    </div>
</div>

<!-- Añadir tarea personalizada -->
<div class="modal fade" id="epAddTaskModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" action="/dashboard/event_planner/add_custom_task.php" class="modal-content">
            <?= ep_csrf_field() ?>
            <input type="hidden" name="plan_id" value="<?= $planId ?>">
            <div class="modal-header"><h5 class="modal-title">Añadir tarea personalizada</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-2"><label class="form-label small">Título</label><input type="text" name="title" class="form-control" required></div>
                <div class="mb-2"><label class="form-label small">Descripción</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                <div class="row">
                    <div class="col-6 mb-2">
                        <label class="form-label small">Equipo</label>
                        <select name="team" id="epAddTaskTeam" class="form-select">
                            <option value="EVENTOS">Eventos</option>
                            <option value="REDES">Redes</option>
                            <option value="WEB">Web</option>
                        </select>
                    </div>
                    <div class="col-6 mb-2">
                        <label class="form-label small">Asignado</label>
                        <select name="assignee_id" id="epAddTaskAssignee" class="form-select"></select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6 mb-2"><label class="form-label small">Límite (días desde el evento)</label><input type="number" name="due_offset_days" class="form-control" value="0"></div>
                    <div class="col-6 mb-2"><label class="form-label small">Inicio (opcional, días)</label><input type="number" name="start_offset_days" class="form-control" placeholder="vacío = sin inicio fijo"></div>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Gate</label>
                    <select name="gate" class="form-select">
                        <option value="none">Sin gate</option>
                        <option value="confirmed">Confirmado</option>
                        <option value="held">Celebrado</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Depende de</label>
                    <select name="depends_on[]" class="form-select" multiple size="5">
                        <?php foreach ($tasks as $existingTask): ?>
                            <option value="<?= (int) $existingTask['id'] ?>"><?= ep_team_label($existingTask['team']) ?>: <?= htmlspecialchars($existingTask['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-custom">Añadir</button></div>
        </form>
    </div>
</div>

<script>
    document.getElementById('epAddTaskTeam').addEventListener('change', epFillAddTaskAssignees);
    document.getElementById('epAddTaskModal').addEventListener('shown.bs.modal', epFillAddTaskAssignees);
    function epFillAddTaskAssignees() {
        var team = document.getElementById('epAddTaskTeam').value;
        var select = document.getElementById('epAddTaskAssignee');
        select.innerHTML = '';
        (EP_MEMBERS_BY_TEAM[team] || []).forEach(function (m) {
            var opt = document.createElement('option');
            opt.value = m.id;
            opt.textContent = m.full_name;
            select.appendChild(opt);
        });
    }
</script>
