<?php
include("../assets/db.php");
include("upload_image.php");

// Make sure ID is provided
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    die("<p style='color:red;'>❌ Error: ID del evento no proporcionado.</p>");
}

$event_id = (int)$_POST['id'];

// Get current image paths from DB in case no new images are uploaded
$result = $conn->query("SELECT image_path, gallery_paths FROM events WHERE id = $event_id");
if (!$result || $result->num_rows === 0) {
    die("<p style='color:red;'>❌ Evento no encontrado.</p>");
}
$current = $result->fetch_assoc();
$currentMainImage = $current['image_path'];
$currentGallery = $current['gallery_paths'];

// 1. Handle main image upload (optional)
$mainImagePath = $currentMainImage;
if (!empty($_FILES['image']['name'])) {
    // Delete old main image folder
    $eventFolder = __DIR__ . "/../images/events/event$event_id";
    if (is_dir($eventFolder)) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($eventFolder, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $fileinfo) {
            $fileinfo->isDir() ? rmdir($fileinfo) : unlink($fileinfo);
        }
        rmdir($eventFolder);
    }

    // Create fresh folder
    mkdir($eventFolder, 0755, true);

    // Upload main image
    $mainImage = handleImageUpload('image', $eventFolder);
    if (isset($mainImage['error'])) {
        die("<p style='color:red;'>❌ Main image error: " . $mainImage['error'] . "</p>");
    }
    $mainImagePath = str_replace(__DIR__ . "/../", "", $mainImage['path']);
}

// 2. Handle gallery upload (optional)
$galleryPathsJson = $currentGallery; // default: keep existing
if (!empty($_FILES['images']['name'][0])) {
    $gallery = handleMultipleImageUpload('images', "$eventFolder/gallery");
    $galleryPaths = array_map(function($path) {
        return str_replace(__DIR__ . "/../", "", $path);
    }, $gallery['paths']);
    $galleryPathsJson = json_encode($galleryPaths);
}

// 3. Prepare SQL for UPDATE including optional images
$sql = "UPDATE events SET
    title_es = ?, title_en = ?,
    type_es = ?, type_en = ?,
    speaker = ?,
    description_es = ?, description_en = ?,
    location = ?,
    start_datetime = ?, end_datetime = ?,
    image_path = ?, gallery_paths = ?,
    google_calendar_url = ?,
    youtube_url = ?
WHERE id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("<p style='color:red;'>❌ Error al preparar la consulta: " . $conn->error . "</p>");
}

$youtubeUrl = !empty($_POST['youtube_url']) ? $_POST['youtube_url'] : null;
$googleCalendarUrl = !empty($_POST['google_calendar_url']) ? $_POST['google_calendar_url'] : null;

$stmt->bind_param(
    "ssssssssssi",
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
    $mainImagePath,
    $galleryPathsJson,
    $googleCalendarUrl,
    $youtubeUrl,
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
