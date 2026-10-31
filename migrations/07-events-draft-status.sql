-- Borradores de eventos: un evento en 'draft' no aparece en la web pública ni admite inscripciones.
-- Los eventos existentes quedan como 'published' por el DEFAULT.
-- Un borrador solo exige title_es, así que title_en y las fechas pasan a admitir NULL
-- (al publicar se siguen exigiendo desde el formulario). Idempotente.

ALTER TABLE events
  ADD COLUMN IF NOT EXISTS status ENUM('draft','published') NOT NULL DEFAULT 'published' AFTER id,
  MODIFY title_en VARCHAR(255) NULL,
  MODIFY start_datetime DATETIME NULL,
  MODIFY end_datetime DATETIME NULL;

CREATE INDEX IF NOT EXISTS idx_events_status ON events (status);
