<?php
include("../assets/db.php");
include("upload_image.php");

// Initialize variables in case they are null
$youtube_url = !empty($_POST['youtube_url']) ? $_POST['youtube_url'] : null;
$open_registration = isset($_POST['open_registration']) ? 1 : 0;

$status = trim($_POST['status'] ?? ''); // name del <select>

$allowed = ['idea','en curso','finalizado','pausado'];
if (!in_array($status, $allowed, true)) {
    $status = 'idea'; // default seguro
}

// 1. Insert project WITHOUT image paths first
$sql = "INSERT INTO projects (
    title_es, title_en,
    short_description_es, short_description_en,
    description_es, description_en,
    status, category,
    start_date, end_date,
    youtube_url, open_registration
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
if (!$stmt) die("Error al preparar la consulta: " . $conn->error);


$stmt->bind_param(
    "ssssssssssss",
    $_POST['title_es'],
    $_POST['title_en'],
    $_POST['short_description_es'],
    $_POST['short_description_en'],
    $_POST['description_es'],
    $_POST['description_en'],
    $status,
    $_POST['category'],
    $_POST['start_date'],
    $_POST['end_date'],
    $youtube_url,
    $open_registration
);

if (!$stmt->execute()) {
    die("<p style='color:red;'>❌ Error al insertar el proyecto: " . $stmt->error . "</p>");
}

// Get the new project ID
$projectId = $conn->insert_id;

// 2. Create folders for images (absolute path for PHP)
$projectFolder = __DIR__ . "/../images/projects/project$projectId";
if (!is_dir($projectFolder)) mkdir($projectFolder, 0755, true);

// Folder for gallery
$galleryFolder = $projectFolder . "/gallery";
if (!is_dir($galleryFolder)) mkdir($galleryFolder, 0755, true);

// 3. Upload main image
$mainImage = handleImageUpload('image', $projectFolder);
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

// 5. Update the project row with image paths
$update = $conn->prepare("UPDATE projects SET image_path = ?, gallery_paths = ? WHERE id = ?");
$update->bind_param("ssi", $mainImagePath, $galleryPathsJson, $projectId);

if ($update->execute()) {
    echo "<p style='color:green;'>✅ Proyecto guardado correctamente con imágenes.</p>";
    echo "<a href='projects_list.php'>Crear otro Proyecto</a>";
} else {
    echo "<p style='color:red;'>❌ Error al actualizar los paths: " . $update->error . "</p>";
}

// Close connections
$stmt->close();
$update->close();
$conn->close();
?>
