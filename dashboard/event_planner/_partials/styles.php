<style>
    :root {
        --ep-primary: var(--primary, #EB178E);
        --ep-secondary: var(--secondary, #20CCF1);
        --ep-redes: #8b5cf6;
        --ep-radius: 14px;
    }

    /* Texto siempre legible encima de bg-light, aunque styles3.css toque body { color } */
    body.bg-light { color: #212529; }

    /* ---------- Equipos ---------- */
    .ep-team-EVENTOS { background-color: var(--ep-primary); color: #fff; }
    .ep-team-REDES { background-color: var(--ep-redes); color: #fff; }
    .ep-team-WEB { background-color: var(--ep-secondary); color: #fff; }

    .ep-team-soft-EVENTOS { background-color: rgba(235, 23, 142, .08); color: #a3116a; }
    .ep-team-soft-REDES { background-color: rgba(139, 92, 246, .1); color: #6d33e0; }
    .ep-team-soft-WEB { background-color: rgba(32, 204, 241, .12); color: #0f8aa3; }

    /* ---------- Estados de tarea ---------- */
    .ep-status-blocked { background-color: #6c757d; color: #fff; }
    .ep-status-active { background-color: #0d6efd; color: #fff; }
    .ep-status-done { background-color: #198754; color: #fff; }
    .ep-status-skipped { background-color: #ced4da; color: #495057; text-decoration: line-through; }

    .badge.ep-status-blocked,
    .badge.ep-status-active,
    .badge.ep-status-done,
    .badge.ep-status-skipped,
    .badge.ep-team-EVENTOS,
    .badge.ep-team-REDES,
    .badge.ep-team-WEB {
        font-weight: 600;
        letter-spacing: .01em;
        border-radius: 999px;
        padding: .4em .75em;
    }

    /* ---------- Estados de plan ---------- */
    .ep-plan-draft { background-color: #6c757d; color: #fff; }
    .ep-plan-confirmed { background-color: var(--ep-primary); color: #fff; }
    .ep-plan-held { background-color: var(--ep-secondary); color: #04323a; }
    .ep-plan-closed { background-color: #198754; color: #fff; }
    .ep-plan-cancelled { background-color: #dc3545; color: #fff; }

    /* ---------- Fechas límite ---------- */
    .ep-due-overdue { color: #dc3545; font-weight: 700; }
    .ep-due-soon { color: #b45309; font-weight: 700; }
    .ep-due-pill { display: inline-flex; align-items: center; gap: .3em; font-size: .8rem; padding: .15em .6em; border-radius: 999px; background: #f1f3f5; }
    .ep-due-pill.ep-due-overdue { background: #fde8ea; }
    .ep-due-pill.ep-due-soon { background: #fdf1dc; }

    /* ---------- Tarjetas de tarea ---------- */
    .ep-task-card { border: 1px solid #eceef1; border-left: 4px solid #dee2e6; border-radius: 10px; cursor: pointer; transition: box-shadow .15s ease, transform .15s ease; }
    .ep-task-card:hover { box-shadow: 0 4px 14px rgba(20, 20, 43, .08); transform: translateY(-1px); }
    .ep-task-card.ep-team-border-EVENTOS { border-left-color: var(--ep-primary); }
    .ep-task-card.ep-team-border-REDES { border-left-color: var(--ep-redes); }
    .ep-task-card.ep-team-border-WEB { border-left-color: var(--ep-secondary); }

    /* ---------- Tableros ---------- */
    .ep-kanban-col { background-color: #f6f7f9; border-radius: var(--ep-radius); padding: 12px; min-height: 140px; }
    .ep-kanban-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: .6rem; padding: 0 .25rem; }
    .ep-kanban-count { background: #fff; border-radius: 999px; padding: .1em .65em; font-size: .78rem; font-weight: 700; color: #495057; box-shadow: 0 0 0 1px #e9ecef inset; }

    .ep-progress-thin { height: 8px; border-radius: 999px; background-color: #eef0f2; }
    .ep-progress-thin .progress-bar { background: linear-gradient(90deg, var(--ep-secondary), #198754); border-radius: 999px; }

    /* ---------- Tarjetas de evento (listado) ---------- */
    .ep-event-card { border: 1px solid #eceef1; border-radius: var(--ep-radius); transition: box-shadow .15s ease, transform .15s ease; overflow: hidden; }
    .ep-event-card:hover { box-shadow: 0 10px 28px rgba(20, 20, 43, .1); transform: translateY(-2px); }
    .ep-event-card .card-body { padding: 1.25rem; }
    .ep-event-meta { color: #6c757d; font-size: .86rem; }
    .ep-event-meta .bi { width: 1.1em; display: inline-block; }

    /* ---------- Estado vacío ---------- */
    .ep-empty-state { text-align: center; padding: 3.5rem 1.5rem; color: #6c757d; border: 1px dashed #dee2e6; border-radius: var(--ep-radius); background: #fcfcfd; }
    .ep-empty-state .bi { font-size: 2.6rem; color: #ced4da; display: block; margin-bottom: .75rem; }

    /* ---------- Asistente: stepper ---------- */
    .ep-stepper { display: flex; align-items: flex-start; margin-bottom: 2rem; position: relative; }
    .ep-stepper::before { content: ""; position: absolute; top: 18px; left: 10%; right: 10%; height: 2px; background: #e9ecef; z-index: 0; }
    .ep-step { flex: 1; text-align: center; position: relative; z-index: 1; }
    .ep-step-circle { width: 36px; height: 36px; line-height: 36px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; background: #e9ecef; color: #868e96; border: 3px solid #fff; }
    .ep-step-label { display: block; margin-top: .4rem; font-size: .82rem; color: #868e96; font-weight: 600; }
    .ep-step.is-active .ep-step-circle { background: var(--ep-primary); color: #fff; }
    .ep-step.is-active .ep-step-label { color: #212529; }
    .ep-step.is-done .ep-step-circle { background: #198754; color: #fff; }
    .ep-step.is-done .ep-step-label { color: #495057; }

    /* ---------- Línea temporal ---------- */
    .ep-timeline-legend { display: flex; flex-wrap: wrap; gap: 1rem; font-size: .8rem; color: #495057; margin-bottom: 1rem; }
    .ep-timeline-legend .dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; margin-right: .35em; vertical-align: middle; }
    .ep-timeline-row { position: relative; height: 36px; }
    .ep-timeline-track { position: absolute; top: 15px; left: 0; right: 0; height: 6px; background: #eef0f2; border-radius: 3px; }
    .ep-timeline-dot { position: absolute; top: 9px; width: 16px; height: 16px; border-radius: 50%; border: 2px solid #fff; transform: translateX(-50%); box-shadow: 0 0 0 1px rgba(0, 0, 0, .08); }
    .ep-timeline-bar { position: absolute; top: 13px; height: 8px; border-radius: 4px; opacity: .9; }
    .ep-timeline-marker { position: absolute; top: -2px; bottom: -2px; width: 2px; background: #212529; opacity: .35; }
    .ep-timeline-marker.ep-timeline-event { background: var(--ep-primary); opacity: .9; width: 2px; }

    /* ---------- Checklist ---------- */
    .ep-checklist-item { border-radius: 10px; padding: .5rem .65rem; transition: background-color .15s ease; }
    .ep-checklist-item:hover { background-color: #f6f7f9; }

    /* ---------- Actividad ---------- */
    .ep-activity-item { display: flex; gap: .75rem; padding: .55rem .25rem; border-bottom: 1px solid #f1f3f5; }
    .ep-activity-item:last-child { border-bottom: none; }
    .ep-activity-icon { width: 28px; height: 28px; min-width: 28px; border-radius: 50%; background: #f1f3f5; color: #6c757d; display: flex; align-items: center; justify-content: center; font-size: .8rem; }

    .btn-custom { background-color: var(--ep-primary) !important; border-color: var(--ep-primary) !important; color: #fff; font-weight: 600; }
    .btn-custom:hover { filter: brightness(92%); color: #fff; }

    @media (max-width: 576px) {
        .ep-hide-mobile { display: none !important; }
        .ep-stepper .ep-step-label { font-size: .72rem; }
    }
</style>
