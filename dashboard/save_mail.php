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

// Stored in the S3 bucket: the container disk is wiped on every deploy
require_once '../assets/mail_templates.php';

$key = MAIL_S3_PREFIX . $name . '.html';
$overwrite = ($_POST['overwrite'] ?? '') === '1';

try {
    if (!$overwrite) {
        $existing = s3_get_object($key);
        $existsInGit = file_exists(__DIR__ . "/../mails/difusion/{$name}.html");
        if (isset($existing['body']) || $existsInGit) {
            http_response_code(409);
            echo json_encode(['ok' => false, 'exists' => true, 'error' => "Ya existe {$name}.html"]);
            exit();
        }
        if (!$existing['missing']) {
            throw new RuntimeException($existing['error']);
        }
    }

    $result = s3_put_contents($html, $key, 'text/html; charset=utf-8');
} catch (Throwable $e) {
    $result = ['error' => $e->getMessage()];
}

if (isset($result['error'])) {
    http_response_code(502);
    echo json_encode(['ok' => false, 'error' => "No se pudo guardar el archivo: {$result['error']}"]);
    exit();
}

echo json_encode(['ok' => true, 'file' => $key]);
