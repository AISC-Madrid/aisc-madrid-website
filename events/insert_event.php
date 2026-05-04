<?php
session_start();
$allowed_roles = ['admin', 'events'];
if (!isset($_SESSION['activated']) || !in_array($_SESSION['role'], $allowed_roles)) {
    http_response_code(403);
    die("Acceso no autorizado");
}

include("../assets/db.php");
include("upload_image.php");

// Initialize variables in case they are null
$youtube_url = !empty($_POST['youtube_url']) ? $_POST['youtube_url'] : null;

// Convert form datetimes from Madrid timezone to UTC for storage
$madridTz = new DateTimeZone('Europe/Madrid');
$utcTz = new DateTimeZone('UTC');
$start_madrid = new DateTime($_POST['start_datetime'], $madridTz);
$start_utc = $start_madrid->setTimezone($utcTz)->format('Y-m-d H:i:s');
$end_madrid = new DateTime($_POST['end_datetime'], $madridTz);
$end_utc = $end_madrid->setTimezone($utcTz)->format('Y-m-d H:i:s');
$requires_registration = isset($_POST['requires_registration']) ? 1 : 0;
$reminder_enabled = isset($_POST['reminder_enabled']) ? 1 : 0;
$reminder_days_before = isset($_POST['reminder_days_before']) ? (int)$_POST['reminder_days_before'] : 2;

// 1. Insert event WITHOUT image paths first
$sql = "INSERT INTO events (
    title_es, title_en,
    type_es, type_en,
    speaker,
    description_es, description_en,
    location,
    start_datetime, end_datetime,
    youtube_url,
    requires_registration,
    reminder_enabled,
    reminder_days_before
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
if (!$stmt)
    die("Error al preparar la consulta: " . $conn->error);

$stmt->bind_param(
    "sssssssssssiii",
    $_POST['title_es'],
    $_POST['title_en'],
    $_POST['type_es'],
    $_POST['type_en'],
    $_POST['speaker'],
    $_POST['description_es'],
    $_POST['description_en'],
    $_POST['location'],
    $start_utc,
    $end_utc,
    $youtube_url,
    $requires_registration,
    $reminder_enabled,
    $reminder_days_before
);


if (!$stmt->execute()) {
    die("<p style='color:red;'>❌ Error al insertar el evento: " . $stmt->error . "</p>");
}

// Get the new event ID
$eventId = $conn->insert_id;

// 2. Cloudinary destination folders (no local folders needed anymore)
$eventFolder   = "events/event$eventId";
$galleryFolder = "events/event$eventId/gallery";

// 3. Upload main image to Cloudinary
$mainImage = handleImageUpload('image', $eventFolder);
if (isset($mainImage['error'])) {
    die("<p style='color:red;'>❌ Main image error: " . $mainImage['error'] . "</p>");
}
$mainImagePath = $mainImage['path']; // full Cloudinary URL

// 4. Upload gallery images to Cloudinary
$gallery = handleMultipleImageUpload('images', $galleryFolder);
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