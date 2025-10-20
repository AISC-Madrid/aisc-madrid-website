<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['activated']) || $_SESSION['role'] !== 'admin') {
    header("Location: projects/login.php");
    exit();
}


include('../assets/db.php'); // Your $conn mysqli connection

// Initialize variables for the form
$title_es = $title_en = $type_es = $type_en = '';
$description_es = $description_en = $short_description_es = $short_description_en = '';
$start_date = $end_date = $image_path = '';
$status = $category = $youtube_url = '';


// Check if an ID is passed
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $project_id = (int)$_GET['id'];

    $stmt = $conn->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $project = $result->fetch_assoc();

    if ($project) {
        $title_es = $project['title_es'];
        $title_en = $project['title_en'];
        // Descripción breve (para listados o cards)
        $short_description_es = $project['short_description_es'];
        $short_description_en = $project['short_description_en'];
        $description_es = $project['description_es'];
        $description_en = $project['description_en'];
        $status = $project['status'];            // 'idea', 'en curso', 'finalizado', 'pausado'
        $category = $project['category'];        // 'educación', 'salud', 'tecnología', etc.
        $start_date = $project['start_date'];
        $end_date = $project['end_date'];
        $image_path = $project['image_path'];
        $youtube_url = $project['youtube_url'];
        $open_registration = $project['open_registration'];
    }
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Crear/Editar proyecto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container my-5">
        <div class="row wrap">
            <h1 class="mb-4"><?= isset($project_id) ? 'Editar Proyecto' : 'Crear Nuevo Proyecto' ?></h1>

            <form action="<?= isset($project_id) ? 'update_project.php' : 'insert_project.php' ?>" method="POST" enctype="multipart/form-data">
                <?php if (isset($project_id)): ?>
                    <input type="hidden" name="id" value="<?= $project_id ?>">
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

                    <!-- Spanish short description -->
                    <div class="mb-3 col-6">
                        <label class="form-label">Descripción Corta (Español)</label>
                        <textarea name="short_description_es" class="form-control" rows="4"><?= htmlspecialchars($short_description_es) ?></textarea>
                    </div>

                    <!-- English short description -->
                    <div class="mb-3 col-6">
                        <label class="form-label">Short Description (English)</label>
                        <textarea name="short_description_en" class="form-control" rows="4"><?= htmlspecialchars($short_description_en) ?></textarea>
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

                    <!-- Status -->
                    <div class = "mb-3 col-6">
                        <label class="form-label">Estado del Proyecto</label>
                        <select class="form-control" name="status" required>
                            <option value="" disabled selected>Selecciona una opción...</option>
                            <option value="idea">Idea</option>
                            <option value="en curso">En Curso</option>
                            <option value="finalizado">Finalizado</option>
                            <option value="pausado">Pausado</option>
                        </select>
                    </div>

                    <!-- Category -->
                    <div class = "mb-3 col-6">
                        <label class="form-label">Categoria del Proyecto</label>
                        <select class="form-control" name="categories" multiple size="5">
                            <option value="ai">Inteligencia Artificial</option>
                            <option value="climate">Cambio Climático</option>
                            <option value="health">Salud</option>
                            <option value="education">Educación</option>
                            <option value="vision">Visión por computador</option>
                            <option value="nlp">Procesamiento del lenguaje natural</option>
                            <option value="robotics">Robótica</option>
                            <option value="ethics">Ética y regulación</option>
                        </select>
                    </div>

                    <!-- Start date/time -->
                    <div class="mb-3 col-6">
                        <label class="form-label">Fecha de inicio</label>
                        <input type="date" name="start_date" class="form-control" required value="<?= $start_date ?>">
                    </div>

                    <!-- End date/time -->
                    <div class="mb-3 col-6">
                        <label class="form-label">Fecha de fin (si hay)</label>
                        <input type="date" name="end_date" class="form-control" value="<?= $end_date ?>">
                    </div>

                    <!-- Image upload -->
                    <div class="mb-3">
                        <label class="form-label">Imagen principal del proyecto</label>
                        <input type="file" name="image" class="form-control" accept="image/*" <?= isset($project_id) ? '' : 'required' ?>>
                    </div>

                    <!-- Multiple project photos -->
                    <div class="mb-3">
                        <label class="form-label">Fotos del projecto</label>
                        <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
                    </div>

                    <!-- Youtube URL -->
                    <div class="mb-3">
                        <label class="form-label">YT URL (empty if none)</label>
                        <input type="url" name="youtube_url" class="form-control" 
                        value="<?= htmlspecialchars($youtube_url ?? '') ?>">
                    </div>

                    <!-- Open Registration -->
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="open_registration" class="form-check-input" id="open_registration" value="1" <?= !empty($requires_registration) && $requires_registration ? 'checked' : '' ?>>
                        <label class="form-check-label" for="open_registration">Inscripciones Abiertas</label>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="btn btn-primary col-6">
                        <?= isset($project_id) ? 'Actualizar Proyecto' : 'Guardar Proyecto' ?>
                    </button>
        </div>
        </form>


    </div>
    </div>
</body>

</html>