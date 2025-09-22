<?php
include("../assets/db.php");
include("upload_image.php");

// Upload main image
$mainImage = handleImageUpload('image', 'image/events/eventx');
if (isset($mainImage['error'])) {
    die("<p style='color:red;'>❌ Main image error: " . $mainImage['error'] . "</p>");
}
$mainImagePath = $mainImage['path'];

// Upload gallery images
$gallery = handleMultipleImageUpload('images', 'image/events/eventx/gallery');
$galleryPathsJson = json_encode($gallery['paths']);

// Prepare SQL (added gallery_paths column)
$sql = "INSERT INTO events (
    title_es, title_en,
    type_es, type_en,
    speaker,
    description_es, description_en,
    location,
    start_datetime, end_datetime,
    image_path,
    gallery_paths,
    google_calendar_url
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)";

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
    $_POST['speaker'],
    $_POST['description_es'],
    $_POST['description_en'],
    $_POST['location'],
    $_POST['start_datetime'],
    $_POST['end_datetime'],
    $mainImagePath,
    $galleryPathsJson,
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
