-- Perfil de contacto de los guests (#334, #335): teléfono y LinkedIn. La columna `email` ya existe
-- y se reutiliza como email de contacto. Columnas opcionales: NULL = sin dato. Idempotente.

SET NAMES utf8mb4;

ALTER TABLE guests
  ADD COLUMN IF NOT EXISTS phone VARCHAR(30) NULL AFTER email,
  ADD COLUMN IF NOT EXISTS linkedin_url VARCHAR(255) NULL AFTER phone;
