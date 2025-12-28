<?php
// Show errors for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../assets/db.php');
include("../assets/head.php");


// Validate and get the project ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    die("❌ Invalid project ID");
}
$project_id = (int) $_GET['id'];

// Prepare SQL
$stmt = $conn->prepare("SELECT * FROM projects WHERE id = ?");
if (!$stmt) {
    die("❌ Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $project_id);
$stmt->execute();
$result = $stmt->get_result();
$project = $result->fetch_assoc();
$stmt->close();

if (!$project) {
    http_response_code(404);
    die("❌ Project not found");
}

$youtubeUrl = $project['youtube_url'] ?? '';

$youtubeId = null;
if (!empty($youtubeUrl)) {
    preg_match("/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&]+)/", $youtubeUrl, $matches);
    $youtubeId = $matches[1] ?? null;
}

if (!$project) {
    die("❌ Project not found");
}

$definedCategories = [
    'ai',
    'climate',
    'health',
    'education',
    'vision',
    'nlp',
    'robotics',
    'ethics'
];

?>



<!DOCTYPE html>
<html lang="en">

<body style="color:black;">
    <?php include("../assets/nav.php"); ?>

    <!-- Main Content -->
    <div class="container-fluid scroll-margin bg-dark">
        <div class="row px-3">
            <div class="col-lg-4 d-flex align-items-end justify-content-center p-0 h-100">
                <img src="<?= htmlspecialchars($project['image_path']) ?>"
                    class="card-img-top"
                    alt="Project Image"
                    style="width: 300px; height:300px; object-fit: cover; position:relative; top:32px;">
            </div>
            <div class="col-lg-8 pt-5 container py-lg-2 d-flex flex-column align-items-start justify-content-center">
                <div class="mb-3">
                    <h1 id="article-title" class="text-light display-5 fw-bold mt-2"
                        data-en="<?= htmlspecialchars($project['title_en']) ?? '' ?>"
                        data-es="<?= htmlspecialchars($project['title_es']) ?? '' ?>">
                        <?= nl2br(htmlspecialchars($project['title_es'] ?? '')) ?>
                    </h1>
                    <p class="text-decoration-none mt-2" style="color: white;"
                        data-en="<?= htmlspecialchars($project['short_description_en'] ?? '') ?>"
                        data-es="<?= htmlspecialchars($project['short_description_es'] ?? '') ?>">
                        Descripción: <?= nl2br(htmlspecialchars($project['short_description_es'] ?? '')) ?>

                        <?php if ($project['open_registration']): ?>
                            <div class="my-3">
                                <a href="/projects/project_registration.php?id=<?= $project_id ?>" 
                                class="btn btn-custom text-light px-4 fw-semibold"
                                data-en="Register for project"
                                data-es="Inscribirse al proyecto">
                                    Inscribirse al proyecto
                                </a>
                            </div>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="bg-muted row px-3 bg-light">
            <?php
            $gallery = [];
            if (!empty($project['gallery_paths'])) {
                $decoded = json_decode($project['gallery_paths'], true);
                if (is_array($decoded) && count($decoded) > 0) {
                    $gallery = $decoded;
                }
            }
            ?>

            

            <!-- Sidebar -->
            <div class="col-lg-4 pt-5 bg-white d-flex justify-content-center align-items-start">
                <div style="width:70%;">
                    <?php
                    $start = (!empty($project['start_date']) && $project['start_date'] !== '0000-00-00')
                        ? new DateTime($project['start_date'])
                        : null;

                    $end = (!empty($project['end_date']) && $project['end_date'] !== '0000-00-00')
                        ? new DateTime($project['end_date'])
                        : null;

                    $category = array_map('trim', explode(',', $project['category']));
                    ?>
                    <div id="article-date" class="mb-3">
                    <i class="fas fa-calendar me-2"></i>
                        <?php
                        if ($start && $end && $start->format('Y-m-d') !== $end->format('Y-m-d')) {
                            echo '<strong>' . $start->format('d/m/Y') . '</strong> - <strong>' . $end->format('d/m/Y') . '</strong>';
                        } elseif ($start) {
                            echo '<strong>' . $start->format('d/m/Y') . '</strong>';
                        }
                        ?>
                    </div>

                    <?php if (!empty($category)): ?>
                        <div class="mb-2">
                          <?php foreach ($category as $cat): ?>
                            <?php

                            $cat_slug = htmlspecialchars(trim($cat));
                            $is_defined = in_array($cat_slug, $definedCategories);

                            if ($is_defined) {
                                $style = '';
                                $class = 'category-' . $cat_slug;
                            } else {
                                // Código para categorías "Otras" (se mantiene tu código de color aleatorio)
                                $hue = rand(0, 360);
                                $saturation = rand(50, 80);
                                $lightness = rand(40, 60);
                                $randomColor = "hsl($hue, $saturation%, $lightness%)";
                                $style = 'style="background-color: ' . $randomColor . '"';
                                $class = 'category-other'; // Añadir una clase genérica para "Otras"
                            }
                            ?>

                            <span class="category-badge <?= $class ?>" <?= $style ?>>
                              <?= $cat_slug ?>
                            </span>

                          <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="mt-3 mb-5">
                    <!-- Future implementation: add authors to DB and display them here 
                        <div class="btn-group">
                            Made by: 
                        </div> -->
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-lg-8">
                <?php if (!empty($gallery)): ?>
                <div class="pt-2 d-flex align-items-center justify-content-center" style="width:100%;">
                    <div id="projectGalleryCarousel" class="carousel slide mt-4" data-bs-ride="carousel" style="width:80%;">
                        <!-- Indicators -->
                        <div class="carousel-indicators">
                            <?php foreach ($gallery as $index => $path): ?>
                                <button type="button" data-bs-target="#projectGalleryCarousel" data-bs-slide-to="<?= $index ?>"
                                    class="<?= $index === 0 ? 'active' : '' ?>"
                                    aria-current="<?= $index === 0 ? 'true' : 'false' ?>"
                                    aria-label="Slide <?= $index + 1 ?>"></button>
                            <?php endforeach; ?>
                        </div>

                        <!-- Slides -->
                        <div class="carousel-inner">
                            <?php foreach ($gallery as $index => $path): ?>
                                <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                    <img src="<?= htmlspecialchars($path) ?>"
                                        class="d-block w-100"
                                        alt="Gallery image <?= $index + 1 ?>"
                                        style="max-height:400px; object-fit:cover;">
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Controls -->
                        <button class="carousel-control-prev" type="button" data-bs-target="#projectGalleryCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#projectGalleryCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                </div>
            <?php endif; ?>

                <section id="article-body" style="padding: 2rem; white-space: pre-line;"
                    data-en="<?= htmlspecialchars_decode($project['description_en']) ?>"
                    data-es="<?= htmlspecialchars_decode($project['description_es']) ?>">
                    <?= nl2br(htmlspecialchars_decode($project['description_es'])) ?>

                </section>

            <?php if (!empty($youtubeId)): ?>
                <div class="my-4 d-flex justify-content-center align-items-center" style="height: 320px;">
                    <iframe width="420" height="315"
                            src="https://www.youtube.com/embed/<?= htmlspecialchars($youtubeId) ?>?autoplay=0"
                            title="Video del proyecto"
                            frameborder="0"
                            allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen>
                    </iframe>
                </div>
            <?php endif; ?>

            </div>
        </div>
    </div>

    <?php include('../assets/footer.php'); ?>

    <script src="../js/navbar.js"></script>
</body>

</html>