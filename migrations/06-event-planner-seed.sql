-- Planificador de eventos: plantilla inicial de ~35 tareas + checklist del día del evento.
-- Es solo el punto de partida: se afina después desde /dashboard/event_planner/admin/plantillas.php.
-- Re-ejecutable: usa ON DUPLICATE KEY UPDATE sobre `key` (task_templates) y borra/reinserta las
-- dependencias por key para no duplicarlas. Tras ejecutar este fichero, valida el grafo con:
--   php scripts/event_planner_validate_templates.php

SET NAMES utf8mb4;

-- ---------------------------------------------------------------------------
-- Equipo Eventos
-- ---------------------------------------------------------------------------
INSERT INTO task_templates (`key`, title, description, team, default_slot, due_offset_days, start_offset_days, gate, confirms_event, sort_order) VALUES
('ev_contacto_ponente', 'Enviar mensaje o correo al ponente', 'Si es por LinkedIn, usar la plantilla "Eventos y Talleres/Eventos/Approach_Linkedin". Objetivo: que conozca la asociación y proponer hablar.', 'EVENTOS', 'EV_CONTACTO', -35, NULL, 'none', 0, 10),
('ev_videollamada', 'Si muestra interés, proponer videollamada', NULL, 'EVENTOS', 'EV_CONTACTO', -30, NULL, 'none', 0, 20),
('ev_carpeta_drive', 'Crear carpeta del evento en Drive', 'Carpeta con el nombre del ponente en "Eventos y Talleres/Eventos". Ahí se guarda todo lo del evento. Pega el link en las notas.', 'EVENTOS', 'EV_APOYO', -28, NULL, 'none', 0, 30),
('ev_confirmar_fecha', 'Cerrar fecha y hora con el ponente', NULL, 'EVENTOS', 'EV_CONTACTO', -25, NULL, 'none', 1, 40),
('ev_foto_ponente', 'Pedir fotografía al ponente y subirla a la carpeta', 'Para carteles y difusión.', 'EVENTOS', 'EV_CONTACTO', -21, NULL, 'confirmed', 0, 50),
('ev_elegir_sala', 'Elegir sala', 'Mirar salas de eventos anteriores.', 'EVENTOS', 'EV_APOYO', -16, NULL, 'confirmed', 0, 60),
('ev_reservar_sala', 'Reservar el espacio', 'https://www.uc3m.es/isec/reserva-espacios - mínimo 10 días de antelación. Indica la sala en las notas.', 'EVENTOS', 'EV_APOYO', -14, NULL, 'confirmed', 0, 70),
('ev_pdf_reserva', 'Subir el PDF de confirmación de la reserva a la carpeta', 'Cuando llegue por correo.', 'EVENTOS', 'EV_APOYO', -10, NULL, 'confirmed', 0, 80),
('ev_figura_3d', 'Encargar figura 3D / estatua del ponente', 'Contacto: Alejandro Barroso.', 'EVENTOS', 'EV_APOYO', -10, NULL, 'confirmed', 0, 90),
('ev_crear_evento_web', 'Crear el evento en la web', 'Cuello de botella: hasta que no existe el evento en la web no hay inscripciones. Con toda la información confirmada. Pega el link de inscripción en las notas.', 'EVENTOS', 'EV_CONTACTO', -11, NULL, 'confirmed', 0, 100),
('ev_difusion_asociaciones', 'Difusión a asociaciones', 'https://aplicaciones.uc3m.es/formulario/difusion_actividades_asociaciones', 'EVENTOS', 'EV_APOYO', -10, NULL, 'confirmed', 0, 110),
('ev_difusion_directores', 'Difusión a directores de grado/máster', 'Elegir conscientemente a qué directores (ante la duda, se manda). Breve introducción personal, no genérica, y después el mensaje maquetado por Web.', 'EVENTOS', 'EV_CONTACTO', -8, NULL, 'confirmed', 0, 120),
('ev_pegar_carteles', 'Imprimir y pegar carteles A4', 'Leganés: columnas de la salida de la biblioteca, columna de la salida principal de la cafetería, columna de la salida lateral de la cafetería, tablón de la entrada del edificio 4, tablón de la entrada del edificio 7 y otros sitios oportunos.', 'EVENTOS', 'EV_APOYO', -7, NULL, 'confirmed', 0, 130),
('ev_parking', 'Preguntar al ponente si necesita parking', 'Se hace siempre. Si lo necesita: nombre, teléfono y matrícula (a las notas). Uber si hace falta.', 'EVENTOS', 'EV_CONTACTO', -7, NULL, 'confirmed', 0, 140),
('ev_material', 'Comprobar el material para el día del evento', 'Micrófonos cargados, rollups, trípode, 2 HDMI, adaptador, tarjeta gráfica, estatua. Todo está en el despacho.', 'EVENTOS', 'EV_APOYO', -1, -3, 'confirmed', 0, 150),
('ev_quitar_carteles', 'Quitar los carteles del campus', NULL, 'EVENTOS', 'EV_APOYO', 3, NULL, 'held', 0, 160),
('ev_fotos_web', 'Añadir las fotos del evento a la web', 'Fotos en la carpeta de Drive del evento.', 'EVENTOS', 'EV_APOYO', 5, NULL, 'held', 0, 170)
ON DUPLICATE KEY UPDATE
  title = VALUES(title), description = VALUES(description), team = VALUES(team),
  default_slot = VALUES(default_slot), due_offset_days = VALUES(due_offset_days),
  start_offset_days = VALUES(start_offset_days), gate = VALUES(gate),
  confirms_event = VALUES(confirms_event), sort_order = VALUES(sort_order), is_active = 1;

