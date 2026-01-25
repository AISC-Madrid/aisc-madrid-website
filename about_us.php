<!DOCTYPE html>
<html lang="en">

<?php include("assets/head.php"); ?>

<body class="d-flex flex-column min-vh-100">

  <?php include("assets/nav.php"); ?>
  <div class="container scroll-margin">
    <div class="text-center mb-5 px-3 px-md-5" style="color:black;">
      <h2 class="fw-bold mb-4" style="color: var(--muted);" data-en="About AISC Madrid" data-es="Sobre AISC Madrid">
        Sobre AISC Madrid
      </h2>
      <div class="mx-auto mb-4" style="width:60px; height:3px; background: var(--primary); border-radius:2px;"></div>
    </div>

    <section class="story-block">
      <div class="story-content">
        <h3 data-es="AI Student Collective" data-en="AI Student Collective">
          AI Student Collective
        </h3>
        <p data-es="AISC Madrid nace como la <strong>primera asociación de IA</strong> en la EPS de la Universidad Carlos III y la primera rama europea de la asociación AISC, inicialmente fundada en <a href='https://www.instagram.com/aiscdavis/' target='_blank'>University of California Davis</a> y actualmente con ramas en <a href='https://www.instagram.com/aiscuw/' target='_blank'>University of Washington</a> y <a href='https://www.instagram.com/aisc_riv/' target='_blank'>University of California Riverside</a>."
          data-en="AISC Madrid was founded as the <strong>first AI association</strong> at the School of Engineering of Universidad Carlos III, and the first European branch of AISC. The association was originally established at <a href='https://www.instagram.com/aiscdavis/' target='_blank'>University of California Davis</a>, and now also has branches at <a href='https://www.instagram.com/aiscuw/' target='_blank'>University of Washington</a> and <a href='https://www.instagram.com/aisc_riv/' target='_blank'>University of California Riverside</a>.">
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
        <h3 data-es="Misión" data-en="Mission">Misión</h3>
        <p data-es="La asociación tiene una visión clara:" data-en="The association has a clear vision:">
          La asociación tiene una visión clara:
        <ul style="color:black">
          <li
            data-es="Queremos ayudar a la comunidad universitaria a adquirir habilidades prácticas relacionadas con la IA, ayudando así a entenderla mejor y desmitificarla mediante talleres prácticos."
            data-en="We want to help the university community acquire practical skills related to AI, better understand it, and demystify it through hands-on workshops.">
            Queremos ayudar a la comunidad universitaria a adquirir habilidades prácticas relacionadas con la IA,
            ayudando así a entenderla mejor y desmitificarla mediante talleres prácticos.
          <li data-es="Queremos acercar el mundo laboral de la mano de miembros destacados del sector."
            data-en="We want to bring the professional world closer through distinguished members of the field.">
            Queremos acercar el mundo laboral de la mano de miembros destacados del sector.
          </li>
          <li data-es="Queremos crear una comunidad que ayude a disfrutar la experiencia universitaria."
            data-en="We want to build a community that enhances the university experience.">
            Queremos crear una comunidad que ayude a disfrutar la experiencia universitaria.
          </li>
        </ul>
      </div>
      <div class="story-image">
        <img src="images/logos/PNG/Understand AI Unlock Potential.png" alt="Understand AI, Unlock Potential">
      </div>
    </section>




    <section class="values-tabs-section container scroll-margin py-5">
      <h3 class="text-center mb-4" style="color: var(--primary);" data-en="Our Values" data-es="Nuestros Valores">
        Nuestros Valores
      </h3>

      <!-- Lista de valores en fila -->
      <ul class="nav nav-pills justify-content-center mb-4" id="valuesTab" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="believe_people-tab" data-bs-toggle="pill" data-bs-target="#believe_people"
            type="button" role="tab" aria-controls="believe_people" aria-selected="true">
            <span data-en="We believe in people" data-es="Creemos en las personas">Creemos en las personas</span>
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="curiosity-tab" data-bs-toggle="pill" data-bs-target="#curiosity" type="button"
            role="tab" aria-controls="curiosity" aria-selected="false">
            <span data-en="Curiosity" data-es="Curiosidad">Curiosidad</span>
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="transparency-tab" data-bs-toggle="pill" data-bs-target="#transparency"
            type="button" role="tab" aria-controls="transparency" aria-selected="false">
            <span data-en="Transparency and Openness" data-es="Transparencia y Apertura">Transparencia y Apertura</span>
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="activeresponsibility-tab" data-bs-toggle="pill"
            data-bs-target="#activeresponsibility" type="button" role="tab" aria-controls="transparency"
            aria-selected="false">
            <span data-en="Actively Take Ownership" data-es="Responsabilidad Activa">Responsabilidad Activa</span>
          </button>
        </li>
      </ul>

      <!-- Contenido de cada valor -->
      <div class="tab-content text-center" id="valuesTabContent">
        <div class="tab-pane fade show active" id="believe_people" role="tabpanel" aria-labelledby="believe_people-tab">
          <p data-en="We trust that every member of the community has something valuable to contribute, and we want each of them to feel part of the association."
            data-es="Confiamos en que cada miembro de la comunidad tiene algo valioso que aportar, y queremos que cada uno de ellos se sienta parte de la asociación.">
            Confiamos en que cada miembro de la comunidad tiene algo valioso que aportar, y queremos que cada uno de
            ellos se sienta parte de la asociación.
          </p>
        </div>

        <div class="tab-pane fade" id="curiosity" role="tabpanel" aria-labelledby="curiosity-tab">
          <p data-en="We explore, test, and learn. As students, we know that we don’t have all the answers, but every attempt brings us closer to doing things better."
            data-es="Exploramos, probamos y aprendemos. Como estudiantes, sabemos que no tenemos todas las respuestas, pero cada intento nos acerca a hacer mejor las cosas.">
            Exploramos, probamos y aprendemos. Como estudiantes, sabemos que no tenemos todas las respuestas, pero cada
            intento nos acerca a hacer mejor las cosas.
          </p>
        </div>

        <div class="tab-pane fade" id="transparency" role="tabpanel" aria-labelledby="transparency-tab">
          <p data-en="We like to share our knowledge and processes in an open and clear way. By publishing resources, projects, and results, we foster a collaborative learning environment that is open to everyone."
            data-es="Nos gusta compartir nuestro conocimiento y procesos de manera abierta y clara. Publicando recursos, proyectos y resultados fomentamos un ambiente de aprendizaje colaborativo y abierto a todo el mundo.">
            Nos gusta compartir nuestro conocimiento y procesos de manera abierta y clara. Publicando recursos,
            proyectos y resultados fomentamos un ambiente de aprendizaje colaborativo y abierto a todo el mundo.
          </p>
        </div>

        <div class="tab-pane fade" id="activeresponsibility" role="tabpanel" aria-labelledby="activeresponsibility-tab">
          <p data-en="We value each member’s initiative and their effort to create meaningful work they can be proud of."
            data-es="Valoramos que cada miembro tome la iniciativa y se esfuerce por lograr un trabajo que le sea útil y del que se sienta orgulloso.">
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
