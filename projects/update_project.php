<?php
session_start();
$allowed_roles = ['admin', 'events'];
if (!isset($_SESSION['activated']) || !in_array($_SESSION['role'], $allowed_roles)) {
    http_response_code(403);
    die("Acceso no autorizado");
}

include("../assets/db.php");
include("upload_image.php");

// --- INPUT VALIDATION AND PRE-PROCESSING ---

// Make sure ID is provided
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    die("<p style='color:red;'>❌ Error: ID del proyecto no proporcionado.</p>");
}

$project_id = (int) $_POST['id'];

// Initialize non-posted variables
$open_registration = isset($_POST['open_registration']) ? 1 : 0;
$youtubeUrl = !empty($_POST['youtube_url']) ? $_POST['youtube_url'] : null;


// --- CATEGORY HANDLING ---

$submitted_categories = $_POST['categories'] ?? [];
$new_category_text = $_POST['new_category'] ?? '';

if (!empty($new_category_text)) {
    // 1. Sanitize and standardize the user input
    $clean_new_category = trim(strtolower($new_category_text));
    $clean_new_category = preg_replace('/\s+/', '-', $clean_new_category);

    // 2. Add the new category to the list
    $submitted_categories[] = $clean_new_category;
}


$category_for_db = implode(',', $submitted_categories);


// --- STATUS VALIDATION ---

$status = trim($_POST['status'] ?? '');
$allowed_status = ['idea', 'en curso', 'finalizado', 'pausado'];
if (!in_array($status, $allowed_status, true)) {
    // Fail safe, prevents SQL injection/bad data
    $status = 'idea';
}


// --- GET CURRENT DATA & INITIALIZE IMAGE PATHS ---

// Get current image paths from DB in case no new images are uploaded
$result = $conn->query("SELECT image_path, gallery_paths FROM projects WHERE id = $project_id");
if (!$result || $result->num_rows === 0) {
    die("<p style='color:red;'>❌ Proyecto no encontrado.</p>");
}
$current = $result->fetch_assoc();
$mainImagePath = $current['image_path'];
$galleryPathsJson = $current['gallery_paths'];
$projectFolder = "projects/project$project_id"; // Cloudinary subfolder


// --- IMAGE PROCESSING ---

// 1. Handle main image upload (optional)
if (!empty($_FILES['image']['name'])) {
    $mainImage = handleImageUpload('image', $projectFolder);
    if (isset($mainImage['error'])) {
        die("<p style='color:red;'>❌ Main image error: " . $mainImage['error'] . "</p>");
    }
    $mainImagePath = $mainImage['path']; // full Cloudinary URL
}


// 2. Handle gallery upload (optional) — replaces gallery if new images posted
if (!empty($_FILES['images']['name'][0])) {
    $gallery = handleMultipleImageUpload('images', "$projectFolder/gallery");
    $galleryPathsJson = json_encode($gallery['paths']);
}


// --- DATABASE UPDATE ---

// 3. Prepare SQL for UPDATE
$sql = "UPDATE projects SET
    title_es = ?, title_en = ?,
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

$stmt->bind_param(
    "ssssssssssssssi",
    $_POST['title_es'],
    $_POST['title_en'],
    $_POST['short_description_es'],
    $_POST['short_description_en'],
    $_POST['description_es'],
    $_POST['description_en'],
    $status,
    $category_for_db,
    $_POST['start_date'],
    $_POST['end_date'],
    $mainImagePath,
    $galleryPathsJson,
    $youtubeUrl,
    $open_registration,
    $project_id
);


// Execute
if ($stmt->execute()) {
    echo "<p style='color:green;'>✅ Proyecto actualizado correctamente.</p>";
    echo "<a href='projects_list.php'>Volver al formulario</a>";
} else {
    echo "<p style='color:red;'>❌ Error al actualizar: " . $stmt->error . "</p>";
}

// Close
$stmt->close();
$conn->close();
?>