-- ---------------------------------------------------------------------------
-- Equipo Redes
-- ---------------------------------------------------------------------------
INSERT INTO task_templates (`key`, title, description, team, default_slot, due_offset_days, start_offset_days, gate, confirms_event, sort_order) VALUES
('rd_cartel', 'Diseñar el cartel del evento (todos los formatos)', 'Una sola tarea con todos los formatos: cuadrado (web, Open UC3M, promo WhatsApp), Instagram/LinkedIn y A4 para imprimir. También el vídeo de promoción, si lo hay. Mejor esperar a tener la foto del ponente.', 'REDES', 'REDES_1', -12, -15, 'confirmed', 0, 200),
('rd_qr_a4', 'Añadir al cartel A4 el QR de inscripción', 'El QR debe llevar a la inscripción en la web, no a información. Al completarla, Eventos recibe automáticamente el aviso para imprimir y pegar.', 'REDES', 'REDES_1', -10, NULL, 'confirmed', 0, 210),
('rd_post_ig_linkedin', 'Publicar el cartel en Instagram y LinkedIn', NULL, 'REDES', 'REDES_1', -10, -10, 'confirmed', 0, 220),
('rd_story_lanzamiento', 'Story de Instagram con el post + link de inscripción', NULL, 'REDES', 'REDES_1', -10, -10, 'confirmed', 0, 230),
('rd_whatsapp', 'Publicar en la comunidad de WhatsApp y en los grupos de la uni', NULL, 'REDES', 'REDES_2', -10, -10, 'confirmed', 0, 240),
('rd_open_uc3m', 'Publicar en Open UC3M', NULL, 'REDES', 'REDES_2', -10, -10, 'confirmed', 0, 250),
('rd_video_promo', 'Publicar el vídeo promocional (si lo hay)', 'Desmarcar en el asistente si no hay vídeo.', 'REDES', 'REDES_1', -5, -5, 'confirmed', 0, 260),
('rd_guion_entrevista', 'Preparar el guion de la entrevista/reel con el ponente', NULL, 'REDES', 'REDES_2', -5, NULL, 'confirmed', 0, 270),
('rd_story_3dias', 'Story de recordatorio (repost de la publicación + link)', NULL, 'REDES', 'REDES_1', -3, -3, 'confirmed', 0, 280),
('rd_story_manana', '¡El evento es mañana!" (story)', NULL, 'REDES', 'REDES_1', -1, -1, 'confirmed', 0, 290),
('rd_grabar_entrevista', 'Grabar la entrevista/reel con el ponente', 'Antes o después del evento, en un sitio con buena luz y poco eco, con el rollup de AISC detrás si se puede. Preferiblemente sale un miembro de Redes.', 'REDES', 'REDES_2', 0, 0, 'confirmed', 0, 300),
('rd_recap', 'Publicar el recap del evento (Instagram y LinkedIn)', 'Mejores fotos, sin mucha edición, orgánico.', 'REDES', 'REDES_1', 2, NULL, 'held', 0, 310),
('rd_editar_reel', 'Editar y subir el reel', NULL, 'REDES', 'REDES_2', 5, NULL, 'held', 0, 320)
ON DUPLICATE KEY UPDATE
  title = VALUES(title), description = VALUES(description), team = VALUES(team),
  default_slot = VALUES(default_slot), due_offset_days = VALUES(due_offset_days),
  start_offset_days = VALUES(start_offset_days), gate = VALUES(gate),
  confirms_event = VALUES(confirms_event), sort_order = VALUES(sort_order), is_active = 1;

