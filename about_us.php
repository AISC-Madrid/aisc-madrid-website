<!DOCTYPE html>
<html lang="en">

<?php include("assets/head.php"); ?>

<body class="d-flex flex-column min-vh-100">

  <?php include("assets/nav.php"); ?>
  <div class="container scroll-margin">
    <div class="text-center mb-5 px-3 px-md-5" style="color:black;">
      <h2 class="fw-bold mb-4" style="color: var(--muted);" translation-key="about_title">
        Sobre AISC Madrid
      </h2>
      <div class="mx-auto mb-4" style="width:60px; height:3px; background: var(--primary); border-radius:2px;"></div>
    </div>

    <section class="story-block">
      <div class="story-content">
        <h3 translation-key="about_h3_aisc">
          AI Student Collective
        </h3>
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
      <div class="story-image">
        <img src="images/resources/equipoAISC.jpg" alt="AISC Team">
      </div>
    </section>

    <section class="story-block reverse">
      <div class="story-content">
        <h3 translation-key="about_h3_mision">Misión</h3>
        <p translation-key="about_p_vision">
          La asociación tiene una visión clara:
        </p>
        <ul style="color:black">
          <li translation-key="about_li_workshops">
            Queremos ayudar a la comunidad universitaria a adquirir habilidades prácticas relacionadas con la IA,
            ayudando así a entenderla mejor y desmitificarla mediante talleres prácticos.
          </li>
          <li translation-key="about_li_professional">
            Queremos acercar el mundo laboral de la mano de miembros destacados del sector.
          </li>
          <li translation-key="about_li_community">
            Queremos crear una comunidad que ayude a disfrutar la experiencia universitaria.
          </li>
        </ul>
      </div>
      <div class="story-image">
        <img src="images/logos/PNG/Understand AI Unlock Potential.png" alt="Understand AI, Unlock Potential">
      </div>
    </section>

    <section class="values-tabs-section container scroll-margin py-5">
      <h3 class="text-center mb-4" style="color: var(--primary);" translation-key="about_h3_values">
        Nuestros Valores
      </h3>

      <ul class="nav nav-pills justify-content-center mb-4" id="valuesTab" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="believe_people-tab" data-bs-toggle="pill" data-bs-target="#believe_people"
            type="button" role="tab" aria-controls="believe_people" aria-selected="true">
            <span translation-key="about_val_people_tab">Creemos en las personas</span>
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="curiosity-tab" data-bs-toggle="pill" data-bs-target="#curiosity" type="button"
            role="tab" aria-controls="curiosity" aria-selected="false">
            <span translation-key="about_val_curiosity_tab">Curiosidad</span>
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="transparency-tab" data-bs-toggle="pill" data-bs-target="#transparency"
            type="button" role="tab" aria-controls="transparency" aria-selected="false">
            <span translation-key="about_val_transparency_tab">Transparencia y Apertura</span>
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="activeresponsibility-tab" data-bs-toggle="pill"
            data-bs-target="#activeresponsibility" type="button" role="tab" aria-controls="transparency"
            aria-selected="false">
            <span translation-key="about_val_responsibility_tab">Responsabilidad Activa</span>
          </button>
        </li>
      </ul>

      <div class="tab-content text-center" id="valuesTabContent">
        <div class="tab-pane fade show active" id="believe_people" role="tabpanel" aria-labelledby="believe_people-tab">
          <p translation-key="about_val_people_p">
            Confiamos en que cada miembro de la comunidad tiene algo valioso que aportar, y queremos que cada uno de
            ellos se sienta parte de la asociación.
          </p>
        </div>

        <div class="tab-pane fade" id="curiosity" role="tabpanel" aria-labelledby="curiosity-tab">
          <p translation-key="about_val_curiosity_p">
            Exploramos, probamos y aprendemos. Como estudiantes, sabemos que no tenemos todas las respuestas, pero cada
            intento nos acerca a hacer mejor las cosas.
          </p>
        </div>

        <div class="tab-pane fade" id="transparency" role="tabpanel" aria-labelledby="transparency-tab">
          <p translation-key="about_val_transparency_p">
            Nos gusta compartir nuestro conocimiento y procesos de manera abierta y clara. Publicando recursos,
            proyectos y resultados fomentamos un ambiente de aprendizaje colaborativo y abierto a todo el mundo.
          </p>
        </div>

        <div class="tab-pane fade" id="activeresponsibility" role="tabpanel" aria-labelledby="activeresponsibility-tab">
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
</body>

</html>