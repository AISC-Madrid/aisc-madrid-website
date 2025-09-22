<?php
include("../assets/db.php");
include("upload_image.php");

// 1. Insert event WITHOUT image paths first
$sql = "INSERT INTO events (
    title_es, title_en,
    type_es, type_en,
    speaker,
    description_es, description_en,
    location,
    start_datetime, end_datetime,
    google_calendar_url
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
if (!$stmt) die("Error al preparar la consulta: " . $conn->error);

$stmt->bind_param(
    "sssssssssss",
    $_POST['title_es'],
    $_POST['title_en'],
    $_POST['type_es'],
    $_POST['type_en'],
    $_POST['speaker'],
    $_POST['description_es'],
    $_POST['description_en'],
    $_POST['location'],
    $_POST['start_datetime'],
    $_POST['end_datetime'],
    $_POST['google_calendar_url']
);

if (!$stmt->execute()) {
    die("<p style='color:red;'>❌ Error al insertar el evento: " . $stmt->error . "</p>");
}

// Get the new event ID
$eventId = $conn->insert_id;

// 2. Create folders for images
$eventFolder = "images/events/event$eventId";
if (!is_dir($eventFolder)) mkdir($eventFolder, 0755, true);

// 3. Upload main image
$mainImage = handleImageUpload('image', $eventFolder);
if (isset($mainImage['error'])) {
    die("<p style='color:red;'>❌ Main image error: " . $mainImage['error'] . "</p>");
}
$mainImagePath = $mainImage['path'];

// 4. Upload gallery images
$gallery = handleMultipleImageUpload('images', "$eventFolder/gallery");
$galleryPathsJson = json_encode($gallery['paths']);

// 5. Update the event row with image paths
$update = $conn->prepare("UPDATE events SET image_path = ?, gallery_paths = ? WHERE id = ?");
$update->bind_param("ssi", $mainImagePath, $galleryPathsJson, $eventId);

if ($update->execute()) {
    echo "<p style='color:green;'>✅ Evento guardado correctamente con imágenes.</p>";
    echo "<a href='events_list.php'>Crear otro evento</a>";
} else {
    echo "<p style='color:red;'>❌ Error al actualizar los paths: " . $update->error . "</p>";
}

// Close connections
$stmt->close();
$update->close();
$conn->close();
?>
