<?php
include("../assets/db.php");

// Make sure ID is provided
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    die("<p style='color:red;'>❌ Error: ID del evento no proporcionado.</p>");
}

$event_id = (int)$_POST['id'];

// Prepare SQL for UPDATE
$sql = "UPDATE events SET
    title_es = ?, title_en = ?,
    type_es = ?, type_en = ?,
    speaker = ?,
    description_es = ?, description_en = ?,
    location = ?,
    start_datetime = ?, end_datetime = ?,
    image_path = ?,
    google_calendar_url = ?
WHERE id = ?";

// Prepare statement
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("<p style='color:red;'>❌ Error al preparar la consulta: " . $conn->error . "</p>");
}

// Bind parameters
$stmt->bind_param(
    "ssssssssssssi",
    $_POST['title_es'],
    $_POST['title_en'],
    $_POST['type_es'],
    $_POST['type_en'],
    $_POST['speaker'],
    $_POST['description_es'],
    $_POST['description_en'],
    $_POST['location_es'],
    $_POST['start_datetime'],
    $_POST['end_datetime'],
    $_POST['image_path'],
    $_POST['google_calendar_url'],
    $event_id
);

// Execute
if ($stmt->execute()) {
    echo "<p style='color:green;'>✅ Evento actualizado correctamente.</p>";
    echo "<a href='events_list.php'>Volver al formulario</a>";
} else {
    echo "<p style='color:red;'>❌ Error al actualizar: " . $stmt->error . "</p>";
}

// Close
$stmt->close();
$conn->close();
?>
