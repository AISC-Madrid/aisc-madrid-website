<!DOCTYPE html>
<html lang="en">
<?php

/*
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['activated']) || $_SESSION['role'] !== 'admin') {
    header("Location: projects/login.php");
    exit();
}

*/
// Enable error reporting only in development (disable in production)
if (getenv('APP_ENV') === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}
include("assets/db.php");

// Get current date
$now = date("Y-m-d");

// Retrieve all projects ordered by start_date
$result_projects = $conn->query("SELECT * FROM projects ORDER BY start_date DESC");

// Separate future and past projects

// Fetch all projects into an array
$all_projects = [];
while ($row = $result_projects->fetch_assoc()) {
    $all_projects[] = $row;
}

?>

<?php
include("assets/head.php");
?>

<body>

  <?php
  include("assets/nav.php");
  ?>

  <!-- Main container -->
  <div class="container-fluid d-flex flex-column align-items-center justify-content-center">
    
    <!-- Header section -->
    <section class="section" id="projects-header">
      <div class="container scroll-margin">
        <div class="row align-items-center m-5">

          <div class="col-md-7 mb-4 mb-md-0 mb-3"> 
            <div class="text-center text-md-start px-3 px-md-5"> 
              <h2 class="fw-bold mb-4" style="color: var(--muted);" data-en="Our Projects" data-es="Nuestros Proyectos">
                Nuestros Proyectos
              </h2>

              <div class="mx-auto mx-md-0 mb-4" style="width:60px; height:3px; background: var(--primary); border-radius:2px;"></div>
              
              <h6 class="lh-lg text-muted mx-auto mx-md-0" style="max-width: 700px"
                data-en="At AISC Madrid we believe in the power of artificial intelligence to transform the world. Our projects reflect our commitment to innovation, education, and positive social impact."
                data-es="En AISC Madrid creemos en el poder de la inteligencia artificial para transformar el mundo. Nuestros proyectos reflejan nuestro compromiso con la innovación, la educación y el impacto social positivo.">
                En AISC Madrid creemos en el poder de la inteligencia artificial para transformar el mundo.
                Nuestros proyectos reflejan nuestro compromiso con la innovación, la educación y el impacto social positivo.
              </h6>
            </div>
          </div>

          <div class="col-md-5 text-center"> 
            <img src="images/projects/projects-main.svg" alt="AI ML Projects" class="img-fluid">
          </div>

        </div>
      </div>
    </section>

    <!-- Projects section -->

    <section class="section w-100 px-3 px-md-5" id="projects">
      <div class="container-fluid">
        <div class="mx-auto mb-4" style="width:60px; height:3px; background: var(--primary); border-radius:2px;"></div>


      <!-- Filter Buttons Row -->
      <div class="row mb-3">
        <div class="col-12 d-flex justify-content-end align-items-center gap-2">
          <button id="order-btn" class="active" data-filter="order" data-order="desc" aria-pressed="false" aria-label="orden" title="Ordenar"></button>
        </div>
      </div>

      <div class="project-group row g-4" style="width:100%; magin-bottom:30px; margin-top:30px;">
        <?php foreach ($all_projects as $project): ?>
          <?php
            $id         = (int)$project['id'];
            $title_es   = htmlspecialchars($project['title_es'] ?? '');
            $title_en   = htmlspecialchars($project['title_en'] ?? '');
            $image_path = htmlspecialchars($project['image_path'] ?? '');
            $sort_timestamp = strtotime($project['start_date']);
            $start_date = !empty($project['start_date']) ? date("d/m/Y", strtotime($project['start_date'])) : '';
            $desc_es    = htmlspecialchars($project['short_description_es'] ?? '');
            $desc_en    = htmlspecialchars($project['short_description_en'] ?? '');
            $category   = array_map('trim', explode(',', $project['category']));
          ?>
            <div class="project-card col-12 col-md-6" date="<?=$sort_timestamp?>">
            <a href="/projects/project.php?id=<?= $id ?>" class="text-decoration-none text-reset">
              <div class="card w-100 h-100 shadow-sm horizontal-card position-relative">
                <div class="row g-0 align-items-stretch h-100">

                  <!-- LEFT: Info -->
                  <div class="col-12 col-sm-7">
                    <div class="h-100 d-flex flex-column p-3 p-md-4">
                      <h5 class="fw-bold mb-2"
                          data-en="<?= $title_en ?>"
                          data-es="<?= $title_es ?>">
                        <?= $title_es ?>
                      </h5>

                      <?php if (!empty($category)): ?>
                        <div class="mb-2">
                          <?php foreach ($category as $cat): ?>
                            <span class="category-badge category-<?=htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></span>
                          <?php endforeach; ?>
                        </div>
                      <?php endif; ?>

                      <?php if ($start_date): ?>
                        <div class="mb-2 text-muted">
                          <i class="fas fa-calendar me-2"></i>
                          <strong><?= $start_date ?></strong>
                        </div>
                      <?php endif; ?>

                      <?php if ($desc_es): ?>
                        <p class="mb-0 flex-grow-1"
                          data-en="<?= $desc_en ?>"
                          data-es="<?= $desc_es ?>">
                          <?= $desc_es ?>
                        </p>
                      <?php endif; ?>

                      <div class="mt-3">
                        <span class="btn btn-sm btn-outline-primary"
                              data-es="Saber más" data-en="More Information">Saber más</span>
                      </div>
                    </div>
                  </div>

                  <!-- RIGHT: Image -->
                  <div class="col-12 col-sm-5 img-side">
                    <?php if ($image_path): ?>
                      <img src="<?= $image_path ?>" alt="<?= $title_es ?>"
                          style="width:100%; height:100%; object-fit:cover; border-radius:10px;">
                    <?php else: ?>
                      <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-light text-muted">
                        Sin imagen
                      </div>
                    <?php endif; ?>
                  </div>

                </div>
              </div>
            </a>
            </div>
          <?php endforeach; ?>
      </div>
    </section>
  </div>






<?php $conn->close(); ?>


  <?php include('assets/footer.php'); ?>
  <?php include("assets/nav.php"); ?>


  <script src="js/language.js"></script>
  <script src="js/index.js" defer></script>
  <script src="js/navbar.js" defer></script>
  <script src="js/language.js" defer></script>
  <script src="js/projects.js" defer></script>  
  <!-- Bootstrap Bundle JS (includes Popper) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>                   

</body>
</html>

<style>
#order-btn{
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
  color: white;
  

  }
.filters {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
  color: red;
}

button {
  padding: 8px 16px;
  border: 1px solid #ccc;
  background-color: #ffffffff;
  border-radius: 8px;
  cursor: pointer;
  transition: 0.2s;
}

button.active {
  background-color: #EB178E;
  color: white;
}

.item {
  padding: 10px;
  margin-bottom: 5px;
  border: 1px solid #ddd;
  border-radius: 6px;
}

.hidden {
  display: none;
}
</style>
