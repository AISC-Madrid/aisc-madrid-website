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

// Make sure ID is provided
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    die("<p style='color:red;'>❌ Error: ID del evento no proporcionado.</p>");
}

$event_id = (int) $_POST['id'];

// Get current image paths from DB in case no new images are uploaded
$result = $conn->query("SELECT status, image_path, gallery_paths FROM events WHERE id = $event_id");
if (!$result || $result->num_rows === 0) {
    die("<p style='color:red;'>❌ Evento no encontrado.</p>");
}
$current = $result->fetch_assoc();
$currentMainImage = $current['image_path'];
$currentGallery = $current['gallery_paths'];

// "draft" saves a draft and "unpublish" turns a published event back into one; anything else publishes
$action = $_POST['action'] ?? '';
$status = in_array($action, ['draft', 'unpublish'], true) ? 'draft' : 'published';

if (trim($_POST['title_es'] ?? '') === '') {
    die("<p style='color:red;'>❌ El título en español es obligatorio, incluso para un borrador.</p>");
}

if ($status === 'published') {
    $missing = event_publish_errors($_POST, !empty($currentMainImage) || !empty($_FILES['image']['name']));
    if ($missing) {
        event_die_missing($missing);
    }
}

// 1. Handle main image upload (optional)

$eventFolder = "events-workshops/$event_id"; // S3 media bucket prefix
$mainImagePath = $currentMainImage;
if (!empty($_FILES['image']['name'])) {
    $mainImage = handleImageUpload('image', $eventFolder);
    if (isset($mainImage['error'])) {
        die("<p style='color:red;'>❌ Main image error: " . $mainImage['error'] . "</p>");
    }
    $mainImagePath = $mainImage['path']; // bucket key
}

// 2. Handle gallery upload (optional) — replaces gallery if new images posted
$galleryPathsJson = $currentGallery;
if (!empty($_FILES['images']['name'][0])) {
    $gallery = handleMultipleImageUpload('images', "$eventFolder/gallery");
    $galleryPathsJson = json_encode($gallery['paths']);
}

// 3. Prepare SQL for UPDATE including optional images
$sql = "UPDATE events SET
    status = ?,
    title_es = ?, title_en = ?,
    type_es = ?, type_en = ?,
    speaker = ?,
    company = ?,
    description_es = ?, description_en = ?,
    location = ?,
    start_datetime = ?, end_datetime = ?,
    image_path = ?, gallery_paths = ?,
    youtube_url = ?,
    requires_registration = ?,
    reminder_enabled = ?,
    reminder_days_before = ?,
    reminder_custom_text = ?
WHERE id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("<p style='color:red;'>❌ Error al preparar la consulta: " . $conn->error . "</p>");
}

// ✅ CORRECCIÓN: Guardar el valor del checkbox en una variable primero
$requires_registration = isset($_POST['requires_registration']) ? 1 : 0;
$reminder_enabled = isset($_POST['reminder_enabled']) ? 1 : 0;
$reminder_days_before = isset($_POST['reminder_days_before']) ? (int)$_POST['reminder_days_before'] : 2;
require_once __DIR__ . '/../mails/reminder_events/reminder_text.php';
$reminder_custom_text = reminder_text_to_store($_POST['reminder_custom_text'] ?? '');
$youtubeUrl = !empty($_POST['youtube_url']) ? $_POST['youtube_url'] : null;
$title_en = trim($_POST['title_en'] ?? '') ?: null;
$company = trim($_POST['company'] ?? '') ?: null;

// Convert form datetimes from Madrid timezone to UTC for storage (drafts may leave them empty)
$start_utc = event_form_datetime_utc($_POST['start_datetime'] ?? null);
$end_utc = event_form_datetime_utc($_POST['end_datetime'] ?? null);


$stmt->bind_param(
    "sssssssssssssssiiisi",
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
    $mainImagePath,
    $galleryPathsJson,
    $youtubeUrl,
    $requires_registration,
    $reminder_enabled,
    $reminder_days_before,
    $reminder_custom_text,
    $event_id
);

// Execute
if ($stmt->execute()) {
    $messages = [
        'draft'     => '✅ Borrador guardado. No será visible en la web hasta que lo publiques.',
        'unpublish' => '✅ Evento despublicado: vuelve a ser un borrador y ya no es visible en la web.',
        'publish'   => '✅ Evento publicado correctamente.',
    ];
    echo "<p style='color:green;'>" . ($messages[$action] ?? '✅ Evento actualizado correctamente.') . "</p>";
    echo "<a href='create_event.php?id=$event_id'>Seguir editando</a> · ";
    echo "<a href='events_list.php'>Volver a la lista de eventos</a>";
} else {
    echo "<p style='color:red;'>❌ Error al actualizar: " . $stmt->error . "</p>";
}

// Close
$stmt->close();
$conn->close();
?>