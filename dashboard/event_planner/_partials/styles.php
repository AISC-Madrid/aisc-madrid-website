<style>
    .ep-team-EVENTOS { background-color: #EB178E; color: #fff; }
    .ep-team-REDES { background-color: #8b5cf6; color: #fff; }
    .ep-team-WEB { background-color: #20CCF1; color: #fff; }

    .ep-status-blocked { background-color: #6c757d; color: #fff; }
    .ep-status-active { background-color: #0d6efd; color: #fff; }
    .ep-status-done { background-color: #198754; color: #fff; }
    .ep-status-skipped { background-color: #adb5bd; color: #fff; text-decoration: line-through; }

    .ep-due-overdue { color: #dc3545; font-weight: 600; }
    .ep-due-soon { color: #b45309; font-weight: 600; }

    .ep-task-card { border-left: 4px solid #dee2e6; cursor: pointer; }
    .ep-task-card:hover { background-color: #f8f9fa; }
    .ep-task-card.ep-team-border-EVENTOS { border-left-color: #EB178E; }
    .ep-task-card.ep-team-border-REDES { border-left-color: #8b5cf6; }
    .ep-task-card.ep-team-border-WEB { border-left-color: #20CCF1; }

    .ep-kanban-col { background-color: #f8f9fa; border-radius: 8px; padding: 10px; min-height: 120px; }
    .ep-progress-thin { height: 8px; }

    .ep-timeline-row { position: relative; height: 34px; }
    .ep-timeline-track { position: absolute; top: 14px; left: 0; right: 0; height: 6px; background: #e9ecef; border-radius: 3px; }
    .ep-timeline-dot { position: absolute; top: 8px; width: 16px; height: 16px; border-radius: 50%; border: 2px solid #fff; transform: translateX(-50%); }
    .ep-timeline-bar { position: absolute; top: 12px; height: 8px; border-radius: 4px; opacity: .85; }
    .ep-timeline-marker { position: absolute; top: 0; bottom: 0; width: 2px; background: #212529; }

    @media (max-width: 576px) {
        .ep-hide-mobile { display: none !important; }
    }
</style>
