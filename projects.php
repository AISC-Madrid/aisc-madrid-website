<!DOCTYPE html>
<html lang="en">
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
include("assets/db.php");

// Get current date
$now = date("Y-m-d");

// Retrieve all projects ordered by start_date
$result_projects = $conn->query("SELECT * FROM projects ORDER BY start_date ASC");

// Separate future and past projects
$future_projects = [];
$past_projects = [];

while ($row = $result_projects->fetch_assoc()) {
    if ($row['end_date'] >= $now) {
        $future_projects[] = $row;
    } else {
        $past_projects[] = $row;
    }
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
    <!-- Landing -->
    <header class="row mx-2 scroll-margin" style="min-height:85vh; width:90vw;">
      <div class="col-12 col-md-7 order-2 order-md-1 d-flex flex-column align-items-start justify-content-center pt-5">
        <h1 class="text-dark fw-bold display-3"> <span style="color:var(--primary);">AI</span> <br> Student Collective <br> <span style="color:var(--secondary);">Madrid</span></h1>
        <h6 class="lh-lg text-muted" data-en="UC3M Student Association Interested in AI.
        Gain in-demand skills, connect with industry, and become part of an international community." data-es="Asociación de Estudiantes de la UC3M interesados en la IA. Adquiere habilidades demandadas, conecta con la industria y forma parte de una comunidad internacional.">Asociación de Estudiantes de la UC3M interesados en la IA. Adquiere habilidades demandadas, conecta con la industria y forma parte de una comunidad internacional.</h6>
        <div class="mt-4 d-flex gap-2">
          <a href="#" class="btn btn-custom text-light px-4 fw-semibold" data-en="Join!" data-es="¡Participa!">¡Participa!</a>
        </div>
      </div>
      <div class="col-12 col-md-5 order-1 order-md-2 d-flex flex-column align-items-center align-items-md-end justify-content-center ">
        <img style="width: 75%;" src="images/logos/PNG/AISC Logo Color.png" alt="Logotipo de la asociación AISC">
      </div>
    </header>

    <!-- Projects section -->

    <section class="section scroll-margin w-100 px-3 px-md-5" id="projects">
      <div class="container-fluid">
        <h2 class="text-center mb-4 fw-bold">
          <span style="color: var(--muted);" data-en="Projects" data-es="Proyectos">Proyectos</span>
        </h2>
        <div class="mx-auto mb-4" style="width:60px; height:3px; background: var(--primary); border-radius:2px;"></div>

        <div class="project-btn-container mb-4 text-center">
            <button class="btn btn-primary project-btn fw-semibold project-filter-btn" data-filter="future" data-en="Future Projects" data-es="Proyectos Futuros">Proyectos Futuros</button>
            <button class="btn btn-primary project-btn fw-semibold project-filter-btn" data-filter="past" data-en="Past Projects" data-es="Proyectos Pasados">Proyectos Pasados</button>
        </div>

        <div class="row g-4" style="width:100%;">
            <?php foreach ($future_projects as $project): ?>
                <div class="col-md-6 col-lg-4 project-future">
                    <a href="/projects/project.php?id=<?= $project['id'] ?>" class="text-decoration-none text-reset">
                        <div class="card h-100 w-100 shadow-sm">
                            <div class="card-body p-0 position-relative">
                                <div class="img-container">
                                    <img src="<?= htmlspecialchars($project['image_path']) ?>" class="card-img-top" alt="<?= htmlspecialchars($project['title_es']) ?>" style="object-fit: cover;">
                                </div>
                                <div class="p-3 pb-5">
                                    <h5 class="card-title mt-3 fw-bold" data-en="<?= htmlspecialchars($project['title_en']) ?>" data-es="<?= htmlspecialchars($project['title_es']) ?>">
                                        <?= htmlspecialchars($project['title_es']) ?>
                                    </h5>
                                    <p class="card-text">
                                        <i class="fas fa-calendar me-2"></i>
                                        <strong><?= date("d/m/Y", strtotime($project['start_date'])) ?></strong><br>
                                        <?= date("H:i", strtotime($project['start_date'])) ?> - <?= date("H:i", strtotime($project['end_date'])) ?>
                                    </p>
                                </div>
                                <div class="card-more-badge" data-en="More information" data-es="Saber más">Saber más</div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>

            <?php
            //Order past projects by most recent first
            usort($past_projects, function($a, $b) {
            return strtotime($b['start_date']) <=> strtotime($a['start_date']);
            });
            foreach ($past_projects as $project): ?>
                <div class="col-md-6 col-lg-4 project-past">
                    <a href="/projects/project.php?id=<?= $project['id'] ?>" class="text-decoration-none text-reset">
                        <div class="card h-100 w-100 shadow-sm">
                            <div class="card-body p-0 position-relative">
                                <div class="img-container">
                                    <img src="<?= htmlspecialchars($project['image_path']) ?>" class="card-img-top" alt="<?= htmlspecialchars($project['title_es']) ?>" style="object-fit: cover;">
                                </div>
                                <div class="p-3 pb-5">
                                    <h5 class="card-title mt-3 fw-bold" data-en="<?= htmlspecialchars($project['title_en']) ?>" data-es="<?= htmlspecialchars($project['title_es']) ?>">
                                        <?= htmlspecialchars($project['title_es']) ?>
                                    </h5>
                                    <p class="card-text">
                                        <i class="fas fa-calendar me-2"></i>
                                        <strong><?= date("d/m/Y", strtotime($project['start_date'])) ?></strong><br>
                                    </p>
                                </div>
                                <div class="card-more-badge" data-en="More information" data-es="Saber más">Saber más</div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php $conn->close(); ?>
    
    <!-- Newsletter section -->
    <section class="container-fluid mb-5 scroll-margin" id="newsletter">
      <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
          <div class=" border-0 form-card no-hover">
            <div id="form-error" class="card-body bg-muted p-4">
              <h4 class="fw-bold text-center mb-3" style="color: var(--secondary);" data-en="Don't miss a thing!" data-es="¡Mantente al tanto!">¡Mantente al tanto!</h4>
              <p class="text-muted text-center mb-4 form-subtext" data-en="Send your name and email to stay informed and join our AI community at UC3M." data-es="Envía tu nombre y correo electrónico para mantenerte informado y unirte a nuestra comunidad de IA en UC3M.">
                Envía tu nombre y correo electrónico para mantenerte informado y unirte a nuestra comunidad de IA en UC3M.
              </p>

              <form method="POST" action="processing/phpmailer.php" class="needs-validation" novalidate>
                <!-- Nombre -->
                <div class="mb-3">
                  <label for="name" class="form-label" data-en="Full name" data-es="Nombre y apellidos">Nombre y apellidos</label>
                  <input type="text" class="form-control form-input" id="name" name="name" data-es="Nombre y apellido(s)" data-en="Full name" placeholder="Michael Scott" required>
                  <div class="invalid-feedback" data-en="Please, introduce your name and surname." data-es="Por favor, introduce tu nombre y apellido(s).">Por favor, introduce tu nombre y apellido(s).</div>
                </div>

                <!-- Email -->
                <div class="mb-3">
                  <label for="email" class="form-label" data-en="E-mail" data-es="Correo electrónico">Correo electrónico</label>
                  <input type="email" class="form-control form-input" id="email" name="email" data-en="name@example.com" data-es="nombre@ejemplo.com" placeholder="name@example.com" required>
                  <div class="invalid-feedback" data-en="Please, introduce a valid e-mail" data-es="Por favor, introduce un correo válido.">Por favor, introduce un correo válido.</div>
                </div>

                <!-- Consentimiento -->
                <div class="form-check mb-3">
                  <input class="form-check-input" type="checkbox" id="consent" name="consent" required>
                  <label class="form-check-label form-text" for="consent" data-en="I consent to AISC Madrid storing my submitted data to contact me." data-es="Doy mi consentimiento para que AISC Madrid almacene mis datos enviados para contactarme.">
                    Doy mi consentimiento para que AISC Madrid almacene mis datos enviados para contactarme.
                  </label>
                      <a class="form-check-label form-text" href="terms_conditions.php" target="_blank" data-en="(Read terms and conditions)" data-es="(Leer términos y condiciones)">
                        (Leer términos y condiciones)
                      </a>
                  <div class="invalid-feedback" data-en="You must give permission to continue" data-es="Debes dar tu consentimiento para continuar. ">Debes dar tu consentimiento para continuar.</div>
                </div>

                <!-- Enviar -->
                <div class="d-grid">
                  <button type="submit" class="btn btn-primary form-btn fw-semibold" data-en="Send" data-es="Enviar">Enviar</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>


  <!-- Footer include -->
  <?php include('assets/footer.php'); ?>

  <!-- Bootstrap validation script -->
  <script src="js/index.js"></script>
  <script src="js/navbar.js"></script>
  <script src="js/language.js"></script>
  
  <!-- Bootstrap Bundle JS (includes Popper) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>



</body>

</html>

