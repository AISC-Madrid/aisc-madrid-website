<?php
// Incluido al principio de cada página del planificador: sesión, BD, config, librería de
// dominio y comprobación de que hay un miembro activo con sesión iniciada. No imprime HTML.

session_start();

require_once __DIR__ . '/../../assets/db.php';
$config = include __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../assets/csrf.php';
require_once __DIR__ . '/../../event_planner/lib/auth.php';
require_once __DIR__ . '/../../event_planner/lib/plan_service.php'; // arrastra clock/teams/graph/recompute/notifications/mailer/activity_log

$currentMember = ep_require_active_member($conn);
$isBoardOrAdmin = ep_is_board_or_admin($currentMember);
