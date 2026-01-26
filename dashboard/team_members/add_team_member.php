<?php
session_start();
$allowed_roles = ['admin', 'finance'];
if (!isset($_SESSION['activated']) || !in_array($_SESSION['role'], $allowed_roles)) {
    http_response_code(403);
    die("Acceso no autorizado");
}

include(__DIR__ . "/../../assets/db.php");
// Prepare SQL
$password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
$sql = "INSERT INTO members (
    full_name,
    mail,
    password_hash,
    position_es, position_en,
    phone,
    dni,
    socials,
    board,
    active,
    image_path
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

// Prepare statement
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Error al preparar la consulta: " . $conn->error);
}

// Bind parameters
$stmt->bind_param(
    "sssssssssss",
    $_POST['full_name'],
    $_POST['mail'],
    $password_hash,
    $_POST['position_es'],
    $_POST['position_en'],
    $_POST['phone'],
    $_POST['dni'],
    $_POST['socials'],
    $_POST['board'],
    $_POST['active'],
    $_POST['image_path']
);

// Execute
if ($stmt->execute()) {
    echo "<p style='color:green;'>✅ Miembro guardado correctamente.</p>";
    echo "<a href='team_members_list.php'>Añadir otro miembro</a>";
} else {
    echo "<p style='color:red;'>❌ Error: " . $stmt->error . "</p>";
}

// Close
$stmt->close();
$conn->close();
?>