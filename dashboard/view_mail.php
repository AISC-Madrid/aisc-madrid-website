<?php
session_start();

// Preview of a Mail-Builder mail stored in S3 (used by send_test_email.php)
$allowed_roles = ['admin', 'web'];
if (!isset($_SESSION['activated']) || !in_array($_SESSION['role'], $allowed_roles)) {
    http_response_code(403);
    exit('No autorizado');
}

require_once '../assets/mail_templates.php';

$key = (string) ($_GET['key'] ?? '');
if (!is_saved_mail_key($key)) {
    http_response_code(400);
    exit('Mail no válido');
}

try {
    $result = s3_get_object($key);
} catch (Throwable $e) {
    $result = ['error' => $e->getMessage(), 'missing' => false];
}

if (isset($result['error'])) {
    http_response_code($result['missing'] ? 404 : 502);
    exit(htmlspecialchars($result['error']));
}

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');
echo $result['body'];
