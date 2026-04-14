<?php
session_start();
$allowed_roles = ['admin', 'finance'];
if (!isset($_SESSION['activated']) || !in_array($_SESSION['role'], $allowed_roles)) {
    http_response_code(403);
    die("Acceso no autorizado");
}

include(__DIR__ . "/../../assets/db.php");

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    die("<p style='color:red;'>❌ Error: ID del miembro no proporcionado.</p>");
}

$id = (int) $_POST['id'];

// Get current password hash member
$query = $conn->prepare("SELECT password_hash FROM members WHERE id = ?");
$query->bind_param("i", $id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows === 0) {
    die("<p style='color:red;'>❌ Error: No se encontró el miembro con ID $id.</p>");
}

$row = $result->fetch_assoc();
$current_password_hash = $row['password_hash'];
$query->close();


if (!empty($_POST['password'])) {
    if (strlen($_POST['password']) < 6) {
        die("<p style='color:red;'>❌ Error: La contraseña debe tener al menos 6 caracteres.</p>");
    }
    $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
} else {
    // Keep current password if no new password is provided
    $password_hash = $current_password_hash;
}

$is_honor = ($_POST['is_honor'] ?? 'no') === 'yes' ? 'yes' : 'no';

// 🔹 Actualizar datos del miembro
$sql = "UPDATE members SET
    full_name = ?,
    mail = ?,
    position_es = ?,
    position_en = ?,
    phone = ?,
    dni = ?,
    password_hash = ?,
    socials = ?,
    board = ?,
    active = ?,
    image_path = ?,
    role = ?,
    is_honor = ?,
    graduation_year = ?,
    honor_quote = ?
WHERE id = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("<p style='color:red;'>❌ Error al preparar la consulta: " . $conn->error . "</p>");
}

$stmt->bind_param(
    "sssssssssssssssi",
    $_POST['full_name'],
    $_POST['mail'],
    $_POST['position_es'],
    $_POST['position_en'],
    $_POST['phone'],
    $_POST['dni'],
    $password_hash,
    $_POST['socials'],
    $_POST['board'],
    $_POST['active'],
    $_POST['image_path'],
    $_POST['role'],
    $is_honor,
    $_POST['graduation_year'],
    $_POST['honor_quote'],
    $id
);

if ($stmt->execute()) {
    echo "<p style='color:green;'>✅ Miembro actualizado correctamente.</p>";
    echo "<a href='team_members_list.php'>Volver al formulario</a>";
} else {
    echo "<p style='color:red;'>❌ Error al actualizar: " . $stmt->error . "</p>";
}

$stmt->close();
$conn->close();
?>