-- ---------------------------------------------------------------------------
-- Equipo Web
-- ---------------------------------------------------------------------------
INSERT INTO task_templates (`key`, title, description, team, default_slot, due_offset_days, start_offset_days, gate, confirms_event, sort_order) VALUES
('web_maquetar_mail', 'Maquetar el mail para directores de grado/máster y la newsletter', 'Mail para directores sencillo: foto, título, breve descripción, fecha y lugar, link de registro y link para añadir al calendario. Sin footer de la asociación ni link de baja, porque lo envían los directores.', 'WEB', 'WEB', -9, NULL, 'confirmed', 0, 400),
('web_newsletter', 'Enviar la newsletter', NULL, 'WEB', 'WEB', -8, NULL, 'confirmed', 0, 410),
('web_credenciales', 'Crear credenciales de invitado para el ponente', NULL, 'WEB', 'WEB', -3, NULL, 'confirmed', 0, 420),
('web_material_link', 'Añadir link de descarga de material en la página del taller', 'Desmarcar en el asistente si no aplica.', 'WEB', 'WEB', -1, NULL, 'confirmed', 0, 430),
('web_borrar_credenciales', 'Eliminar las credenciales de invitado del ponente', NULL, 'WEB', 'WEB', 7, NULL, 'held', 0, 440)
ON DUPLICATE KEY UPDATE
  title = VALUES(title), description = VALUES(description), team = VALUES(team),
  default_slot = VALUES(default_slot), due_offset_days = VALUES(due_offset_days),
  start_offset_days = VALUES(start_offset_days), gate = VALUES(gate),
  confirms_event = VALUES(confirms_event), sort_order = VALUES(sort_order), is_active = 1;

-- ---------------------------------------------------------------------------
-- Dependencias (por key, para no depender de IDs numéricos)
-- ---------------------------------------------------------------------------
DELETE ttd FROM task_template_dependencies ttd
  JOIN task_templates t ON t.id = ttd.task_template_id
  WHERE t.`key` IN (
    'ev_videollamada','ev_carpeta_drive','ev_confirmar_fecha','ev_foto_ponente','ev_elegir_sala',
    'ev_reservar_sala','ev_pdf_reserva','ev_figura_3d','ev_crear_evento_web','ev_difusion_asociaciones',
    'ev_difusion_directores','ev_pegar_carteles','ev_parking',
    'rd_cartel','rd_qr_a4','rd_post_ig_linkedin','rd_story_lanzamiento','rd_whatsapp','rd_open_uc3m',
    'rd_video_promo','rd_guion_entrevista','rd_story_3dias','rd_story_manana','rd_grabar_entrevista','rd_editar_reel',
    'web_maquetar_mail','web_newsletter','web_credenciales','web_material_link','web_borrar_credenciales'
  );

