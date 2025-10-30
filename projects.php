<!DOCTYPE html>
<html lang="en">
<?php

// Enable error reporting only in development (disable in production)
if (getenv('APP_ENV') === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}
include("assets/db.php");

// Get current date
$now = date("Y-m-d");

// Retrieve all projects ordered by start_date
$result_projects = $conn->query("SELECT * FROM projects ORDER BY start_date ASC");

// Separate future and past projects

// Fetch all projects into an array
$all_projects = [];
while ($row = $result_projects->fetch_assoc()) {
    $all_projects[] = $row;
}

// Separate future and past projects
$future_projects = [];
$past_projects = [];

foreach ($all_projects as $row) {
    if ($row['end_date'] >= $now) {
        $future_projects[] = $row;
    } else {
        $past_projects[] = $row;
    }
}

// Separate projects by status
$status_projects = [
    'idea' => [],
    'en curso' => [],
    'finalizado' => [],
    'pausado' => [],
];

foreach ($all_projects as $row) {
    $status_key = $row['status'];
    // Use array key to categorize directly. Ensure key exists or use a fallback.
    if (isset($status_projects[$status_key])) {
        $status_projects[$status_key][] = $row;
    }
}

// Assign variables for use in HTML (optional, but keeps old variable names)
$wish_projects = $status_projects['idea'];
$current_projects = $status_projects['en curso'];
$finished_projects = $status_projects['finalizado'];
$paused_projects = $status_projects['pausado'];

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
        <div class="text-center mb-5 px-3 px-md-5">
          <h2 class="fw-bold mb-4" style="color: var(--muted);" data-en="Our Projects" data-es="Nuestros Proyectos">
            Nuestros Proyectos
          </h2>

          <div class="mx-auto mb-4" style="width:60px; height:3px; background: var(--primary); border-radius:2px;"></div>

          <h6 class="lh-lg text-muted mx-auto" style="max-width: 700px"
            data-en="At AISC Madrid we believe in the power of artificial intelligence to transform the world. Our projects reflect our commitment to innovation, education, and positive social impact."
            data-es="En AISC Madrid creemos en el poder de la inteligencia artificial para transformar el mundo. Nuestros proyectos reflejan nuestro compromiso con la innovación, la educación y el impacto social positivo.">
            En AISC Madrid creemos en el poder de la inteligencia artificial para transformar el mundo.
            Nuestros proyectos reflejan nuestro compromiso con la innovación, la educación y el impacto social positivo.
          </h6>
        </div>
    </section>

    <!-- Projects section -->

    <section class="section w-100 px-3 px-md-5" id="projects">
      <div class="container-fluid">
        <div class="mx-auto mb-4" style="width:60px; height:3px; background: var(--primary); border-radius:2px;"></div>

        <div class="project-btn-container mb-4 text-center">
            <button type="button" class="btn btn-primary project-btn fw-semibold project-filter-btn" data-filter="wish" data-en="Idea" data-es="Idea">Idea</button>
            <button type="button" class="btn btn-primary project-btn fw-semibold project-filter-btn" data-filter="current" data-en="In progress" data-es="En curso">En curso</button>
            <button type="button" class="btn btn-primary project-btn fw-semibold project-filter-btn" data-filter="finished" data-en="Finished" data-es="Finalizado">Finalizado</button>
            <button type="button" class="btn btn-primary project-btn fw-semibold project-filter-btn" data-filter="paused" data-en="Paused" data-es="En pausa">En pausa</button>
        </div>

      <!-- Idea de Proyecto -->
      <div class="project-group wish row g-4" style="width:100%; magin-bottom:30px; margin-top:30px;">
        <?php
          // Ordenar ideas por fecha
          usort($wish_projects, function($a, $b) {
              return strtotime($b['start_date']) <=> strtotime($a['start_date']);
          });
        ?>
          <?php foreach ($wish_projects as $project): ?>
            <?php
              $id         = (int)$project['id'];
              $title_es   = htmlspecialchars($project['title_es'] ?? '');
              $title_en   = htmlspecialchars($project['title_en'] ?? '');
              $image_path = htmlspecialchars($project['image_path'] ?? '');
              $start_date = !empty($project['start_date']) ? date("d/m/Y", strtotime($project['start_date'])) : '';
              $desc_es    = htmlspecialchars($project['short_description_es'] ?? '');
              $desc_en    = htmlspecialchars($project['short_description_en'] ?? '');
              $category   = array_map('trim', explode(',', $project['category']));
            ?>
            <div class="col-12 col-md-6 project-wish">
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

      <!-- Proyecto en Curso -->
      <div class="project-group current row g-4" style="width:100%; magin-bottom:30px; margin-top:30px;">
        <?php
          //Order past projects by most recent first
          usort($current_projects, function($a, $b) {
          return strtotime($b['start_date']) <=> strtotime($a['start_date']);
          });
        ?>
          <?php foreach ($current_projects as $project): ?>
            <?php
              // Safe helpers / fallbacks
              $id           = (int)$project['id'];
              $title_es     = htmlspecialchars($project['title_es'] ?? '');
              $title_en     = htmlspecialchars($project['title_en'] ?? '');
              $image_path   = htmlspecialchars($project['image_path'] ?? '');
              $start_date   = !empty($project['start_date']) ? date("d/m/Y", strtotime($project['start_date'])) : '';
              $desc_es      = htmlspecialchars($project['short_description_es'] ?? $project['short_description'] ?? '');
              $desc_en      = htmlspecialchars($project['short_description_en'] ?? $project['short_description'] ?? '');
              $category     = array_map('trim', explode(',', $project['category']));
            ?>
            <div class="col-12 col-md-6 project-current">
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
                          data-en="<?= htmlspecialchars($desc_en ?? '') ?>"
                          data-es="<?= htmlspecialchars($desc_es) ?>">
                          <?= htmlspecialchars($desc_es) ?>
                        </p>
                      <?php endif; ?>

                      <div class="mt-3">
                        <span class="btn btn-sm btn-outline-primary" data-es="Saber más" data-en="More Information">Saber más</span>
                      </div>
                    </div>
                  </div>

                  <!-- RIGHT: Image -->
                  <div class="col-12 col-sm-5 img-side">
                    <?php if ($image_path): ?>
                      <img src="<?= $image_path ?>" alt="<?= $title_es ?>" style="width:100%; height:100%; object-fit:cover; border-radius:10px;">
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


      <!-- Proyecto Terminado -->
      <div class="project-group finished row g-4" style="width:100%; magin-bottom:30px; margin-top:30px;">
      <?php
      //Order past projects by most recent first
      usort($finished_projects, function($a, $b) {
      return strtotime($b['start_date']) <=> strtotime($a['start_date']);
      }); ?>
        <?php foreach ($finished_projects as $project): ?>
          <?php
          // Safe helpers / fallbacks
          $id           = (int)$project['id'];
          $title_es     = htmlspecialchars($project['title_es'] ?? '');
          $title_en     = htmlspecialchars($project['title_en'] ?? '');
          $image_path   = htmlspecialchars($project['image_path'] ?? '');
          $start_date   = !empty($project['start_date']) ? date("d/m/Y", strtotime($project['start_date'])) : '';
          $desc_es      = htmlspecialchars($project['short_description_es'] ?? $project['short_description'] ?? '');
          $desc_en      = htmlspecialchars($project['short_description_en'] ?? $project['short_description'] ?? '');
          $category     = array_map('trim', explode(',', $project['category']));
          ?>
          <div class="col-12 col-md-6 project-finished">
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
                        data-en="<?= htmlspecialchars($desc_en ?? '') ?>"
                        data-es="<?= htmlspecialchars($desc_es) ?>">
                        <?= htmlspecialchars($desc_es) ?>
                      </p>
                    <?php endif; ?>

                    <!-- Optional CTA -->
                    <div class="mt-3">
                      <span class="btn btn-sm btn-outline-primary" data-es="Saber más" data-en="More Information">Saber más</span>
                    </div>
                  </div>
                </div>

                <!-- RIGHT: Image -->
                <div class="col-12 col-sm-5 img-side">
                  <?php if ($image_path): ?>
                    <img src="<?= $image_path ?>" alt="<?= $title_es ?>" style="width:100%; height:100%; object-fit:cover; border-radius:10px;">
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


        <!-- Proyecto en Pausa -->
        <div class="project-group paused row g-4" style="width:100%; magin-bottom:30px; margin-top:30px;">
        <?php
        //Order past projects by most recent first
        usort($paused_projects, function($a, $b) {
        return strtotime($b['start_date']) <=> strtotime($a['start_date']);
        }); ?>
          <?php foreach ($paused_projects as $project): ?>
            <?php
              // Safe helpers / fallbacks
              $id           = (int)$project['id'];
              $title_es     = htmlspecialchars($project['title_es'] ?? '');
              $title_en     = htmlspecialchars($project['title_en'] ?? '');
              $image_path   = htmlspecialchars($project['image_path'] ?? '');
              $start_date   = !empty($project['start_date']) ? date("d/m/Y", strtotime($project['start_date'])) : '';
              $desc_es      = htmlspecialchars($project['short_description_es'] ?? $project['short_description'] ?? '');
              $desc_en      = htmlspecialchars($project['short_description_en'] ?? $project['short_description'] ?? '');
              $category     = array_map('trim', explode(',', $project['category']));
            ?>

            <div class="col-12 col-md-6 project-paused">
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
                          data-en="<?= htmlspecialchars($desc_en ?? '') ?>"
                          data-es="<?= htmlspecialchars($desc_es) ?>">
                          <?= htmlspecialchars($desc_es) ?>
                        </p>
                      <?php endif; ?>

                      <!-- Optional CTA -->
                      <div class="mt-3">
                        <span class="btn btn-sm btn-outline-primary" data-es = "Saber más" data-en = "More Information">Saber más</span>
                      </div>
                    </div>
                  </div>

                  <!-- RIGHT: Image -->
                  <div class="col-12 col-sm-5 img-side">
                    <?php if ($image_path): ?>
                      <img src="<?= $image_path ?>" alt="<?= $title_es ?>" style="width:100%; height:100%; object-fit:cover; border-radius:10px;">
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

  


  <!-- Footer include -->
  <?php include('assets/footer.php'); ?>

  <!-- Bootstrap validation script -->
  <script src="js/index.js" defer></script>
  <script src="js/navbar.js" defer></script>
  <script src="js/language.js" defer></script>
  <script src="js/projects.js" defer></script>
  
  <!-- Bootstrap Bundle JS (includes Popper) -->
  <!-- Removed duplicate Bootstrap Bundle JS inclusion -->

</body>

<?php $conn->close(); ?>
</html>
  <script src="js/language.js"></script>
  
  <!-- Bootstrap Bundle JS (includes Popper) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
