<?php
include("../assets/db.php");
include("upload_image.php");

// Initialize variables in case they are null
$youtube_url = !empty($_POST['youtube_url']) ? $_POST['youtube_url'] : null;
$google_calendar_url = !empty($_POST['google_calendar_url']) ? $_POST['google_calendar_url'] : null;

// 1. Insert event WITHOUT image paths first
$sql = "INSERT INTO events (
    title_es, title_en,
    type_es, type_en,
    speaker,
    description_es, description_en,
    location,
    start_datetime, end_datetime,
    youtube_url,
    google_calendar_url,
    requires_registration
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
if (!$stmt) die("Error al preparar la consulta: " . $conn->error);

$stmt->bind_param(
    "ssssssssssssi",
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
    $youtube_url,
    $google_calendar_url,
    isset($_POST['requires_registration']) ? 1 : 0
);


if (!$stmt->execute()) {
    die("<p style='color:red;'>❌ Error al insertar el evento: " . $stmt->error . "</p>");
}

// Get the new event ID
$eventId = $conn->insert_id;

// 2. Create folders for images (absolute path for PHP)
$eventFolder = __DIR__ . "/../images/events/event$eventId";
if (!is_dir($eventFolder)) mkdir($eventFolder, 0755, true);

// Folder for gallery
$galleryFolder = $eventFolder . "/gallery";
if (!is_dir($galleryFolder)) mkdir($galleryFolder, 0755, true);

// 3. Upload main image
$mainImage = handleImageUpload('image', $eventFolder);
if (isset($mainImage['error'])) {
    die("<p style='color:red;'>❌ Main image error: " . $mainImage['error'] . "</p>");
}
// Store relative path in DB
$mainImagePath = str_replace(__DIR__ . "/../", "", $mainImage['path']);

// 4. Upload gallery images
$gallery = handleMultipleImageUpload('images', $galleryFolder);
$galleryPaths = array_map(function($path) {
    return str_replace(__DIR__ . "/../", "", $path);
}, $gallery['paths']);
$galleryPathsJson = json_encode($galleryPaths);

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
