<!DOCTYPE html>
<html lang="en">

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include("assets/db.php");

// Get current datetime
$now = date("Y-m-d H:i:s");

// Retrieve all events ordered by start_datetime
$events = $conn->query("SELECT * FROM events ORDER BY start_datetime DESC");
?>

<?php include("assets/head.php"); ?>
  <div class="container scroll-margin">
    <div class="text-center px-3 px-md-5">
      <h2 class="fw-bold" style="color: var(--muted);" data-en="All the events of AISC Madrid" data-es="Todos los eventos de AISC Madrid">
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

<section class="section w-100 px-3 px-md-5 mt-5">
    <div class="row g-4" style="width:100%;">
        <?php foreach ($events as $event): ?>
            <div class="col-md-6 col-lg-4 event-future">
                <a href="/events/evento.php?id=<?= $event['id'] ?>" class="text-decoration-none text-reset">
                    <div class="card h-100 w-100 shadow-sm">
                        <div class="card-body p-0 position-relative">
                            <div class="img-container">
                                <img src="<?= htmlspecialchars($event['image_path']) ?>" class="card-img-top" alt="<?= htmlspecialchars($event['title_es']) ?>" style="object-fit: cover;">
                            </div>
                            <div class="p-3 pb-5">
                                <h5 class="card-title mt-3 fw-bold" data-en="<?= htmlspecialchars($event['title_en']) ?>" data-es="<?= htmlspecialchars($event['title_es']) ?>">
                                    <?= htmlspecialchars($event['title_es']) ?>
                                </h5>
                                <p class="card-text">
                                    <i class="fas fa-calendar me-2"></i>
                                    <strong><?= date("d/m/Y", strtotime($event['start_datetime'])) ?></strong><br>
                                    <?= date("H:i", strtotime($event['start_datetime'])) ?> - <?= date("H:i", strtotime($event['end_datetime'])) ?>
                                </p>
                                <p class="card-text"><i class="fas fa-map-marker-alt me-2"></i><span><?= htmlspecialchars($event['location']) ?></span></p>
                            </div>
                            <div class="card-more-badge" data-en="More information" data-es="Saber más">Saber más</div>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>

    </div>
</section>
<?php $conn->close(); ?>

<?php include("assets/nav.php"); ?>

<?php include('assets/footer.php'); ?>

<script src="js/language.js"></script>
<script src="js/navbar.js"></script>
<script src="js/char_counter.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
