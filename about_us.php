<!DOCTYPE html>
<html lang="en">

<?php include("assets/head.php"); ?>



<body class="d-flex flex-column min-vh-100">

  <?php include("assets/nav.php"); ?>

  <div class="container scroll-margin">

    <!-- Título -->
    <div class="text-center mb-5 px-3 px-md-5 fade-up" style="color:black;">
      <h2 class="fw-bold mb-4" style="color: var(--muted);" translation-key="about_title">
        Sobre AISC Madrid
      </h2>
      <div class="mx-auto mb-4" style="width:60px; height:3px; background: var(--primary); border-radius:2px;"></div>
    </div>

    <!-- AISC Section -->
    <section class="story-block">
      <div class="story-content fade-left">
        <h3 translation-key="about_h3_aisc">AI Student Collective</h3>
        <p translation-key="about_p_aisc">
          AISC Madrid nace como la <strong>primera asociación de IA</strong> en la EPS de la Universidad Carlos III y
          la primera rama europea de la asociación AISC, inicialmente fundada en
          <a href="https://www.instagram.com/aiscdavis/" target="_blank">University of California Davis</a>
          y actualmente con ramas en
          <a href="https://www.instagram.com/aiscuw/" target="_blank">University of Washington</a>
          y
          <a href="https://www.instagram.com/aisc_riv/" target="_blank">University of California Riverside.</a>
        </p>
      </div>
      <div class="story-image fade-right">
        <img src="images/resources/equipoAISC.jpg" alt="AISC Team">
      </div>
    </section>

    <!-- Misión Section -->
    <section class="story-block reverse">
      <div class="story-content fade-right">
        <h3 translation-key="about_h3_mision">Misión</h3>
        <p translation-key="about_p_vision">La asociación tiene una visión clara:</p>
        <ul style="color:black">
          <li class="fade-up delay-1" translation-key="about_li_workshops">
            Queremos ayudar a la comunidad universitaria a adquirir habilidades prácticas relacionadas con la IA,
            ayudando así a entenderla mejor y desmitificarla mediante talleres prácticos.
          </li>
          <li class="fade-up delay-2" translation-key="about_li_professional">
            Queremos acercar el mundo laboral de la mano de miembros destacados del sector.
          </li>
          <li class="fade-up delay-3" translation-key="about_li_community">
            Queremos crear una comunidad que ayude a disfrutar la experiencia universitaria.
          </li>
        </ul>
      </div>
      <div class="story-image fade-left">
        <img src="images/logos/PNG/Understand AI Unlock Potential.png" alt="Understand AI, Unlock Potential">
      </div>
    </section>

  </div>

  <!-- Carrusel de fotos -->
  <section class="photo-carousel-section fade-up">
    <div class="container">
      <h3 translation-key="about_h3_gallery">📸 Nuestra Comunidad</h3>

      <div id="photoCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4000">
        <div class="carousel-indicators">
          <button type="button" data-bs-target="#photoCarousel" data-bs-slide-to="0" class="active"></button>
          <button type="button" data-bs-target="#photoCarousel" data-bs-slide-to="1"></button>
          <button type="button" data-bs-target="#photoCarousel" data-bs-slide-to="2"></button>
          <button type="button" data-bs-target="#photoCarousel" data-bs-slide-to="3"></button>
          <button type="button" data-bs-target="#photoCarousel" data-bs-slide-to="4"></button>
        </div>

        <div class="carousel-inner">
          <!-- Cuando tengas las fotos, reemplaza cada .carousel-placeholder por:
               <img src="images/resources/fotoX.jpg" class="d-block w-100" alt="..."> -->
          <div class="carousel-item active">
            <div class="carousel-placeholder">
              <span class="icon">🖼️</span>
              <span translation-key="about_carousel_placeholder">Foto próximamente</span>
            </div>
          </div>
          <div class="carousel-item">
            <div class="carousel-placeholder">
              <span class="icon">🖼️</span>
              <span translation-key="about_carousel_placeholder">Foto próximamente</span>
            </div>
          </div>
          <div class="carousel-item">
            <div class="carousel-placeholder">
              <span class="icon">🖼️</span>
              <span translation-key="about_carousel_placeholder">Foto próximamente</span>
            </div>
          </div>
          <div class="carousel-item">
            <div class="carousel-placeholder">
              <span class="icon">🖼️</span>
              <span translation-key="about_carousel_placeholder">Foto próximamente</span>
            </div>
          </div>
          <div class="carousel-item">
            <div class="carousel-placeholder">
              <span class="icon">🖼️</span>
              <span translation-key="about_carousel_placeholder">Foto próximamente</span>
            </div>
          </div>
        </div>

        <button class="carousel-control-prev" type="button" data-bs-target="#photoCarousel" data-bs-slide="prev">
          <span class="carousel-control-prev-icon"></span>
          <span class="visually-hidden">Anterior</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#photoCarousel" data-bs-slide="next">
          <span class="carousel-control-next-icon"></span>
          <span class="visually-hidden">Siguiente</span>
        </button>
      </div>
    </div>
  </section>

  <!-- Valores -->
  <div class="container">
    <section class="values-tabs-section container scroll-margin py-5 fade-up">
      <h3 class="text-center mb-4" style="color: var(--primary);" translation-key="about_h3_values">
        Nuestros Valores
      </h3>

      <ul class="nav nav-pills justify-content-center mb-4 flex-wrap gap-2" id="valuesTab" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="believe_people-tab" data-bs-toggle="pill"
            data-bs-target="#believe_people" type="button" role="tab" aria-selected="true">
            <span translation-key="about_val_people_tab">Creemos en las personas</span>
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="curiosity-tab" data-bs-toggle="pill" data-bs-target="#curiosity"
            type="button" role="tab" aria-selected="false">
            <span translation-key="about_val_curiosity_tab">Curiosidad</span>
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="transparency-tab" data-bs-toggle="pill" data-bs-target="#transparency"
            type="button" role="tab" aria-selected="false">
            <span translation-key="about_val_transparency_tab">Transparencia y Apertura</span>
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="activeresponsibility-tab" data-bs-toggle="pill"
            data-bs-target="#activeresponsibility" type="button" role="tab" aria-selected="false">
            <span translation-key="about_val_responsibility_tab">Responsabilidad Activa</span>
          </button>
        </li>
      </ul>

      <div class="tab-content text-center" id="valuesTabContent">
        <div class="tab-pane fade show active" id="believe_people" role="tabpanel">
          <p translation-key="about_val_people_p">
            Confiamos en que cada miembro de la comunidad tiene algo valioso que aportar, y queremos que cada uno de
            ellos se sienta parte de la asociación.
          </p>
        </div>
        <div class="tab-pane fade" id="curiosity" role="tabpanel">
          <p translation-key="about_val_curiosity_p">
            Exploramos, probamos y aprendemos. Como estudiantes, sabemos que no tenemos todas las respuestas, pero cada
            intento nos acerca a hacer mejor las cosas.
          </p>
        </div>
        <div class="tab-pane fade" id="transparency" role="tabpanel">
          <p translation-key="about_val_transparency_p">
            Nos gusta compartir nuestro conocimiento y procesos de manera abierta y clara. Publicando recursos,
            proyectos y resultados fomentamos un ambiente de aprendizaje colaborativo y abierto a todo el mundo.
          </p>
        </div>
        <div class="tab-pane fade" id="activeresponsibility" role="tabpanel">
          <p translation-key="about_val_responsibility_p">
            Valoramos que cada miembro tome la iniciativa y se esfuerce por lograr un trabajo que le sea útil y del que
            se sienta orgulloso.
          </p>
        </div>
      </div>
    </section>
  </div>

  <?php include('assets/footer.php'); ?>

  <script src="js/navbar.js"></script>
  <script>
    // ── Intersection Observer para animaciones scroll ──
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
        }
      });
    }, { threshold: 0.15 });

    document.querySelectorAll('.fade-up, .fade-left, .fade-right').forEach(el => {
      observer.observe(el);
    });
  </script>
</body>

</html>