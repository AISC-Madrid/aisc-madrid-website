-- Texto personalizado del recordatorio por email: sustituye al párrafo "Te recordamos que estás registrado/a..."
-- Columna opcional: NULL = se usa el texto por defecto.

SET NAMES utf8mb4;

ALTER TABLE events ADD COLUMN IF NOT EXISTS reminder_custom_text TEXT NULL AFTER reminder_days_before;
