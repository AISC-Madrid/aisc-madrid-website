<?php
session_start();

$allowed_roles = ['admin', 'web'];
if (!isset($_SESSION['activated']) || !in_array($_SESSION['role'], $allowed_roles)) {
    http_response_code(403);
    die("Acceso no autorizado");
}
require __DIR__ . "/../../assets/csrf.php";
if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    die("Token CSRF inválido.");
}
include(__DIR__ . "/../../assets/db.php");
include(__DIR__ . "/../../assets/contacts.php");

$id = isset($_POST['id']) && is_numeric($_POST['id']) ? (int) $_POST['id'] : null;
$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$category = $_POST['category'] ?? '';
$organization = trim($_POST['organization'] ?? '');
$notes = trim($_POST['notes'] ?? '');
$active = ($_POST['active'] ?? '1') === '1' ? 1 : 0;

if ($full_name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || !isset(CONTACT_CATEGORIES[$category])) {
    die("<p style='color:red;'>❌ Error: revisa el nombre, el email y la categoría.</p>");
}

$organization = $organization === '' ? null : $organization;
$notes = $notes === '' ? null : $notes;

if ($id) {
    $stmt = $conn->prepare("UPDATE contacts SET full_name = ?, email = ?, category = ?, organization = ?, notes = ?, active = ? WHERE id = ?");
    $stmt->bind_param("sssssii", $full_name, $email, $category, $organization, $notes, $active, $id);
} else {
    $stmt = $conn->prepare("INSERT INTO contacts (full_name, email, category, organization, notes, active) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $full_name, $email, $category, $organization, $notes, $active);
}

try {
    $stmt->execute();
} catch (mysqli_sql_exception $e) {
    if ($e->getCode() === 1062) {
        die("<p style='color:red;'>❌ Ya existe un contacto con el email " . htmlspecialchars($email) . ".</p>");
    }
    die("<p style='color:red;'>❌ Error al guardar: " . htmlspecialchars($e->getMessage()) . "</p>");
}

$stmt->close();
$conn->close();

header("Location: contacts_list.php?saved=1");
exit;
