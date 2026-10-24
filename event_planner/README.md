# Planificador de eventos

Tracker interno de tareas por evento (sustituye al Excel), en `dashboard/event_planner/`.
Esta carpeta (`event_planner/lib/`) es la capa de dominio: nada de esto se sirve
directamente al navegador salvo `lib/mail_templates/*.html`, que solo se usan como
plantillas internas.

Páginas del dashboard:
- `index.php` — listado (Activos/Archivados).
- `new.php` — asistente de 3 pasos para crear un evento.
- `plan.php` — detalle: Kanban por estado/equipo, línea temporal, checklist, actividad,
  informe PDF, acciones de junta/admin (editar, cambiar fecha, confirmar/cerrar/cancelar,
  enlazar evento público, añadir tarea).
- `mis_tareas.php` — tareas activas del miembro conectado + próximas (bloqueadas).
- `admin/plantillas.php` y `admin/checklist.php` — CRUD de las plantillas (junta/admin).
- `report_pdf.php` — informe PDF del plan, con FPDF (ya usado en el repo para la lista de
  asistentes; no se añadió dompdf para no duplicar librería de PDF).

## Aplicar el esquema (manual, igual que el resto de `migrations/`)

```
mysql -u <user> -p <database> < migrations/05-event-planner-schema.sql
mysql -u <user> -p <database> < migrations/06-event-planner-seed.sql
php scripts/event_planner_validate_templates.php
```

El seed es re-ejecutable (usa `ON DUPLICATE KEY UPDATE` sobre `task_templates.key`). Vuelve
a correrlo cada vez que cambies la plantilla inicial en el `.sql` — los cambios en el admin
(Fase 3) no necesitan esto, se hacen desde la web.

## Variables de entorno nuevas

| Variable | Para qué | En producción |
|---|---|---|
| `EVENT_PLANNER_ALLOW_CLOCK_OVERRIDE` | Permite que `EVENT_PLANNER_NOW` sustituya al reloj real | **No definir** |
| `EVENT_PLANNER_NOW` | Fecha/hora simulada (`2026-11-10T09:05`), Europe/Madrid | No definir |
| `MAIL_REDIRECT_ALL_TO` | Si está definida, todos los correos del planificador van a esa dirección, con el destinatario real en el asunto (`[para: x@y]`) | No definir (o definir temporalmente para probar) |

## Probar el motor sin tocar el dashboard

```
EVENT_PLANNER_SIMULATE_CONFIRM=1 php scripts/event_planner_simulate.php
```

Crea un plan y 6 miembros temporales (emails `simulacion+...@aiscmadrid.test`), avanza el
reloj día a día completando/saltando tareas según un guion, imprime qué se activa y qué
correos se encolan cada día, comprueba que no hay `dedupe_key` duplicados y que el plan
llega a `closed`. Al terminar (incluso si falla a medias) borra todo lo que creó. **Solo
contra una base de datos de desarrollo/pruebas.**

## Cron

`scripts/event_planner_cron.php` no es accesible por web (comprueba `PHP_SAPI`). En
producción, en Coolify: **Scheduled Tasks** del recurso → nueva tarea →
comando `php /var/www/html/scripts/event_planner_cron.php`, cron `*/15 * * * *`.

Pasos que ejecuta cada vez: transición `confirmed -> held` / `held -> closed`,
`recomputePlan` en todos los planes abiertos, el paso diario (recordatorios, vencidas,
resumen de la junta, avisos de draft — una vez al día, a partir de las 09:00 Madrid,
controlado con `cron_runs`) y el envío de `email_outbox`.

## Mapeo de equipos (decisión del reconocimiento)

No se tocó la tabla `members`. El equipo de cada miembro se deriva de la columna
`role` ya existente (ver `lib/teams.php`):

- `role = 'events'` → equipo **EVENTOS**
- `role = 'web'` → equipo **WEB**
- `role = 'marketing'` → equipo **REDES**
- `role = 'admin' | 'finance'` → sin equipo (no pueden ocupar un slot, pero si además
  `board = 'yes'` o `role = 'admin'` sí son "junta o admin" a efectos de permisos)

## Entorno local (Windows + Herd + MySQL local)

Si `vendor/` tiene paquetes que no pintan nada aquí (Laravel, etc. — sobras de otro
proyecto en la misma carpeta), o `vendor/fpdf` no existe pese a estar en `composer.json`,
corre `composer install` para sincronizarlo con `composer.lock`. `vendor/` está en
`.gitignore`, así que esto es puramente local y no afecta al repo.

Pasos para correr el sitio en local (ver también la guía paso a paso que se dio durante
el desarrollo):
1. `config.php` (gitignored, cada quien el suyo) con `db_host/db_name/db_user/db_pass`
   apuntando a una base de datos de desarrollo con el esquema completo del sitio.
2. Aplicar `migrations/05-event-planner-schema.sql` y `06-event-planner-seed.sql`.
3. `php -S localhost:8000` desde la raíz del proyecto.
4. Login en `/login.php` con un miembro de prueba (`active='yes'`; `board='yes'` o
   `role='admin'` para probar las acciones de junta).

## Decisiones de la Fase 3

- **PDF con FPDF**, no dompdf: en `dev` ya se añadió `fpdf/fpdf` (lo usa
  `processing/export_attendees_pdf.php`), así que se reutilizó esa convención en vez de
  añadir una segunda librería de PDF para lo mismo.
- Las plantillas no se borran de verdad desde el admin, se desactivan (`is_active = 0`):
  un borrado real podría dejar huérfana la dependencia de otra plantilla activa. La
  checklist sí se borra de verdad (no tiene dependencias y no afecta a planes ya creados).
- Cada guardado de plantilla valida el grafo completo de plantillas activas
  (`event_planner_validate_template_graph`); si queda un ciclo, se hace `ROLLBACK` y se
  muestra el error en vez de guardar.
