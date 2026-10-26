-- Planificador de eventos: tablas nuevas para el tracker de tareas por evento (sustituye al Excel).
-- No modifica la tabla `events` (pública) ni `members`; solo añade FKs opcionales hacia ellas.
-- Re-ejecutable: todo usa CREATE TABLE IF NOT EXISTS.
-- Asume members.id / events.id como INT AUTO_INCREMENT (mismo patrón que 03-contacts.sql y 04-events-company.sql).

SET NAMES utf8mb4;

-- ---------------------------------------------------------------------------
-- Planes de evento
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS event_plans (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  description TEXT NULL,
  speaker_name VARCHAR(255) NOT NULL,
  speaker_company VARCHAR(255) NULL,
  speaker_contact VARCHAR(255) NULL,
  starts_at DATETIME NOT NULL COMMENT 'UTC, igual que events.start_datetime',
  duration_minutes INT NOT NULL DEFAULT 60,
  location VARCHAR(255) NULL,
  drive_folder_url VARCHAR(500) NULL,
  status ENUM('draft','confirmed','held','closed','cancelled') NOT NULL DEFAULT 'draft',
  public_event_id INT NULL,
  created_by INT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  confirmed_at DATETIME NULL,
  held_at DATETIME NULL,
  closed_at DATETIME NULL,
  cancelled_at DATETIME NULL,
  INDEX idx_event_plans_status (status),
  CONSTRAINT fk_event_plans_public_event FOREIGN KEY (public_event_id) REFERENCES events (id) ON DELETE SET NULL,
  CONSTRAINT fk_event_plans_created_by FOREIGN KEY (created_by) REFERENCES members (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Slots de personas asignadas al evento (5 por plan)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS event_plan_members (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event_plan_id INT NOT NULL,
  slot ENUM('EV_CONTACTO','EV_APOYO','REDES_1','REDES_2','WEB') NOT NULL,
  member_id INT NOT NULL,
  UNIQUE KEY uq_event_plan_slot (event_plan_id, slot),
  CONSTRAINT fk_epm_plan FOREIGN KEY (event_plan_id) REFERENCES event_plans (id) ON DELETE CASCADE,
  CONSTRAINT fk_epm_member FOREIGN KEY (member_id) REFERENCES members (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Plantillas de tareas (editables desde el admin; copiadas al crear un plan)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS task_templates (
  id INT AUTO_INCREMENT PRIMARY KEY,
  `key` VARCHAR(64) NOT NULL UNIQUE,
  title VARCHAR(255) NOT NULL,
  description TEXT NULL,
  team ENUM('EVENTOS','REDES','WEB') NOT NULL,
  default_slot ENUM('EV_CONTACTO','EV_APOYO','REDES_1','REDES_2','WEB') NOT NULL,
  due_offset_days INT NOT NULL,
  start_offset_days INT NULL,
  gate ENUM('none','confirmed','held') NOT NULL DEFAULT 'none',
  confirms_event TINYINT(1) NOT NULL DEFAULT 0,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS task_template_dependencies (
  task_template_id INT NOT NULL,
  depends_on_template_id INT NOT NULL,
  PRIMARY KEY (task_template_id, depends_on_template_id),
  CONSTRAINT fk_ttd_task FOREIGN KEY (task_template_id) REFERENCES task_templates (id) ON DELETE CASCADE,
  CONSTRAINT fk_ttd_dep FOREIGN KEY (depends_on_template_id) REFERENCES task_templates (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Tareas reales de un plan (copiadas de la plantilla en el momento de crear el plan)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS event_tasks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event_plan_id INT NOT NULL,
  template_id INT NULL COMMENT 'NULL = tarea personalizada',
  `key` VARCHAR(64) NOT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT NULL,
  team ENUM('EVENTOS','REDES','WEB') NOT NULL,
  assignee_id INT NOT NULL,
  due_offset_days INT NOT NULL,
  start_offset_days INT NULL,
  due_date DATE NOT NULL,
  start_date DATE NULL,
  gate ENUM('none','confirmed','held') NOT NULL DEFAULT 'none',
  confirms_event TINYINT(1) NOT NULL DEFAULT 0,
  status ENUM('blocked','active','done','skipped') NOT NULL DEFAULT 'blocked',
  activated_at DATETIME NULL,
  completed_at DATETIME NULL,
  completed_by INT NULL,
  completion_notes TEXT NULL,
  skipped_at DATETIME NULL,
  skipped_by INT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_event_tasks_plan (event_plan_id),
  INDEX idx_event_tasks_status_due (status, due_date),
  CONSTRAINT fk_et_plan FOREIGN KEY (event_plan_id) REFERENCES event_plans (id) ON DELETE CASCADE,
  CONSTRAINT fk_et_template FOREIGN KEY (template_id) REFERENCES task_templates (id) ON DELETE SET NULL,
  CONSTRAINT fk_et_assignee FOREIGN KEY (assignee_id) REFERENCES members (id),
  CONSTRAINT fk_et_completed_by FOREIGN KEY (completed_by) REFERENCES members (id),
  CONSTRAINT fk_et_skipped_by FOREIGN KEY (skipped_by) REFERENCES members (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS event_task_dependencies (
  task_id INT NOT NULL,
  depends_on_task_id INT NOT NULL,
  PRIMARY KEY (task_id, depends_on_task_id),
  CONSTRAINT fk_etd_task FOREIGN KEY (task_id) REFERENCES event_tasks (id) ON DELETE CASCADE,
  CONSTRAINT fk_etd_dep FOREIGN KEY (depends_on_task_id) REFERENCES event_tasks (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Checklist del día del evento (sin correos, sin dependencias, sin asignado)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS checklist_templates (
  id INT AUTO_INCREMENT PRIMARY KEY,
  team ENUM('EVENTOS','REDES','WEB') NOT NULL,
  title VARCHAR(255) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS event_checklist_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event_plan_id INT NOT NULL,
  team ENUM('EVENTOS','REDES','WEB') NOT NULL,
  title VARCHAR(255) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  done TINYINT(1) NOT NULL DEFAULT 0,
  done_by INT NULL,
  done_at DATETIME NULL,
  CONSTRAINT fk_eci_plan FOREIGN KEY (event_plan_id) REFERENCES event_plans (id) ON DELETE CASCADE,
  CONSTRAINT fk_eci_done_by FOREIGN KEY (done_by) REFERENCES members (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Log de actividad (base del informe PDF)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS event_activity_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event_plan_id INT NOT NULL,
  task_id INT NULL,
  actor_id INT NULL COMMENT 'NULL = sistema (cron)',
  action VARCHAR(64) NOT NULL,
  payload JSON NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_activity_plan_created (event_plan_id, created_at),
  CONSTRAINT fk_log_plan FOREIGN KEY (event_plan_id) REFERENCES event_plans (id) ON DELETE CASCADE,
  CONSTRAINT fk_log_task FOREIGN KEY (task_id) REFERENCES event_tasks (id) ON DELETE SET NULL,
  CONSTRAINT fk_log_actor FOREIGN KEY (actor_id) REFERENCES members (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Cola de correos (el cron los envía, nunca la petición web)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS email_outbox (
  id INT AUTO_INCREMENT PRIMARY KEY,
  to_member_id INT NULL,
  to_email VARCHAR(255) NOT NULL,
  subject VARCHAR(255) NOT NULL,
  body_html MEDIUMTEXT NOT NULL,
  body_text MEDIUMTEXT NOT NULL,
  kind VARCHAR(64) NOT NULL,
  dedupe_key VARCHAR(191) NOT NULL UNIQUE,
  status ENUM('pending','sent','failed') NOT NULL DEFAULT 'pending',
  attempts INT NOT NULL DEFAULT 0,
  last_error TEXT NULL,
  scheduled_for DATETIME NOT NULL,
  sent_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_outbox_status_scheduled (status, scheduled_for),
  CONSTRAINT fk_outbox_member FOREIGN KEY (to_member_id) REFERENCES members (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Control de ejecución diaria del cron (recordatorios/vencidas/resumen/draft)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cron_runs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  run_key VARCHAR(64) NOT NULL UNIQUE,
  run_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
