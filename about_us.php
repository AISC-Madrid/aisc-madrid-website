<!DOCTYPE html>
<html lang="en">

<?php include("assets/head.php"); ?>

<style>
  /* ── Animaciones de entrada ── */
  .fade-up {
    opacity: 0;
    transform: translateY(40px);
    transition: opacity 0.7s ease, transform 0.7s ease;
  }
  .fade-up.visible {
    opacity: 1;
    transform: translateY(0);
  }
  .fade-left {
    opacity: 0;
    transform: translateX(-40px);
    transition: opacity 0.7s ease, transform 0.7s ease;
  }
  .fade-left.visible {
    opacity: 1;
    transform: translateX(0);
  }
  .fade-right {
    opacity: 0;
    transform: translateX(40px);
    transition: opacity 0.7s ease, transform 0.7s ease;
  }
  .fade-right.visible {
    opacity: 1;
    transform: translateX(0);
  }

  /* Delays escalonados */
  .delay-1 { transition-delay: 0.1s; }
  .delay-2 { transition-delay: 0.2s; }
  .delay-3 { transition-delay: 0.3s; }
  .delay-4 { transition-delay: 0.4s; }

  /* ── Carrusel ── */
  .photo-carousel-section {
    padding: 60px 0;
    background: linear-gradient(135deg, #f8f8f8 0%, #fff0f8 100%);
  }
  .photo-carousel-section h3 {
    color: var(--primary);
    text-align: center;
    margin-bottom: 30px;
    font-weight: 700;
  }
  #photoCarousel .carousel-inner {
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 8px 40px rgba(0,0,0,0.12);
  }
  .carousel-placeholder {
    height: 420px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    gap: 12px;
    color: #aaa;
    font-size: 1rem;
  }
  .carousel-placeholder .icon {
    font-size: 3rem;
    opacity: 0.4;
  }
  .carousel-item:nth-child(1) .carousel-placeholder { background: linear-gradient(135deg, #fce4ec, #f8bbd0); }
  .carousel-item:nth-child(2) .carousel-placeholder { background: linear-gradient(135deg, #e8eaf6, #c5cae9); }
  .carousel-item:nth-child(3) .carousel-placeholder { background: linear-gradient(135deg, #e0f7fa, #b2ebf2); }
  .carousel-item:nth-child(4) .carousel-placeholder { background: linear-gradient(135deg, #f3e5f5, #e1bee7); }
  .carousel-item:nth-child(5) .carousel-placeholder { background: linear-gradient(135deg, #fff9c4, #fff59d); }

  #photoCarousel .carousel-control-prev-icon,
  #photoCarousel .carousel-control-next-icon {
    filter: invert(0%) sepia(100%) saturate(1000%) hue-rotate(300deg) brightness(60%);
  }
  #photoCarousel .carousel-indicators [data-bs-target] {
    background-color: var(--primary);
  }

  /* ── Valores tabs mejoradas ── */
  #valuesTab .nav-link {
    transition: all 0.3s ease;
    border-radius: 30px;
    font-weight: 500;
  }
  #valuesTab .nav-link:hover:not(.active) {
    background: rgba(220, 50, 120, 0.1);
    transform: translateY(-2px);
  }
  #valuesTab .nav-link.active {
    background: var(--primary);
    color: white;
    box-shadow: 0 4px 15px rgba(220, 50, 120, 0.3);
  }

  .tab-content .tab-pane p {
    font-size: 1.1rem;
    color: #444;
    max-width: 600px;
    margin: 0 auto;
    line-height: 1.8;
  }

  /* ── Story blocks ── */
  .story-image img {
    transition: transform 0.4s ease, box-shadow 0.4s ease;
    border-radius: 12px;
  }
  .story-image img:hover {
    transform: scale(1.02);
    box-shadow: 0 12px 40px rgba(0,0,0,0.15);
  }

  /* ── Stats strip ── */
  .stats-strip {
    background: var(--primary);
    color: white;
    padding: 40px 0;
    text-align: center;
  }
  .stat-item {
    padding: 10px 20px;
  }
  .stat-number {
    font-size: 2.5rem;
    font-weight: 800;
    display: block;
    letter-spacing: -1px;
  }
  .stat-label {
    font-size: 0.85rem;
    opacity: 0.85;
    text-transform: uppercase;
    letter-spacing: 1px;
  }
</style>

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