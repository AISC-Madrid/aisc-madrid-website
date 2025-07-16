<!DOCTYPE html>
<html lang="en">

<?php
include("assets/head.php");
?>

<body>
-
  <?php
  include("assets/nav.php");
  ?>

  <!-- Main container -->
  <div class="container-fluid d-flex flex-column align-items-center justify-content-center">
    <!-- Landing -->
    <header class="row mx-2 scroll-margin" style="height:85vh; width:90vw;">
      <div class="col-7 d-flex flex-column align-items-start justify-content-center">
        <h1 class="text-dark fw-bold display-3"> <span style="color:var(--primary);">AI</span> <br> Student Collective <br> <span style="color:var(--secondary);">Madrid</span></h1>
        <h6 class="lh-lg text-muted">Colectivo de estudiantes de IA en UC3M. Adquiere habilidades demandadas, conecta con la industria y forma parte de una comunidad internacional de IA.</h6>
        <div class="mt-4 d-flex gap-2">
          <a href="#get-involved" style="background-color:var(--primary);" class="text-light btn px-4 fw-semibold">¡Únete!</a>
          <a href="#more" class="btn btn-outline-secondary px-4 fw-semibold">
            Más <span class="ms-1">&rarr;</span>
          </a>
        </div>
      </div>
      <div class="col-5 d-flex flex-column align-items-end justify-content-center ">
        <img style="width: 75%;" src="images/SVG/Property%201=AISC%20Logo%20Color.svg" alt="Logotipo de la asociación AISC">
      </div>
    </header>

    <section class="scroll-margin">
      <div class="container py-4">
        <h2 class="text-center mb-4"><span style="color:var(--muted);">Desde AISC Madrid</span></h2>
        <div class="row g-4">

          <!-- Card 1 -->
          <div class="col-md-4">
            <div class="card h-100 shadow-sm">
              <div class="card-body d-flex flex-column">
                <div class="mb-3 d-inline-flex align-items-center justify-content-center rounded text-white" style="width:50px; height:50px; background-color: var(--primary);">
                  <i class="fa-solid fa-brain fs-2"></i>
                </div>
                <h5 class="card-title">Ingeniería e Inteligencia Artificial</h5>
                <p class="card-text">
                  Asociación de estudiantes en la Universidad Carlos III de Madrid.
                </p>
              </div>
            </div>
          </div>

          <!-- Card 2 -->
          <div class="col-md-4">
            <div class="card h-100 shadow-sm">
              <div class="card-body d-flex flex-column">
                <div class="mb-3 d-inline-flex align-items-center justify-content-center rounded text-white" style="width:50px; height:50px; background-color: var(--primary);">
                  <i class="bi bi-tools fs-2 "></i>
                </div>
                <h5 class="card-title">Talleres</h5>
                <p class="card-text">
                  Desmitifica la IA adquieriendo habilidades prácticas demandas por la industria.
                </p>
              </div>
            </div>
          </div>

          <!-- Card 3 -->
          <div class="col-md-4">
            <div class="card h-100 shadow-sm">
              <div class="card-body d-flex flex-column">
                <div class="mb-3 d-inline-flex align-items-center justify-content-center rounded text-white" style="width:50px; height:50px; background-color: var(--primary);">
                  <i class="bi bi-diagram-3 fs-2"></i>
                </div>
                <h5 class="card-title">Eventos y Conexiones</h5>
                <p class="card-text">
                  Acércate al mundo laboral de la mano de estudiantes y miembros del sector.
                </p>
              </div>
            </div>
          </div>

          <!-- Card 4 -->
          <div class="col-md-4">
            <div class="card h-100 shadow-sm">
              <div class="card-body d-flex flex-column">
                <div class="mb-3 d-inline-flex align-items-center justify-content-center rounded text-white" style="width:50px; height:50px; background-color: var(--secondary);">
                  <i class="bi bi-people fs-2"></i>
                </div>
                <h5 class="card-title">Comunidad</h5>
                <p class="card-text">
                  Aprovecha para conocer a otros estudiantes y disfrutar de la experiencia universitaria.
                </p>
              </div>
            </div>
          </div>

          <!-- Card 5 -->
          <div class="col-md-4">
            <div class="card h-100 shadow-sm">
              <div class="card-body d-flex flex-column">
                <div class="mb-3 d-inline-flex align-items-center justify-content-center rounded text-white" style="width:50px; height:50px; background-color: var(--secondary);">
                  <i class="bi bi-globe-americas fs-2"></i>
                </div>
                <h5 class="card-title">Red Internacional</h5>
                <p class="card-text">
                  Forma parte de la comunidad AI Student Collective, fundada en UC Davis, California y expandida por Estados Unidos.
                </p>
              </div>
            </div>
          </div>

          <!-- Card 6 -->
          <div class="col-md-4">
            <div class="card h-100 shadow-sm">
              <div class="card-body d-flex flex-column">
                <div class="mb-3 d-inline-flex align-items-center justify-content-center rounded text-white" style="width:50px; height:50px; background-color: var(--secondary);">
                  <i class="bi bi-rocket-takeoff fs-2"></i>
                </div>
                <h5 class="card-title">Bienvenid@</h5>
                <p class="card-text">
                  ¡Únete y disfruta del proceso!
                </p>
              </div>
            </div>
          </div>

        </div>
      </div>
    </section>

