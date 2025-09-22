<?php
include(__DIR__ . "/../../assets/db.php");
// Prepare SQL
$sql = "INSERT INTO members (
    full_name,
    mail,
    position_es, position_en,
    phone,
    socials,
    active,
    image_path
) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

// Prepare statement
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Error al preparar la consulta: " . $conn->error);
}

// Bind parameters
$stmt->bind_param(
    "ssssssss",
    $_POST['full_name'],
    $_POST['mail'],
    $_POST['position_es'],
    $_POST['position_en'],
    $_POST['phone'],
    $_POST['socials'],
    $_POST['active'],
    $_POST['image_path'],
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