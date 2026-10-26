<!DOCTYPE html>
<html lang="en">

<?php
include("assets/db.php");

$utc = new DateTimeZone('UTC');
$madrid = new DateTimeZone('Europe/Madrid');
$now = new DateTime('now', $madrid);

// Retrieve all events, newest first
$events = $conn->query("SELECT * FROM events ORDER BY start_datetime DESC");

// Separate upcoming and past events (an event is upcoming until it ends)
$future_events = [];
$past_events = [];

while ($row = $events->fetch_assoc()) {
  if (new DateTime($row['end_datetime'], $utc) >= $now) {
    $future_events[] = $row;
  } else {
    $past_events[] = $row;
  }
}

// Soonest upcoming event first
$future_events = array_reverse($future_events);

/**
 * Render one event card. Upcoming and past cards share the markup;
 * the event-future / event-past class changes how they look.
 */
function render_event_card(array $event, bool $is_future, DateTimeZone $utc, DateTimeZone $madrid): void
{
  $type = strtolower(trim(htmlspecialchars($event['type_en'])));
  $start = (new DateTime($event['start_datetime'], $utc))->setTimezone($madrid);
  $end = (new DateTime($event['end_datetime'], $utc))->setTimezone($madrid);
  $event_class = $is_future ? 'event-future' : 'event-past';
  ?>
  <div class="col-12 col-md-6 col-lg-4 event-card <?= $event_class ?>"
    data-type="<?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?>" date="<?= (int) $end->getTimestamp() ?>">
    <a href="/events/evento.php?id=<?= (int) $event['id'] ?>" class="text-decoration-none text-reset">
      <div class="card h-100 shadow-sm">
        <div class="card-body p-0 position-relative">
          <div class="img-container">
            <img src="<?= htmlspecialchars(cdn_from_image_path($event['image_path'])) ?>" class="card-img-top"
              alt="<?= htmlspecialchars($event['title_es']) ?>" style="object-fit: cover;">
          </div>
          <?php if ($is_future): ?>
            <span class="badge rounded-pill position-absolute m-2 upcoming-badge" data-en="Upcoming"
              data-es="Próximamente">Próximamente</span>
          <?php else: ?>
            <span class="badge rounded-pill position-absolute m-2 past-badge" data-en="Finished"
              data-es="Finalizado">Finalizado</span>
          <?php endif; ?>

          <div class="p-3 pb-5">
            <h5 class="card-title mt-3 fw-bold" data-en="<?= htmlspecialchars($event['title_en']) ?>"
              data-es="<?= htmlspecialchars($event['title_es']) ?>">
              <?= htmlspecialchars($event['title_es']) ?>
            </h5>

            <p class="card-text">
              <i class="fas fa-calendar me-2"></i>
              <strong><?= $start->format("d/m/Y") ?></strong><br>
              <?= $start->format("H:i") ?> - <?= $end->format("H:i") ?>
            </p>

            <p class="card-text">
              <i class="fas fa-map-marker-alt me-2"></i>
              <span><?= htmlspecialchars($event['location']) ?></span>
            </p>
          </div>

          <div class="card-more-badge" data-en="More information" data-es="Saber más">
            Saber más
          </div>
        </div>
      </div>
    </a>
  </div>
  <?php
}
?>

<?php include("assets/head.php"); ?>
<div class="container scroll-margin">
  <div class="text-center px-3 px-md-5">
    <h2 class="fw-bold" style="color: var(--muted);" data-en="All the events of AISC Madrid"
      data-es="Todos los eventos de AISC Madrid">
      Todos los eventos de AISC Madrid
    </h2>
    <div class="mx-auto mb-4" style="width:60px; height:3px; background: var(--primary); border-radius:2px;"></div>
    <h6 class="lh-lg text-muted mx-auto" style="max-width: 700px;"
      data-en="Take a look at all the events we have organised so far, and the ones which are about to come."
      data-es="Echa un vistazo a todos los eventos que hemos organizado hasta ahora y a los que están por venir.">
      Echa un vistazo a todos los eventos que hemos organizado hasta ahora y a los que están por venir.
    </h6>
  </div>
</div>
<section class="section mt-5">
  <div class="px-3 px-md-5">

    <!-- Filter Buttons Row -->
    <div class="row mb-3">
      <div class="col-12 d-flex justify-content-end align-items-center gap-2">
        <div class="filters d-flex gap-2">
          <button class="filter-button active" data-filter="all" data-en="All" data-es="Todos">Todos</button>
          <button class="filter-button" data-filter="event" data-en="Events" data-es="Eventos">Eventos</button>
          <button class="filter-button" data-filter="workshop" data-en="Workshops" data-es="Talleres">Talleres</button>
        </div>
      </div>
    </div>

    <!-- Upcoming events -->
    <div class="events-section events-section--upcoming mb-5">
      <div class="events-section-title">
        <span class="events-live-dot" aria-hidden="true"></span>
        <h3 data-en="Upcoming events" data-es="Próximos eventos">Próximos eventos</h3>
        <span class="events-count"><?= count($future_events) ?></span>
      </div>

      <?php if ($future_events): ?>
        <div class="row g-4">
          <?php foreach ($future_events as $event) {
            render_event_card($event, true, $utc, $madrid);
          } ?>
        </div>
      <?php endif; ?>
      <p class="events-empty text-muted mb-0" <?= $future_events ? 'hidden' : '' ?>
        data-en="There are no upcoming events right now. Follow us so you don't miss the next one!"
        data-es="Ahora mismo no hay eventos próximos. ¡Síguenos para no perderte el siguiente!">
        Ahora mismo no hay eventos próximos. ¡Síguenos para no perderte el siguiente!
      </p>
    </div>

    <!-- Past events -->
    <?php if ($past_events): ?>
      <div class="events-section events-section--past">
        <div class="events-section-title">
          <h3 data-en="Past events" data-es="Eventos pasados">Eventos pasados</h3>
          <span class="events-count"><?= count($past_events) ?></span>
        </div>

        <div class="row g-4">
          <?php foreach ($past_events as $event) {
            render_event_card($event, false, $utc, $madrid);
          } ?>
        </div>
      </div>
    <?php endif; ?>

  </div>
</section>

<?php $conn->close(); ?>

<?php include("assets/nav.php"); ?>

<?php include('assets/footer.php'); ?>

<script src="js/navbar.js"></script>
<script src="js/filtering_buttons.js"></script>
<script src="js/ordering_button.js"></script>
<script src="js/char_counter.js"></script>
</body>

</html>
