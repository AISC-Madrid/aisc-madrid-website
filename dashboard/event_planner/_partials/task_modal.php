<?php // Modal único reutilizado por todas las tarjetas de tarea. Rellenado por JS (ver plan.php). ?>
<div class="modal fade" id="epTaskModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="epModalTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>
                    <span id="epModalTeamBadge"></span>
                    <span id="epModalStatusBadge"></span>
                </p>
                <p id="epModalDescription" class="text-muted"></p>
                <p class="mb-1"><strong>Asignado a:</strong> <span id="epModalAssignee"></span></p>
                <p class="mb-1"><strong>Fecha límite:</strong> <span id="epModalDue"></span></p>
                <p class="mb-1"><strong>Fecha de inicio:</strong> <span id="epModalStart"></span></p>
                <p class="mb-1"><strong>Depende de:</strong></p>
                <ul id="epModalDeps" class="small"></ul>
                <p id="epModalNotes" class="small bg-light p-2 rounded"></p>
                <p id="epModalCompletedBy" class="small text-success"></p>

                <hr>

                <form method="post" action="/dashboard/event_planner/complete_task.php" id="epFormComplete" class="mb-2">
                    <?= ep_csrf_field() ?>
                    <input type="hidden" name="plan_id" value="<?= $planId ?>">
                    <input type="hidden" name="task_id" value="">
                    <label class="form-label small">Notas (opcional)</label>
                    <textarea name="notes" class="form-control form-control-sm mb-2" rows="2" placeholder="Ej: Aula 2.3.A03 reservada"></textarea>
                    <button type="submit" class="btn btn-success btn-sm w-100"><i class="bi bi-check-lg"></i> Completar</button>
                </form>

                <?php if ($isBoardOrAdmin): ?>
                    <form method="post" action="/dashboard/event_planner/reassign_task.php" id="epFormReassign" class="mb-2 d-flex gap-2">
                        <?= ep_csrf_field() ?>
                        <input type="hidden" name="plan_id" value="<?= $planId ?>">
                        <input type="hidden" name="task_id" value="">
                        <select name="member_id" id="epReassignSelect" class="form-select form-select-sm"></select>
                        <button type="submit" class="btn btn-outline-primary btn-sm">Reasignar</button>
                    </form>

                    <form method="post" action="/dashboard/event_planner/skip_task.php" id="epFormSkip" onsubmit="return confirm('¿Marcar esta tarea como No aplica?');">
                        <?= ep_csrf_field() ?>
                        <input type="hidden" name="plan_id" value="<?= $planId ?>">
                        <input type="hidden" name="task_id" value="">
                        <button type="submit" class="btn btn-outline-secondary btn-sm w-100">No aplica</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
