<?php
// Mapea el rol de dashboard existente en members.role a los 3 equipos del planificador,
// tal y como se decidió en el reconocimiento (sin tocar la tabla members):
//   role 'events'    -> equipo EVENTOS
//   role 'web'       -> equipo WEB
//   role 'marketing' -> equipo REDES
//   role 'admin'     -> los tres equipos (pueden ocupar cualquier slot)
// role 'finance'/'guest' no pertenecen a ningún equipo (no pueden ocupar un slot).

const EVENT_PLANNER_ROLE_TO_TEAM = [
    'events' => 'EVENTOS',
    'web' => 'WEB',
    'marketing' => 'REDES',
];

// Roles que, además de su equipo de EVENT_PLANNER_ROLE_TO_TEAM (si tienen), pueden
// ocupar un slot de cualquier equipo.
const EVENT_PLANNER_ROLES_IN_ALL_TEAMS = ['admin'];

const EVENT_PLANNER_SLOTS = ['EV_CONTACTO', 'EV_APOYO', 'REDES_1', 'REDES_2', 'WEB'];

const EVENT_PLANNER_SLOT_TEAM = [
    'EV_CONTACTO' => 'EVENTOS',
    'EV_APOYO' => 'EVENTOS',
    'REDES_1' => 'REDES',
    'REDES_2' => 'REDES',
    'WEB' => 'WEB',
];

function event_planner_team_for_role(string $role): ?string
{
    return EVENT_PLANNER_ROLE_TO_TEAM[$role] ?? null;
}

// Miembros activos que pertenecen a un equipo dado (candidatos para un slot o reasignación).
// Incluye a quienes tengan el rol propio del equipo y, además, a los roles de
// EVENT_PLANNER_ROLES_IN_ALL_TEAMS (hoy, 'admin'), que valen para cualquier equipo.
function event_planner_active_members_for_team(mysqli $conn, string $team): array
{
    $roles = array_keys(array_filter(EVENT_PLANNER_ROLE_TO_TEAM, fn($t) => $t === $team));
    $roles = array_unique(array_merge($roles, EVENT_PLANNER_ROLES_IN_ALL_TEAMS));
    if (empty($roles)) {
        return [];
    }
    $placeholders = implode(',', array_fill(0, count($roles), '?'));
    $stmt = $conn->prepare("SELECT id, full_name, mail, role FROM members WHERE active = 'yes' AND role IN ($placeholders) ORDER BY full_name");
    $stmt->bind_param(str_repeat('s', count($roles)), ...$roles);
    $stmt->execute();
    $result = $stmt->get_result();
    $members = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $members;
}

function event_planner_member_is_active(mysqli $conn, int $memberId): bool
{
    $stmt = $conn->prepare("SELECT 1 FROM members WHERE id = ? AND active = 'yes'");
    $stmt->bind_param('i', $memberId);
    $stmt->execute();
    $found = (bool) $stmt->get_result()->fetch_row();
    $stmt->close();
    return $found;
}
