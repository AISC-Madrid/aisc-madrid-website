<!DOCTYPE html>
<html lang="en">
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
include("assets/db.php");

// Get current datetime
$now = date("Y-m-d H:i:s");

// Retrieve all events ordered by start_datetime
$result = $conn->query("SELECT * FROM events ORDER BY start_datetime DESC");

// Separate future and past events
$future_events = [];
$past_events = [];

while ($row = $result->fetch_assoc()) {
  // Note: Use time() for comparison
  if (strtotime($row['end_datetime']) >= time()) {
    $future_events[] = $row;
  } else {
    $past_events[] = $row;
  }
}

// To display only 6 events (all future and rest past)
$limit = 6;
$events_to_display = [];

// First event to apear should be the soonest upcoming one
$future_events = array_reverse($future_events);

// Take up to 6 of the newest future events.
$events_to_display = array_slice($future_events, 0, $limit);

// Calculate how many slots are left.
$needed_from_past = $limit - count($events_to_display);

// If slots remain, fill them with the most recent past events.
if ($needed_from_past > 0) {
  // Take the required number of the newest past events (already sorted DESC).
  $past_to_add = array_slice($past_events, 0, $needed_from_past);

  // Merge the past events onto the end of the future events array.
  $events_to_display = array_merge($events_to_display, $past_to_add);
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
        <h1 class="text-dark fw-bold display-3"> <span style="color:var(--primary);">AI</span> <br> Student Collective
          <br> <span style="color:var(--secondary);">Madrid</span></h1>
        <h6 class="lh-lg text-muted" data-en="UC3M Student Association Interested in AI. We organize hands-on workshops focused on acquiring 
          in-demand skills, industry events, and connect students through an international community."
          data-es="Asociación de Estudiantes de la UC3M interesados en la IA. Organizamos talleres prácticos orientados 
          a adquirir habilidades demandadas, eventos con la industria y conectamos estudiantes a través de una comunidad internacional.">
          Asociación de Estudiantes de la UC3M interesados en la IA. Organizamos talleres prácticos orientados 
          a adquirir habilidades demandadas, eventos con la industria y conectamos estudiantes a través de una comunidad internacional.</h6>
      </div>
      <div
        class="col-12 col-md-5 order-1 order-md-2 d-flex flex-column align-items-center align-items-md-end justify-content-center ">
        <img style="width: 75%;" src="images/logos/PNG/AISC Logo Color.png" alt="Logotipo de la asociación AISC">
      </div>
    </header>
    </section>

    <section class="section scroll-margin w-100 px-3 px-md-5" id="events">
      <div class="container-fluid">
        <h2 class="text-center mb-4 fw-bold">
          <span style="color: var(--muted);" data-en="Events" data-es="Eventos">Eventos</span>
        </h2>
        <div class="mx-auto mb-4" style="width:60px; height:3px; background: var(--primary); border-radius:2px;"></div>

        <div class="row g-4" style="width:100%;">
          <?php
          // Loop through the final list of up to 6 events (future prioritized, newest first)
          foreach ($events_to_display as $event):

            // Determine the appropriate CSS class for styling/scripting
            // Uses the same logic you used to separate them initially
            $is_future = strtotime($event['end_datetime']) >= time();
            $event_class = $is_future ? 'event-future' : 'event-past';
            ?>
            <div class="col-md-6 col-lg-4 <?= $event_class ?>">
              <a href="/events/evento.php?id=<?= $event['id'] ?>" class="text-decoration-none text-reset">
                <div class="card h-100 w-100 shadow-sm">
                  <div class="card-body p-0 position-relative">
                    <div class="img-container">
                      <img src="<?= htmlspecialchars($event['image_path']) ?>" class="card-img-top"
                        alt="<?= htmlspecialchars($event['title_es']) ?>" style="object-fit: cover;">
                    </div>
                    <?php if ($is_future): ?>
                      <span class="badge rounded-pill position-absolute m-2 upcoming-badge" data-en="Upcoming"
                        data-es="Próximo">Próximamente</span>
                    <?php endif; ?>
                    <div class="p-3 pb-5">
                      <h5 class="card-title mt-3 fw-bold" data-en="<?= htmlspecialchars($event['title_en']) ?>"
                        data-es="<?= htmlspecialchars($event['title_es']) ?>">
                        <?= htmlspecialchars($event['title_es']) ?>
                      </h5>
                      <p class="card-text">
                        <i class="fas fa-calendar me-2"></i>
                        <strong><?= date("d/m/Y", strtotime($event['start_datetime'])) ?></strong><br>
                        <?= date("H:i", strtotime($event['start_datetime'])) ?> -
                        <?= date("H:i", strtotime($event['end_datetime'])) ?>
                      </p>
                      <p class="card-text"><i
                          class="fas fa-map-marker-alt me-2"></i><span><?= htmlspecialchars($event['location']) ?></span>
                      </p>
                    </div>
                    <div class="card-more-badge" data-en="More information" data-es="Saber más">Saber más</div>
                  </div>
                </div>
              </a>
            </div>
          <?php endforeach; ?>
        </div>
        <a class="btn btn-custom w-100 w-lg-auto mt-4" href="events.php" role="button" data-en="View all events"
          data-es="Ver todos los eventos">
          Ver todos los eventos
        </a>
      </div>
    </section>
    <?php $conn->close(); ?>

    <section class="section" id="team">
      <div class="container scroll-margin">
        <h2 class="text-center mb-4 fw-bold">
          <span style="color: var(--muted);" data-en="Meet the team" data-es="Conoce al equipo">
            Conoce al equipo
          </span>
        </h2>
        <div class="mx-auto mb-4" style="width:60px; height:3px; background: var(--primary); border-radius:2px;"></div>

        <!-- Board members row. Get info from DB -->
        <?php
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        include("assets/db.php");

        // Retrieve all board members
        $result = $conn->query("SELECT * FROM members WHERE board = 'yes' ORDER BY id ASC");
        $num_board_members = $result->num_rows;

        // Determine Bootstrap column class dynamically
        if ($num_board_members <= 1) {
          $col_class = 'col-lg-12';
        } elseif ($num_board_members == 2) {
          $col_class = 'col-lg-6';
        } elseif ($num_board_members == 3) {
          $col_class = 'col-lg-4';
        } elseif ($num_board_members == 4) {
          $col_class = 'col-lg-3';
        } else {
          $col_class = 'col-lg-2'; // 5 or more members
        }
        ?>

        <div class="mt-5 row justify-content-center">
          <?php foreach ($result as $board_member): ?>
            <div class="col-12 col-sm-6 col-md-4 <?= $col_class ?> mb-4">
              <div class="team-box text-center">
                <div class="team-wrapper">
                  <div class="team-board_member">
                    <a href="<?= $board_member['socials'] ?>" target="_blank">
                      <img src="<?= htmlspecialchars($board_member['image_path']) ?>"
                        alt="<?= $board_member['full_name'] ?>" class="img-fluid rounded">
                    </a>
                  </div>
                </div>
                <h5 class="mt-3" style="color: var(--background)"><?= $board_member['full_name'] ?></h5>
                <p class="text-muted" data-en="<?= htmlspecialchars($board_member['position_en']) ?>"
                  data-es="<?= htmlspecialchars($board_member['position_es']) ?>">
                </p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <!-- End board members row -->

        <!-- Button aligned with first column on desktop, full-width on mobile -->
        <div class="row mt-4">
          <div class="col-12 d-flex justify-content-center justify-content-lg-start">
            <a class="btn btn-custom w-100 w-lg-auto" href="team.php" role="button" data-en="See all members"
              data-es="Ver todos los miembros">
              Ver todos los miembros
            </a>
          </div>
        </div>
      </div>
    </section>

    <section class="container-fluid mb-5 scroll-margin" id="newsletter">
      <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
          <div class=" border-0 form-card no-hover">
            <div id="form-error" class="card-body bg-muted p-4">
              <h4 class="fw-bold text-center mb-3" style="color: var(--secondary);" data-en="Don't miss a thing!"
                data-es="¡Mantente al tanto!">¡Mantente al tanto!</h4>
              <p class="text-muted text-center mb-4 form-subtext"
                data-en="Send your name and email to stay informed and join our AI community at UC3M."
                data-es="Envía tu nombre y correo electrónico para mantenerte informado y unirte a nuestra comunidad de IA en UC3M.">
                Envía tu nombre y correo electrónico para mantenerte informado y unirte a nuestra comunidad de IA en
                UC3M.
              </p>

              <form method="POST" action="processing/phpmailer.php" class="needs-validation" novalidate>
                <!-- Nombre -->
                <div class="mb-3">
                  <label for="name" class="form-label" data-en="Full name" data-es="Nombre y apellidos">Nombre y
                    apellidos</label>
                  <input type="text" class="form-control form-input" id="name" name="name"
                    data-es="Nombre y apellido(s)" data-en="Full name" placeholder="Michael Scott" required>
                  <div class="invalid-feedback" data-en="Please, introduce your name and surname."
                    data-es="Por favor, introduce tu nombre y apellido(s).">Por favor, introduce tu nombre y
                    apellido(s).</div>
                </div>

                <!-- Email -->
                <div class="mb-3">
                  <label for="email" class="form-label" data-en="E-mail" data-es="Correo electrónico">Correo
                    electrónico</label>
                  <input type="email" class="form-control form-input" id="email" name="email" data-en="name@example.com"
                    data-es="nombre@ejemplo.com" placeholder="name@example.com" required>
                  <div class="invalid-feedback" data-en="Please, introduce a valid e-mail"
                    data-es="Por favor, introduce un correo válido.">Por favor, introduce un correo válido.</div>
                </div>

                <!-- Consentimiento -->
                <div class="form-check mb-3">
                  <input class="form-check-input" type="checkbox" id="consent" name="consent" required>
                  <label class="form-check-label form-text" for="consent"
                    data-en="I consent to AISC Madrid storing my submitted data to contact me."
                    data-es="Doy mi consentimiento para que AISC Madrid almacene mis datos enviados para contactarme.">
                    Doy mi consentimiento para que AISC Madrid almacene mis datos enviados para contactarme.
                  </label>
                  <a class="form-check-label form-text" href="terms_conditions.php" target="_blank"
                    data-en="(Read terms and conditions)" data-es="(Leer términos y condiciones)">
                    (Leer términos y condiciones)
                  </a>
                  <div class="invalid-feedback" data-en="You must give permission to continue"
                    data-es="Debes dar tu consentimiento para continuar. ">Debes dar tu consentimiento para continuar.
                  </div>
                </div>

                <!-- Enviar -->
                <div class="d-grid">
                  <button type="submit" class="btn btn-primary form-btn fw-semibold" data-en="Send"
                    data-es="Enviar">Enviar</button>
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

</body>

</html>