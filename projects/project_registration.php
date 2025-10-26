<?php
// Show errors for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
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
    die("❌ Prepare failed: " . $conn->error);
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
        <h2 class="fw-bold mb-4" style="color: var(--muted);" data-en="project Registration" data-es="Inscripción al proyecto">
            Inscripción al proyecto
        </h2>
        <h4 class="fw-bold" style="color: var(--primary);"><?= htmlspecialchars($project['title_es']) ?></h4>
        <div class="mx-auto mt-3 mb-4" style="width:60px; height:3px; background: var(--primary); border-radius:2px;"></div>
        <!-- project image if exists -->
        <?php if (!empty($project['image_path'])): ?>
        <img src="<?= htmlspecialchars($project['image_path']) ?>" alt="<?= htmlspecialchars($project['title_es']) ?>" class="img-fluid rounded mb-4" style="max-width: 80%; height: auto;">
        <?php endif; ?>
        <p class="text-muted" data-en="Fill out the form to secure your spot at the project." data-es="Rellena el formulario para asegurar tu plaza en el proyecto.">
            Rellena el formulario para solicitar tu plaza en el proyecto.</p>
    </div>

    <section class="container-fluid mb-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="border-0 form-card no-hover">
                    <div class="card-body bg-muted p-4">
                        
                        <?php
                        // Mensajes de éxito o error
                        if (isset($_GET['success'])) echo '<div class="alert alert-success">¡Gracias! Te has inscrito correctamente.</div>';
                        if (isset($_GET['error_duplicate'])) echo '<div class="alert alert-danger">Ya existe una inscripción con este correo para este proyecto.</div>';
                        if (isset($_GET['error_validation'])) echo '<div class="alert alert-danger">Por favor, completa todos los campos correctamente.</div>';
                        ?>

                        <form method="POST" action="/processing/register_project.php">
                            <input type="hidden" name="project_id" value="<?= $project_id ?>">

                            <div class="mb-3">
                                <label for="name" class="form-label" style="color: black" data-en="Full name" data-es="Nombre y apellidos">Nombre y apellidos</label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Michael Scott" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label" style="color: black" data-en="Email" data-es="Correo electrónico">Correo electrónico</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="consent" name="consent" required>
                                <label class="form-check-label form-text" for="consent" data-en="I give my consent for AISC Madrid to store my data for the management of this project." data-es="Doy mi consentimiento para que AISC Madrid almacene mis datos para la gestión de este proyecto.">
                                    Doy mi consentimiento para que AISC Madrid almacene mis datos para la gestión de este proyecto.
                                </label>
                                <a class="form-check-label form-text" href="../terms_conditions.php" target="_blank" data-en="(Read terms and conditions)" data-es="(Leer términos y condiciones)">
                                    (Leer términos y condiciones)
                                </a>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary form-btn fw-semibold" data-en="Register" data-es="Inscribirme">Inscribirme</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include('../assets/footer.php'); ?>

<script src="/js/language.js"></script>
<script src="/js/navbar.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>