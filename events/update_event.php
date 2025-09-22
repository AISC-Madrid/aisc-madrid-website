<?php
include("../assets/db.php");
include("upload_image.php");

// Make sure ID is provided
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    die("<p style='color:red;'>❌ Error: ID del evento no proporcionado.</p>");
}

$event_id = (int)$_POST['id'];

// 1. Delete old event folder (main image + gallery)
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

// 2. Create fresh folder for images
if (!is_dir($eventFolder)) {
    mkdir($eventFolder, 0755, true);
}

// 3. Upload main image
$mainImage = handleImageUpload('image', $eventFolder);
if (isset($mainImage['error'])) {
    die("<p style='color:red;'>❌ Main image error: " . $mainImage['error'] . "</p>");
}
$mainImagePath = str_replace(__DIR__ . "/../", "", $mainImage['path']); // relative path for DB

// 4. Upload gallery images
$gallery = handleMultipleImageUpload('images', "$eventFolder/gallery");
$galleryPaths = array_map(function($path) {
    return str_replace(__DIR__ . "/../", "", $path);
}, $gallery['paths']);
$galleryPathsJson = json_encode($galleryPaths);

// 5. Prepare SQL for UPDATE including speaker and images
$sql = "UPDATE events SET
    title_es = ?, title_en = ?,
    type_es = ?, type_en = ?,
    speaker = ?,
    description_es = ?, description_en = ?,
    location = ?,
    start_datetime = ?, end_datetime = ?,
    image_path = ?, gallery_paths = ?,
    google_calendar_url = ?
WHERE id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("<p style='color:red;'>❌ Error al preparar la consulta: " . $conn->error . "</p>");
}

// Bind parameters
$stmt->bind_param(
    "sssssssssssssi",
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
    $_POST['google_calendar_url'],
    $event_id
);

// Execute
if ($stmt->execute()) {
    echo "<p style='color:green;'>✅ Evento actualizado correctamente con nuevas imágenes.</p>";
    echo "<a href='events_list.php'>Volver al formulario</a>";
} else {
    echo "<p style='color:red;'>❌ Error al actualizar: " . $stmt->error . "</p>";
}

// Close
$stmt->close();
$conn->close();
?>
