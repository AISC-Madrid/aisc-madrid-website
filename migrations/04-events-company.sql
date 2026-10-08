-- Empresa del ponente en eventos (se muestra en el email a contactos: "...la visita de <speaker>, de <company>...")
-- Columna opcional: los eventos existentes quedan con NULL y siguen funcionando igual.

SET NAMES utf8mb4;

ALTER TABLE events ADD COLUMN IF NOT EXISTS company VARCHAR(150) NULL AFTER speaker;
