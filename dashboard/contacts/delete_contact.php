<?php
session_start();

$allowed_roles = ['admin', 'web'];
if (!isset($_SESSION['activated']) || !in_array($_SESSION['role'], $allowed_roles)) {
    http_response_code(403);
    die("Acceso no autorizado");
}
require __DIR__ . "/../../assets/csrf.php";
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validate_csrf_token($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    die("Token CSRF inválido.");
}
include(__DIR__ . "/../../assets/db.php");

$id = (int) ($_POST['id'] ?? 0);
if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM contacts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}
$conn->close();

header("Location: contacts_list.php?deleted=1");
exit;
