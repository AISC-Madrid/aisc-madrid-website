<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Same access rules as mail_creation.php
$allowed_roles = ['admin', 'web'];
if (!isset($_SESSION['activated']) || !in_array($_SESSION['role'], $allowed_roles)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'No autorizado']);
    exit();
}

require_once '../assets/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
    exit();
}

if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Token CSRF inválido']);
    exit();
}

$html = $_POST['html'] ?? '';
if (trim($html) === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Crea contenido primero']);
    exit();
}

// Only letters, numbers, "_" and "-" so the name can't escape the folder
$name = preg_replace('/\.html$/i', '', trim($_POST['filename'] ?? ''));
if (!preg_match('/^[A-Za-z0-9_-]{1,100}$/', $name)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Nombre no válido: usa solo letras, números, "_" o "-"']);
    exit();
}

$dir = realpath(__DIR__ . '/../mails/difusion');
if ($dir === false || !is_writable($dir)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'La carpeta mails/difusion no existe o no tiene permisos de escritura']);
    exit();
}

$path = $dir . DIRECTORY_SEPARATOR . $name . '.html';
$overwrite = ($_POST['overwrite'] ?? '') === '1';
if (file_exists($path) && !$overwrite) {
    http_response_code(409);
    echo json_encode(['ok' => false, 'exists' => true, 'error' => "Ya existe {$name}.html"]);
    exit();
}

// Some Docker/network filesystems don't support file locks, so retry without LOCK_EX
$written = @file_put_contents($path, $html, LOCK_EX);
if ($written === false) {
    $written = @file_put_contents($path, $html);
}

if ($written === false) {
    $reason = error_get_last()['message'] ?? 'motivo desconocido';
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => "No se pudo guardar el archivo: {$reason}"]);
    exit();
}

echo json_encode(['ok' => true, 'file' => "mails/difusion/{$name}.html"]);
