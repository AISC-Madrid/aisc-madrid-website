<?php
include(__DIR__ . "/../../assets/db.php");

// Make sure ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("<p style='color:red;'>❌ Error: ID del miembro no proporcionado.</p>");
}

$id = (int)$_GET['id'];

// Prepare SQL for UPDATE
$sql = "UPDATE members SET
    full_name = ?,
    mail = ?,
    position_es = ?, position_en = ?,
    phone = ?,
    socials = ?,
    active = ?,
    image_path = ?
WHERE id = ?";

// Prepare statement
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("<p style='color:red;'>❌ Error al preparar la consulta: " . $conn->error . "</p>");
}

// Bind parameters
$stmt->bind_param(
    "ssssssssi",
    $_POST['full_name'],
    $_POST['mail'],
    $_POST['position_es'],
    $_POST['position_en'],
    $_POST['phone'],
    $_POST['socials'],
    $_POST['active'],
    $_POST['image_path'],
    $id
);

// Execute
if ($stmt->execute()) {
    echo "<p style='color:green;'>✅ Miembro actualizado correctamente.</p>";
    echo "<a href='events_list.php'>Volver al formulario</a>";
} else {
    echo "<p style='color:red;'>❌ Error al actualizar: " . $stmt->error . "</p>";
}

// Close
$stmt->close();
$conn->close();
?>
