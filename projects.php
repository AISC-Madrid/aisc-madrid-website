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

$all_categories_used = [];
foreach ($all_projects as $project) {
    if (!empty($project['category'])) {
        $categories_in_project = array_map('trim', explode(',', $project['category']));
        $all_categories_used = array_merge($all_categories_used, $categories_in_project);
    }
}
// Obtener categorías que no están en $definedCategories
$other_categories = array_diff(array_unique($all_categories_used), $definedCategories);

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

          <div class="col-md-7 mb-4 mb-md-0 mb-3 text-center text-md-start px-3 px-md-5"> 
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

          <div class="col-md-5 text-center"> 
            <img src="images/projects/projects-main.svg" alt="AI ML Projects" class="img-fluid">
          </div>
        </div>
      </div>
    </section>

    <!-- Projects section -->

    <section class="section w-100 px-3 px-md-5" id="projects">
      <div class="container-fluid">
        <div class="mx-auto mb-4" style="width:60px; height:3px; background: var(--primary); border-radius:2px;">
        </div>


        <!-- Filter Buttons Row -->
        <div class="row mb-3 align-items-center">
      
            <div class="col-6 col-md-8 d-flex justify-content-start align-items-center gap-2">
                
                <button class="btn btn-custom dropdown-toggle" 
                        type="button" 
                        id="categoryFilterDropdown" 
                        data-bs-toggle="dropdown" 
                        aria-expanded="false" 
                        data-es="Filtrar Categorías" 
                        data-en="Filter Categories">
                    Filtrar Categorías
                </button>
                
                <ul class="dropdown-menu category-menu" aria-labelledby="categoryFilterDropdown">
                    <li>
                        <button class="dropdown-item filter-btn-cat active" data-filter="all">
                            Todas las Categorías
                        </button>
                    </li>
                    <li><hr class="dropdown-divider"></li>

                    <?php foreach ($definedCategories as $cat): ?>
                        <li>
                            <button class="dropdown-item filter-btn-cat" data-filter="<?= htmlspecialchars($cat) ?>">
                                <?= htmlspecialchars(ucfirst($cat)) ?>
                            </button>
                        </li>
                    <?php endforeach; ?>
                    
                    <?php if (!empty($other_categories)): ?>
                        <li>
                            <button class="dropdown-item filter-btn-cat" data-filter="other">
                                Otras
                            </button>
                        </li>
                    <?php endif; ?>
                </ul>
                
            </div>

            <div class="col-6 col-md-4 d-flex justify-content-end align-items-center gap-2">
                <button id="order-btn" class="btn btn-custom" data-filter="order" data-order="desc" aria-pressed="false" aria-label="orden" title="Ordenar"></button>
            </div>
        </div>

        <div class="row project-group g-4" style="width:100%;">
          <?php foreach ($all_projects as $project): ?>
            <?php
              // ... Variables PHP de cada proyecto (id, title_es, category_classes, etc.) ...
              $id          = (int)$project['id'];
              $title_es    = htmlspecialchars($project['title_es'] ?? '');
              $title_en    = htmlspecialchars($project['title_en'] ?? '');
              $image_path  = htmlspecialchars($project['image_path'] ?? '');
              $sort_timestamp = strtotime($project['start_date']);
              $start_date = !empty($project['start_date']) ? date("d/m/Y", strtotime($project['start_date'])) : '';
              $desc_es     = htmlspecialchars($project['short_description_es'] ?? '');
              $desc_en     = htmlspecialchars($project['short_description_en'] ?? '');
              $category    = array_map('trim', explode(',', $project['category']));

              // Clases de categorías para el filtro
              $category_classes = '';
              foreach ($category as $cat) {
                  $cat_slug = htmlspecialchars(trim($cat));
                  if (in_array($cat_slug, $definedCategories)) {
                      $category_classes .= ' cat-' . $cat_slug;
                  } else {
                      $category_classes .= ' cat-other';
                  }
              }
            ?>
          
                <div class="project-card col-12 col-lg-6 border-0 <?= $category_classes ?>" date="<?=$sort_timestamp?>">
                  <a href="/projects/project.php?id=<?= $id ?>" class="text-decoration-none text-reset">
                    <div class="card horizontal-card w-100 h-100 position-relative shadow-sm">
                      <div class="row g-0 align-items-stretch h-100">

                        <div class="col-12 col-sm-7 d-flex flex-column p-3 p-md-4 h-100"> 
                          
                            <h5 class="fw-bold mb-2"
                                data-en="<?= $title_en ?>"
                                data-es="<?= $title_es ?>">
                              <?= $title_es ?>
                            </h5>

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
                                      $class = 'category-other'; 
                                  }
                                  ?>

                                  <span class="category-badge <?= $class ?>" <?= $style ?>>
                                    <?= $cat_slug ?>
                                  </span>
                                <?php endforeach; ?>
                              </div>
                            <?php endif; ?>
                            
                            <?php if ($start_date): ?>
                              <div class="mb-2 text-muted">
                                <small>
                                  <i class="fas fa-calendar me-2"></i>
                                  <?= $start_date ?>
                                </small>
                              </div>
                            <?php endif; ?>

                            <?php if ($desc_es): ?>
                              <p class="mb-0 flex-grow-1 project-description"
                                  data-en="<?= $desc_en ?>"
                                  data-es="<?= $desc_es ?>">
                                  <?= $desc_es ?>
                              </p>
                            <?php endif; ?> 
                            
                            <div class="card-footer bg-transparent border-0 p-0 mt-3">
                                <span class="btn btn-custom btn-sm btn-outline-primary"
                                    data-es="Saber más" data-en="More Information">Saber más</span>
                            </div>
                        </div> <div class="col-12 col-sm-5 img-side">
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
      </div>
    </section>
  </div>






<?php $conn->close(); ?>


  <?php include('assets/footer.php'); ?>
  <?php include("assets/nav.php"); ?>


  <script src="js/index.js"></script>
  <script src="js/navbar.js"></script>
  <script src="js/language.js"></script>
  <script src="js/projects.js"></script>  
  <!-- Bootstrap Bundle JS (includes Popper) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>                   

</body>
</html>

<style>
#order-btn{
  display: flex;
  gap: 10px;
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

.item {
  padding: 10px;
  margin-bottom: 5px;
  border: 1px solid #ddd;
  border-radius: 6px;
}

</style>