INSERT INTO task_template_dependencies (task_template_id, depends_on_template_id)
SELECT t.id, d.id FROM (
  SELECT 'ev_videollamada' AS tkey, 'ev_contacto_ponente' AS dkey
  UNION ALL SELECT 'ev_carpeta_drive', 'ev_contacto_ponente'
  UNION ALL SELECT 'ev_confirmar_fecha', 'ev_videollamada'
  UNION ALL SELECT 'ev_foto_ponente', 'ev_confirmar_fecha'
  UNION ALL SELECT 'ev_foto_ponente', 'ev_carpeta_drive'
  UNION ALL SELECT 'ev_elegir_sala', 'ev_confirmar_fecha'
  UNION ALL SELECT 'ev_reservar_sala', 'ev_elegir_sala'
  UNION ALL SELECT 'ev_pdf_reserva', 'ev_reservar_sala'
  UNION ALL SELECT 'ev_pdf_reserva', 'ev_carpeta_drive'
  UNION ALL SELECT 'ev_figura_3d', 'ev_foto_ponente'
  UNION ALL SELECT 'ev_crear_evento_web', 'rd_cartel'
  UNION ALL SELECT 'ev_crear_evento_web', 'ev_reservar_sala'
  UNION ALL SELECT 'ev_difusion_asociaciones', 'ev_crear_evento_web'
  UNION ALL SELECT 'ev_difusion_directores', 'web_maquetar_mail'
  UNION ALL SELECT 'ev_pegar_carteles', 'rd_qr_a4'
  UNION ALL SELECT 'ev_parking', 'ev_confirmar_fecha'
  UNION ALL SELECT 'rd_cartel', 'ev_foto_ponente'
  UNION ALL SELECT 'rd_qr_a4', 'rd_cartel'
  UNION ALL SELECT 'rd_qr_a4', 'ev_crear_evento_web'
  UNION ALL SELECT 'rd_post_ig_linkedin', 'rd_cartel'
  UNION ALL SELECT 'rd_post_ig_linkedin', 'ev_crear_evento_web'
  UNION ALL SELECT 'rd_story_lanzamiento', 'rd_post_ig_linkedin'
  UNION ALL SELECT 'rd_whatsapp', 'rd_cartel'
  UNION ALL SELECT 'rd_whatsapp', 'ev_crear_evento_web'
  UNION ALL SELECT 'rd_open_uc3m', 'rd_cartel'
  UNION ALL SELECT 'rd_open_uc3m', 'ev_crear_evento_web'
  UNION ALL SELECT 'rd_video_promo', 'rd_cartel'
  UNION ALL SELECT 'rd_guion_entrevista', 'ev_confirmar_fecha'
  UNION ALL SELECT 'rd_story_3dias', 'rd_post_ig_linkedin'
  UNION ALL SELECT 'rd_story_manana', 'rd_post_ig_linkedin'
  UNION ALL SELECT 'rd_grabar_entrevista', 'rd_guion_entrevista'
  UNION ALL SELECT 'rd_editar_reel', 'rd_grabar_entrevista'
  UNION ALL SELECT 'web_maquetar_mail', 'ev_crear_evento_web'
  UNION ALL SELECT 'web_maquetar_mail', 'rd_cartel'
  UNION ALL SELECT 'web_newsletter', 'web_maquetar_mail'
  UNION ALL SELECT 'web_credenciales', 'ev_crear_evento_web'
  UNION ALL SELECT 'web_material_link', 'ev_crear_evento_web'
  UNION ALL SELECT 'web_borrar_credenciales', 'web_credenciales'
) pairs
JOIN task_templates t ON t.`key` = pairs.tkey
JOIN task_templates d ON d.`key` = pairs.dkey;

-- ---------------------------------------------------------------------------
-- Checklist del día del evento
-- ---------------------------------------------------------------------------
DELETE FROM checklist_templates;
INSERT INTO checklist_templates (team, title, sort_order) VALUES
('EVENTOS', 'Llegar 30 min antes', 10),
('EVENTOS', 'Recibir al ponente (persona de contacto)', 20),
('EVENTOS', 'Llevar el material del despacho al aula: 2 rollups, trípode, 2 HDMI, adaptador, tarjeta gráfica, estatua del ponente', 30),
('EVENTOS', 'Pegar carteles en la puerta del aula', 40),
('EVENTOS', 'Colocar los rollups: uno en la entrada y otro junto a la proyección, que se vea en fotos', 50),
('EVENTOS', 'Recepción de asistentes escaneando QRs; inscribir en el momento a quien no lo esté. Una persona se queda 10 min más para los rezagados', 60),
('REDES', 'Story "¡Hemos empezado! Estamos en el aula X"', 10),
('REDES', 'Hacer contenido durante el evento (fotos y vídeos)', 20),
('REDES', 'Publicar varias stories orgánicas con fotos del evento', 30),
('REDES', 'Subir todo el material a la carpeta de Drive del evento', 40),
('WEB', 'Soporte en tiempo real: caídas de la web y registros o accesos de última hora', 10);
