<?php
// Reloj único del planificador. Toda la lógica de "hoy"/"ahora" pasa por aquí.
// Para probar el cron y las activaciones por fecha, define EVENT_PLANNER_NOW
// (p. ej. "2026-11-10T09:05") y EVENT_PLANNER_ALLOW_CLOCK_OVERRIDE=1.
// No definas EVENT_PLANNER_ALLOW_CLOCK_OVERRIDE en producción.

function event_planner_timezone(): DateTimeZone
{
    return new DateTimeZone('Europe/Madrid');
}

function event_planner_now(): DateTimeImmutable
{
    $override = getenv('EVENT_PLANNER_NOW');
    $allowOverride = getenv('EVENT_PLANNER_ALLOW_CLOCK_OVERRIDE') === '1';

    if ($allowOverride && $override) {
        return new DateTimeImmutable($override, event_planner_timezone());
    }

    return new DateTimeImmutable('now', event_planner_timezone());
}

function event_planner_today(): DateTimeImmutable
{
    return event_planner_now()->setTime(0, 0, 0);
}

// Convierte un DATETIME almacenado en UTC (igual que events.start_datetime) a la fecha
// natural en Madrid (sin hora), que es la base de todos los offsets de la plantilla.
function event_planner_event_date(string $startsAtUtc): DateTimeImmutable
{
    $dt = new DateTimeImmutable($startsAtUtc, new DateTimeZone('UTC'));
    return $dt->setTimezone(event_planner_timezone())->setTime(0, 0, 0);
}

function event_planner_add_days(DateTimeImmutable $date, int $offsetDays): DateTimeImmutable
{
    return $date->modify(($offsetDays >= 0 ? '+' : '') . $offsetDays . ' days');
}

// Umbral de activación de la regla B: a partir de las 09:00 de Madrid del start_date.
function event_planner_start_threshold(string $startDate): DateTimeImmutable
{
    return new DateTimeImmutable($startDate . ' 09:00:00', event_planner_timezone());
}

// "YYYY-MM-DD HH:MM" en hora de Madrid (lo que escribe el usuario en el asistente) -> cadena
// DATETIME en UTC (lo que se guarda en event_plans.starts_at, igual que events.start_datetime).
function event_planner_local_to_utc(string $localDateTime): string
{
    $dt = new DateTimeImmutable($localDateTime, event_planner_timezone());
    return $dt->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
}
