# Planificador de eventos (Fase 1 — motor y correos)

Tracker interno de tareas por evento, en `dashboard/event_planner/` (Fase 2). Esta carpeta
(`event_planner/lib/`) es la capa de dominio: nada de esto se sirve directamente al
navegador salvo `lib/mail_templates/*.html`, que solo se usan como plantillas internas.

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

## Siguiente (Fase 2)

Páginas del dashboard (`dashboard/event_planner/`): listado, asistente de 3 pasos, detalle
(Kanban + línea temporal + checklist + actividad), "Mis tareas", deep links con `?next=`
(ya soportado en `login.php`).
