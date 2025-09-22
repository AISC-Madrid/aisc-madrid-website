<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Not logged in, redirect to login page
    header("Location: login.php");
    exit();
}


include('../assets/db.php'); // Your $conn mysqli connection

// Initialize variables for the form
$title_es = $title_en = $type_es = $type_en = '';
$description_es = $description_en = $location_es = '';
$start_datetime = $end_datetime = $image_path = $google_calendar_url = '';
$speaker = '';


// Check if an ID is passed
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $event_id = (int)$_GET['id'];

    $stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $event = $result->fetch_assoc();

    if ($event) {
        $title_es = $event['title_es'];
        $title_en = $event['title_en'];
        $type_es = $event['type_es'];
        $type_en = $event['type_en'];
        $speaker = $event['speaker'];
        $description_es = $event['description_es'];
        $description_en = $event['description_en'];
        $location_es = $event['location'];
        $start_datetime = $event['start_datetime'];
        $end_datetime = $event['end_datetime'];
        $image_path = $event['image_path'];
        $google_calendar_url = $event['google_calendar_url'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Crear/Editar evento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container my-5">
        <div class="row wrap">
            <h1 class="mb-4"><?= isset($event_id) ? 'Editar Evento' : 'Crear Nuevo Evento' ?></h1>

            <form action="<?= isset($event_id) ? 'update_event.php' : 'insert_event.php' ?>" method="POST" enctype="multipart/form-data">
                <?php if (isset($event_id)): ?>
                    <input type="hidden" name="id" value="<?= $event_id ?>">
                <?php endif; ?>

                    <div class="row justify-content-center">
                    <!-- Spanish title -->
                    <div class="mb-3 col-6">
                        <label class="form-label">Título (Español)</label>
                        <input type="text" name="title_es" class="form-control" required value="<?= htmlspecialchars($title_es) ?>">
                    </div>

                    <!-- English title -->
                    <div class="mb-3 col-6">
                        <label class="form-label">Title (English)</label>
                        <input type="text" name="title_en" class="form-control" required value="<?= htmlspecialchars($title_en) ?>">
                    </div>

                    <!-- Spanish type -->
                    <div class="mb-3 col-6">
                        <label class="form-label">Tipo (Español)</label>
                        <input type="text" name="type_es" class="form-control" value="<?= htmlspecialchars($type_es) ?>">
                    </div>

                    <!-- English type -->
                    <div class="mb-3 col-6">
                        <label class="form-label">Type (English)</label>
                        <input type="text" name="type_en" class="form-control" value="<?= htmlspecialchars($type_en) ?>">
                    </div>

                    <!-- Speaker -->
                    <div class="mb-3">
                        <label class="form-label">Speaker</label>
                        <input type="text" name="speaker" class="form-control" value="<?= htmlspecialchars($speaker) ?>">
                    </div>

                    <!-- Spanish description -->
                    <div class="mb-3 col-6">
                        <label class="form-label">Descripción (Español)</label>
                        <textarea name="description_es" class="form-control" rows="4"><?= htmlspecialchars($description_es) ?></textarea>
                    </div>

                    <!-- English description -->
                    <div class="mb-3 col-6">
                        <label class="form-label">Description (English)</label>
                        <textarea name="description_en" class="form-control" rows="4"><?= htmlspecialchars($description_en) ?></textarea>
                    </div>

                    <!-- Location -->
                    <div class="mb-3">
                        <label class="form-label">Ubicación</label>
                        <input type="text" name="location_es" class="form-control" value="<?= htmlspecialchars($location_es) ?>">
                    </div>

                    <!-- Start date/time -->
                    <div class="mb-3 col-6">
                        <label class="form-label">Fecha y hora de inicio</label>
                        <input type="datetime-local" name="start_datetime" class="form-control" required value="<?= $start_datetime ?>">
                    </div>

                    <!-- End date/time -->
                    <div class="mb-3 col-6">
                        <label class="form-label">Fecha y hora de fin</label>
                        <input type="datetime-local" name="end_datetime" class="form-control" required value="<?= $end_datetime ?>">
                    </div>

                    <!-- Image upload -->
                    <div class="mb-3">
                        <label class="form-label">Imagen principal del evento</label>
                        <input type="file" name="image" class="form-control" accept="image/*" <?= isset($event_id) ? '' : 'required' ?>>
                    </div>

                    <!-- Multiple event photos -->
                    <div class="mb-3">
                        <label class="form-label">Fotos del evento</label>
                        <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
                    </div>

                    <!-- Google Calendar URL -->
                    <div class="mb-3">
                        <label class="form-label">URL Google Calendar</label>
                        <input type="url" name="google_calendar_url" class="form-control" value="<?= htmlspecialchars($google_calendar_url) ?>">
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="btn btn-primary col-6">
                        <?= isset($event_id) ? 'Actualizar Evento' : 'Guardar Evento' ?>
                    </button>
        </div>
        </form>


    </div>
    </div>
</body>

</html>