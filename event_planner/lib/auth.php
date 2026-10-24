<?php
// Permisos del planificador. Se comprueban contra la BD en cada petición (no solo en la
// sesión), porque el documento exige comprobar sesión y permisos en cada endpoint.

const EVENT_PLANNER_MEMBER_ROLES = ['admin', 'events', 'web', 'finance', 'marketing'];

// Exige una sesión de miembro activo (no guest). Si no la hay, redirige al login con
// ?next= para volver aquí después de autenticarse. Devuelve los datos frescos del miembro.
function ep_require_active_member(mysqli $conn): array
{
    if (!isset($_SESSION['activated']) || !in_array($_SESSION['role'] ?? '', EVENT_PLANNER_MEMBER_ROLES, true) || empty($_SESSION['user_id'])) {
        ep_redirect_to_login();
    }

    $memberId = (int) $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT id, full_name, mail, role, board FROM members WHERE id = ? AND active = 'yes'");
    $stmt->bind_param('i', $memberId);
    $stmt->execute();
    $member = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$member) {
        ep_redirect_to_login();
    }

    return $member;
}

function ep_is_board_or_admin(array $member): bool
{
    return ($member['role'] ?? '') === 'admin' || ($member['board'] ?? 'no') === 'yes';
}

function ep_require_board_or_admin(array $member): void
{
    if (!ep_is_board_or_admin($member)) {
        http_response_code(403);
        exit('Acceso no autorizado.');
    }
}

function ep_redirect_to_login(): void
{
    $next = urlencode($_SERVER['REQUEST_URI'] ?? '/dashboard/event_planner/index.php');
    header('Location: /login.php?next=' . $next);
    exit();
}
