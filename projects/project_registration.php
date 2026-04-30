<?php
include('../assets/csrf.php');
include('../assets/db.php');
include("../assets/head.php");

// Validate and get the project ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("❌ Invalid project ID");
}
$project_id = (int) $_GET['id'];

// Prepare SQL to get project details
$stmt = $conn->prepare("SELECT title_es, image_path FROM projects WHERE id = ?");
if (!$stmt) {
    error_log("DB prepare failed in " . __FILE__ . ": " . $conn->error);
    http_response_code(500);
    die("Error interno del servidor.");
}
$stmt->bind_param("i", $project_id);
$stmt->execute();
$result = $stmt->get_result();
$project = $result->fetch_assoc();
$stmt->close();

if (!$project) {
    die("❌ project not found");
}
?>

<!DOCTYPE html>
<html lang="en">

<body class="d-flex flex-column min-vh-100">

    <?php include("../assets/nav.php"); ?>

    <div class="container scroll-margin">
        <div class="text-center mb-5 px-3 px-md-5">
            <h2 class="fw-bold mb-4" style="color: var(--muted);" translation-key="proj_reg_title">
                Inscripción al proyecto
            </h2>
            <h4 class="fw-bold" style="color: var(--primary);"><?= htmlspecialchars($project['title_es']) ?></h4>
            <div class="mx-auto mt-3 mb-4"
                style="width:60px; height:3px; background: var(--primary); border-radius:2px;"></div>
            <!-- project image if exists -->
            <?php if (!empty($project['image_path'])): ?>
                <img src="<?= htmlspecialchars($project['image_path']) ?>"
                    alt="<?= htmlspecialchars($project['title_es']) ?>" class="img-fluid rounded mb-4"
                    style="max-width: 80%; height: auto;">
            <?php endif; ?>
            <p class="text-muted" translation-key="proj_reg_subtitle">
                Rellena el formulario para solicitar tu plaza en el proyecto.
            </p>
        </div>

        <section class="container-fluid mb-5">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="border-0 form-card no-hover">
                        <div class="card-body bg-muted p-4">

                            <?php
                            if (isset($_GET['success']))
                                echo '<div class="alert alert-success" translation-key="proj_reg_success">¡Gracias! Te has inscrito correctamente.</div>';
                            if (isset($_GET['error_duplicate']))
                                echo '<div class="alert alert-danger" translation-key="proj_reg_err_dup">Ya existe una inscripción con este correo para este proyecto.</div>';
                            if (isset($_GET['error_validation']))
                                echo '<div class="alert alert-danger" translation-key="proj_reg_err_val">Por favor, completa todos los campos correctamente.</div>';
                            ?>

                            <form method="POST" action="/processing/register_project.php">
                                <input type="hidden" name="project_id" value="<?= $project_id ?>">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>">

                                <div class="mb-3">
                                    <label for="name" class="form-label" style="color: black" translation-key="proj_reg_label_name">Nombre y apellidos</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        placeholder="Michael Scott" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label" style="color: black" translation-key="proj_reg_label_email">Correo electrónico</label>
                                    <input type="email" class="form-control" id="email" name="email"
                                        placeholder="name@example.com" required>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="consent" name="consent"
                                        required>
                                    <label class="form-check-label form-text" for="consent">
                                        <span translation-key="proj_reg_consent">Doy mi consentimiento para que AISC Madrid almacene mis datos para la gestión de este proyecto.</span>
                                    </label>
                                    <a class="form-check-label form-text" href="../terms_conditions.php" target="_blank" translation-key="proj_reg_terms">
                                        (Leer términos y condiciones)
                                    </a>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary form-btn fw-semibold" translation-key="proj_reg_submit">Inscribirme</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <?php include('../assets/footer.php'); ?>

    <script src="/js/navbar.js"></script>
</body>

</html>