<?php
include(__DIR__ . "/../../assets/db.php");

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    die("<p style='color:red;'>❌ Error: ID del miembro no proporcionado.</p>");
}

$id = (int)$_POST['id'];

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
    $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
} else {
    // Keep current password if no new password is provided
    $password_hash = $current_password_hash;
}

// 🔹 Actualizar datos del miembro
$sql = "UPDATE members SET
    full_name = ?,
    mail = ?,
    position_es = ?,
    position_en = ?,
    phone = ?,
    password_hash = ?,
    socials = ?,
    board = ?,
    active = ?,
    image_path = ?
WHERE id = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("<p style='color:red;'>❌ Error al preparar la consulta: " . $conn->error . "</p>");
}

$stmt->bind_param(
    "ssssssssssi",
    $_POST['full_name'],
    $_POST['mail'],
    $_POST['position_es'],
    $_POST['position_en'],
    $_POST['phone'],
    $password_hash,
    $_POST['socials'],
    $_POST['board'],
    $_POST['active'],
    $_POST['image_path'],
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
