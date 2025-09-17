<?php
include("../assets/db.php");
// Prepare SQL
$sql = "INSERT INTO events (
    title_es, title_en,
    type_es, type_en,
    speaker_es, speaker_en,
    description_es, description_en,
    location,
    start_datetime, end_datetime,
    image_path,
    google_calendar_url
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

// Prepare statement
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Error al preparar la consulta: " . $conn->error);
}

// Bind parameters
$stmt->bind_param(
    "sssssssssssss",
    $_POST['title_es'],
    $_POST['title_en'],
    $_POST['type_es'],
    $_POST['type_en'],
    $_POST['speaker_es'],
    $_POST['speaker_en'],
    $_POST['description_es'],
    $_POST['description_en'],
    $_POST['location_es'],
    $_POST['start_datetime'],
    $_POST['end_datetime'],
    $_POST['image_path'],
    $_POST['google_calendar_url']
);

// Execute
if ($stmt->execute()) {
    echo "<p style='color:green;'>✅ Evento guardado correctamente.</p>";
    echo "<a href='events_list.php'>Crear otro evento</a>";
} else {
    echo "<p style='color:red;'>❌ Error: " . $stmt->error . "</p>";
}

// Close
$stmt->close();
$conn->close();
?>