<section class="section scroll-margin" id="team"> 
  <div class="container">
    <h2 class="text-center mb-4">
      <span style="color: var(--muted);">Conoce al equipo</span>
    </h2>
    <div class="mt-5 row">

      <div class="col-sm-6 col-lg-3">
        <div class="team-box text-center">
          <div class="team-wrapper">
            <div class="team-member">
              <a href="https://giphy.com/gifs/theoffice-the-office-tv-frame-toby-hyyV7pnbE0FqLNBAzs" target="_blank">
                <img src="images/test/michael.jpg" alt="Michael Scott" class="img-fluid rounded">
              </a>
            </div>
          </div>
          <h5 class="mt-3" style="color: var(--background)">Michael Scott</h5>
          <p class="text-muted">Presidente</p>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3">
        <div class="team-box text-center">
          <div class="team-wrapper">
            <div class="team-member">
              <a href="https://giphy.com/gifs/theoffice-the-office-tv-frame-toby-hyyV7pnbE0FqLNBAzs" target="_blank">
                <img src="images/test/dwight.jpg" alt="Dwight" class="img-fluid rounded">
              </a>
            </div>
          </div>
          <h5 class="mt-3" style="color: var(--background)">Dwight Schrute</h5>
          <p class="text-muted">Vicepresidente</p>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3">
        <div class="team-box text-center">
          <div class="team-wrapper">
            <div class="team-member">
              <a href="https://giphy.com/gifs/theoffice-the-office-tv-frame-toby-hyyV7pnbE0FqLNBAzs" target="_blank">
                <img src="images/test/amador.jpg" alt="Amador" class="img-fluid rounded">
              </a>
            </div>
          </div>
          <h5 class="mt-3" style="color: var(--background)">Amador Rivas</h5>
          <p class="text-muted">Vividor</p>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3">
        <div class="team-box text-center">
          <div class="team-wrapper">
            <div class="team-member">
              <a href="https://giphy.com/gifs/theoffice-the-office-tv-frame-toby-hyyV7pnbE0FqLNBAzs" target="_blank">
                <img src="images/test/kenny.jpg" alt="Kenny" class="img-fluid rounded">
              </a>
            </div>
          </div>
          <h5 class="mt-3" style="color: var(--background)">Kenny</h5>
          <p class="text-muted">Miembro</p>
        </div>
      </div>

    </div>
  </div>
</section>


 <section class="container-fluid mb-5 scroll-margin" id="get-involved">
      <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
          <div class="card shadow-sm border-0 form-card no-hover">
            <div id="form-error" class="card-body p-4">
              <h4 class="display-4 text-center mb-3">¡Participa!</h4>
              <p class="text-muted text-center mb-4 form-subtext">
                Envía tu nombre y correo electrónico para mantenerte informado y unirte a nuestra comunidad de IA en UC3M.
              </p>

              <form method="POST" action="processing/get_involved_form.php" class="needs-validation" novalidate>
                <!-- Nombre -->
                <div class="mb-3">
                  <label for="name" class="form-label">Nombre completo</label>
                  <input type="text" class="form-control form-input" id="name" name="name" placeholder="Tu nombre" required>
                  <div class="invalid-feedback">Por favor, introduce tu nombre.</div>
                </div>

                <!-- Email -->
                <div class="mb-3">
                  <label for="email" class="form-label">Correo electrónico</label>
                  <input type="email" class="form-control form-input" id="email" name="email" placeholder="nombre@ejemplo.com" required>
                  <div class="invalid-feedback">Por favor, introduce un correo válido.</div>
                </div>

                <!-- Consentimiento -->
                <div class="form-check mb-3">
                  <input class="form-check-input" type="checkbox" id="consent" name="consent" required>
                  <label class="form-check-label form-text" for="consent">
                    Doy mi consentimiento para que AISC Madrid almacene mis datos enviados para contactarme.
                  </label>
                  <div class="invalid-feedback">Debes dar tu consentimiento para continuar.</div>
                </div>

                <!-- Enviar -->
                <div class="d-grid">
                  <button type="submit" class="btn btn-primary form-btn fw-semibold">Enviar</button>
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
  <script>
    (() => {
      'use strict';
      const forms = document.querySelectorAll('.needs-validation');

      Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
          if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
          }
          form.classList.add('was-validated');
        }, false);
      });
    })();
  const params = new URLSearchParams(window.location.search);
  const error = params.get('error');
  if (error) {
    let message = '';
    switch (error) {
      case 'validation':
        message = 'Por favor, completa todos los campos correctamente.';
        break;
      case 'duplicate':
        message = 'Este correo ya está registrado.';
        break;
      case 'insert':
        message = 'Hubo un error al guardar tus datos. Inténtalo de nuevo.';
        break;
      case 'connection':
        message = 'No se pudo conectar a la base de datos.';
        break;
      default:
        message = 'Ha ocurrido un error inesperado.';
    }

    const formSection = document.getElementById('form-error');
    if (formSection) {
      const alertDiv = document.createElement('div');
      alertDiv.className = 'alert alert-danger';
      alertDiv.innerText = message;
      formSection.prepend(alertDiv);
    }

    // Eliminar el parámetro de la URL después de mostrarlo
    window.history.replaceState({}, document.title, window.location.pathname + '#get-involved');
  }
</script>



  <!-- Bootstrap Bundle JS (includes Popper) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>



  <body>

</html>
