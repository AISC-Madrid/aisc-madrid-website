<?php
session_start();
$allowed_roles = ['admin', 'events'];
if (!isset($_SESSION['activated']) || !in_array($_SESSION['role'], $allowed_roles)) {
    http_response_code(403);
    die("Acceso no autorizado");
}

include("../assets/db.php");
include("upload_image.php");
include("event_form.php");

// "draft" keeps the event hidden from the public site; anything else publishes it
$status = ($_POST['action'] ?? '') === 'draft' ? 'draft' : 'published';

if (trim($_POST['title_es'] ?? '') === '') {
    die("<p style='color:red;'>❌ El título en español es obligatorio, incluso para un borrador.</p>");
}

$hasMainImage = !empty($_FILES['image']['name']);
if ($status === 'published') {
    $missing = event_publish_errors($_POST, $hasMainImage);
    if ($missing) {
        event_die_missing($missing);
    }
}

// Initialize variables in case they are null
$youtube_url = !empty($_POST['youtube_url']) ? $_POST['youtube_url'] : null;
$title_en = trim($_POST['title_en'] ?? '') ?: null;

// Convert form datetimes from Madrid timezone to UTC for storage (drafts may leave them empty)
$start_utc = event_form_datetime_utc($_POST['start_datetime'] ?? null);
$end_utc = event_form_datetime_utc($_POST['end_datetime'] ?? null);
$requires_registration = isset($_POST['requires_registration']) ? 1 : 0;
$reminder_enabled = isset($_POST['reminder_enabled']) ? 1 : 0;
$reminder_days_before = isset($_POST['reminder_days_before']) ? (int)$_POST['reminder_days_before'] : 2;
$company = trim($_POST['company'] ?? '') ?: null;

// 1. Insert event WITHOUT image paths first
$sql = "INSERT INTO events (
    status,
    title_es, title_en,
    type_es, type_en,
    speaker,
    company,
    description_es, description_en,
    location,
    start_datetime, end_datetime,
    youtube_url,
    requires_registration,
    reminder_enabled,
    reminder_days_before
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
if (!$stmt)
    die("Error al preparar la consulta: " . $conn->error);

$stmt->bind_param(
    "sssssssssssssiii",
    $status,
    $_POST['title_es'],
    $title_en,
    $_POST['type_es'],
    $_POST['type_en'],
    $_POST['speaker'],
    $company,
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

// 2. S3 media bucket destination prefixes
$eventFolder   = "events-workshops/$eventId";
$galleryFolder = "events-workshops/$eventId/gallery";

// 3. Upload main image to S3 (a draft may not have it yet)
$mainImagePath = null;
if ($hasMainImage) {
    $mainImage = handleImageUpload('image', $eventFolder);
    if (isset($mainImage['error'])) {
        die("<p style='color:red;'>❌ Main image error: " . $mainImage['error'] . "</p>");
    }
    $mainImagePath = $mainImage['path']; // bucket key
}

// 4. Upload gallery images to S3
$gallery = handleMultipleImageUpload('images', $galleryFolder);
$galleryPathsJson = json_encode($gallery['paths']);

// 5. Update the event row with image paths
$update = $conn->prepare("UPDATE events SET image_path = ?, gallery_paths = ? WHERE id = ?");
$update->bind_param("ssi", $mainImagePath, $galleryPathsJson, $eventId);

if ($update->execute()) {
    if ($status === 'draft') {
        echo "<p style='color:green;'>✅ Borrador guardado. No será visible en la web hasta que lo publiques.</p>";
        echo "<a href='create_event.php?id=$eventId'>Seguir editando</a> · ";
    } else {
        echo "<p style='color:green;'>✅ Evento publicado correctamente con imágenes.</p>";
    }
    echo "<a href='events_list.php'>Volver a la lista de eventos</a>";
} else {
    echo "<p style='color:red;'>❌ Error al actualizar los paths: " . $update->error . "</p>";
}

// Close connections
$stmt->close();
$update->close();
$conn->close();
?>
