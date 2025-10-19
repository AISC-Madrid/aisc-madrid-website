<?php
include("../assets/db.php");
include("upload_image.php");

// Make sure ID is provided
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    die("<p style='color:red;'>❌ Error: ID del proyecto no proporcionado.</p>");
}

$project_id = (int)$_POST['id'];

// Get current image paths from DB in case no new images are uploaded
$result = $conn->query("SELECT image_path, gallery_paths FROM projects WHERE id = $project_id");
if (!$result || $result->num_rows === 0) {
    die("<p style='color:red;'>❌ proyecto no encontrado.</p>");
}
$current = $result->fetch_assoc();
$currentMainImage = $current['image_path'];
$currentGallery = $current['gallery_paths'];

// 1. Handle main image upload (optional)
$mainImagePath = $currentMainImage;
if (!empty($_FILES['image']['name'])) {
    // Delete old main image folder
    $projectFolder = __DIR__ . "/../images/projects/project$project_id";
    if (is_dir($projectFolder)) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($projectFolder, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $fileinfo) {
            $fileinfo->isDir() ? rmdir($fileinfo) : unlink($fileinfo);
        }
        rmdir($projectFolder);
    }

    // Create fresh folder
    mkdir($projectFolder, 0755, true);

    // Upload main image
    $mainImage = handleImageUpload('image', $projectFolder);
    if (isset($mainImage['error'])) {
        die("<p style='color:red;'>❌ Main image error: " . $mainImage['error'] . "</p>");
    }
    $mainImagePath = str_replace(__DIR__ . "/../", "", $mainImage['path']);
}

// 2. Handle gallery upload (optional)
$galleryPathsJson = $currentGallery; // default: keep existing
if (!empty($_FILES['images']['name'][0])) {
    $gallery = handleMultipleImageUpload('images', "$projectFolder/gallery");
    $galleryPaths = array_map(function($path) {
        return str_replace(__DIR__ . "/../", "", $path);
    }, $gallery['paths']);
    $galleryPathsJson = json_encode($galleryPaths);
}

// 3. Prepare SQL for UPDATE including optional images
$sql = "UPDATE projects SET
    title_es = ?, title_en = ?,
    type_es = ?, type_en = ?,
    short_description_es = ?, short_description_en = ?,
    description_es = ?, description_en = ?,
    status = ?, category = ?,
    start_date = ?, end_date = ?,
    image_path = ?, gallery_paths = ?,
    youtube_url = ?,
    open_registration = ?
WHERE id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("<p style='color:red;'>❌ Error al preparar la consulta: " . $conn->error . "</p>");
}

$open_registration = isset($_POST['open_registration']) ? 1 : 0;
$youtubeUrl = !empty($_POST['youtube_url']) ? $_POST['youtube_url'] : null;

$stmt->bind_param(
    "ssssssssssssssi",
    $_POST['title_es'],
    $_POST['title_en'],
    $_POST['type_es'],
    $_POST['type_en'],
    $_POST['short_description_es'],
    $_POST['short_description_en'],
    $_POST['description_es'],
    $_POST['description_en'],
    $_POST['status'],
    $_POST['category'],
    $_POST['start_datetime'],
    $_POST['end_datetime'],
    $mainImagePath,
    $galleryPathsJson,
    $youtubeUrl,
    $open_registration,
    $project_id
);


// Execute
if ($stmt->execute()) {
    echo "<p style='color:green;'>✅ proyecto actualizado correctamente.</p>";
    echo "<a href='projects_list.php'>Volver al formulario</a>";
} else {
    echo "<p style='color:red;'>❌ Error al actualizar: " . $stmt->error . "</p>";
}

// Close
$stmt->close();
$conn->close();
?>
