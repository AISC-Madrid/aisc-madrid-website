-- Migración de imágenes de proyectos: Cloudinary -> MinIO (bucket aisc-public, prefijo projects/)
-- 3 proyectos · 12 imágenes (3 portadas + 9 de galería) · MariaDB
-- Guarda solo la KEY (p. ej. projects/4/cover.webp). El backend construye la URL con
-- MEDIA_BASE_URL = https://s3.aiscmadrid.com/aisc-public  ->  MEDIA_BASE_URL . '/' . key
-- IMPORTANTE: despliega antes el helper PHP que acepta keys 'projects/' o las imágenes dejarán de verse.

-- 1) Copia de seguridad de las columnas afectadas (IF NOT EXISTS: re-ejecutar no pisa el backup original)
CREATE TABLE IF NOT EXISTS projects_media_backup_20260911 AS SELECT id, image_path, gallery_paths FROM projects WHERE id IN (2, 3, 4);

START TRANSACTION;

-- 2) Solo se actualiza la fila si image_path sigue siendo la URL de Cloudinary original
UPDATE projects SET image_path = 'projects/2/cover.webp', gallery_paths = JSON_ARRAY('projects/2/gallery/01.webp', 'projects/2/gallery/02.webp') WHERE id = 2 AND image_path = 'https://res.cloudinary.com/dpchpoort/image/upload/aisc_madrid/projects/project2/img_693e6f7950f6b7.59363208.webp';
UPDATE projects SET image_path = 'projects/3/cover.webp', gallery_paths = JSON_ARRAY() WHERE id = 3 AND image_path = 'https://res.cloudinary.com/dpchpoort/image/upload/aisc_madrid/projects/project3/img_693ef0d31d93d3.86441990.webp';
UPDATE projects SET image_path = 'projects/4/cover.webp', gallery_paths = JSON_ARRAY('projects/4/gallery/01.webp', 'projects/4/gallery/02.webp', 'projects/4/gallery/03.webp', 'projects/4/gallery/04.webp', 'projects/4/gallery/05.webp', 'projects/4/gallery/06.webp', 'projects/4/gallery/07.webp') WHERE id = 4 AND image_path = 'https://res.cloudinary.com/dpchpoort/image/upload/aisc_madrid/projects/project4/img_69b54011e5dc97.82106295.webp';

-- 3) Revisa: deben salir 3 filas con key y 0 con 'cloudinary'
SELECT id, image_path, JSON_LENGTH(gallery_paths) AS n_gallery FROM projects WHERE id IN (2, 3, 4) ORDER BY id;
SELECT id, image_path FROM projects WHERE image_path LIKE '%cloudinary%' OR gallery_paths LIKE '%cloudinary%';

-- 4) COMMIT solo si las 3 filas quedaron migradas y no queda nada en Cloudinary; si no, ROLLBACK.
--    Automático para que funcione igual en AdminNeo (que cierra la conexión al terminar la
--    petición y descartaría una transacción abierta) que en el cliente mariadb.
SELECT COUNT(*) INTO @migrated FROM projects WHERE id IN (2, 3, 4) AND image_path LIKE 'projects/%';
SELECT COUNT(*) INTO @cloud FROM projects WHERE image_path LIKE '%cloudinary%' OR gallery_paths LIKE '%cloudinary%';
SELECT @migrated AS migradas, @cloud AS con_cloudinary;
DELIMITER //
BEGIN NOT ATOMIC
  IF @migrated = 3 AND @cloud = 0 THEN COMMIT; SELECT 'COMMITTED' AS resultado;
  ELSE ROLLBACK; SELECT 'ROLLED BACK' AS resultado; END IF;
END //
DELIMITER ;

-- Revertir después del COMMIT:
-- UPDATE projects p JOIN projects_media_backup_20260911 b ON b.id = p.id SET p.image_path = b.image_path, p.gallery_paths = b.gallery_paths;
