<?php
// Show errors for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('../assets/db.php');
include("../assets/head.php");

// Validate and get the event ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  die("❌ Invalid event ID");
}
$event_id = (int) $_GET['id'];

// Prepare SQL to get event details
$stmt = $conn->prepare("SELECT title_es, image_path, end_datetime FROM events WHERE id = ?");
if (!$stmt) {
  die("❌ Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();
$stmt->close();
?>

<?php
if (!$event) {
  die("❌ Event not found");
} else {
  $end_datetime = $event['end_datetime'];
}

// Compare current time with end_datetime
$is_past_event = ($end_datetime < date('Y-m-d H:i:s'));
?>

<!DOCTYPE html>
<html lang="en">

<body class="d-flex flex-column min-vh-100">

  <?php include("../assets/nav.php"); ?>

  <?php if (!$is_past_event): ?>
    <!-- Available registration for event -->
    <div class="container scroll-margin">
      <div class="text-center mb-5 px-3 px-md-5">
        <h2 class="fw-bold mb-4" style="color: var(--muted);" data-en="Event Registration"
          data-es="Inscripción al Evento">
          Inscripción al Evento
        </h2>
        <h4 class="fw-bold" style="color: var(--primary);"><?= htmlspecialchars($event['title_es']) ?></h4>
        <div class="mx-auto mt-3 mb-4" style="width:60px; height:3px; background: var(--primary); border-radius:2px;">
        </div>
        <!-- Event image if exists -->
        <?php if (!empty($event['image_path'])): ?>
          <img src="<?= htmlspecialchars($event['image_path']) ?>" alt="<?= htmlspecialchars($event['title_es']) ?>"
            class="img-fluid rounded mb-4" style="max-width: 80%; height: auto;">
        <?php endif; ?>
        <p class="text-muted" data-en="Fill out the form to secure your spot at the event."
          data-es="Rellena el formulario para asegurar tu plaza en el evento.">
          Rellena el formulario para asegurar tu plaza en el evento.</p>
      </div>

      <section class="container-fluid mb-5">
        <div class="row justify-content-center">
          <div class="col-md-8 col-lg-6">
            <div class="border-0 form-card no-hover">
              <div class="card-body bg-muted p-4">

                <?php
                // Mensajes de éxito o error
                if (isset($_GET['success']))
                  echo '<div class="alert alert-success">¡Gracias! Te has inscrito correctamente.</div>';
                if (isset($_GET['error_duplicate']))
                  echo '<div class="alert alert-danger">Ya existe una inscripción con este correo para este evento.</div>';
                if (isset($_GET['error_validation']))
                  echo '<div class="alert alert-danger">Por favor, completa todos los campos correctamente.</div>';
                ?>

                <form method="POST" action="/processing/register_event.php">
                  <input type="hidden" name="event_id" value="<?= $event_id ?>">

                  <div class="mb-3">
                    <label for="name" class="form-label" style="color: black" data-en="Full name"
                      data-es="Nombre y apellidos">Nombre y apellidos</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Michael Scott" required>
                  </div>
                  <div class="mb-3">
                    <label for="email" class="form-label" style="color: black" data-en="Email"
                      data-es="Correo electrónico">Correo electrónico</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com"
                      required>
                  </div>
                  <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="consent" name="consent" required>
                    <label class="form-check-label form-text" for="consent"
                      data-en="I give my consent for AISC Madrid to store my data for the management of this event."
                      data-es="Doy mi consentimiento para que AISC Madrid almacene mis datos para la gestión de este evento.">
                      Doy mi consentimiento para que AISC Madrid almacene mis datos para la gestión de este evento.
                    </label>
                    <a class="form-check-label form-text" href="../terms_conditions.php" target="_blank"
                      data-en="(Read terms and conditions)" data-es="(Leer términos y condiciones)">
                      (Leer términos y condiciones)
                    </a>
                  </div>
                  <div class="d-grid">
                    <button type="submit" class="btn btn-primary form-btn fw-semibold" data-en="Register"
                      data-es="Inscribirme">Inscribirme</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>

  <?php else: ?>
    <!-- Registration closed message -->
    <div class="container text-center my-5">
      <h2 class="fw-bold mb-3 scroll-margin" style="color: var(--muted);" data-en="Event Closed"
        data-es="Evento Finalizado">Evento Finalizado</h2>
      <h4 class="fw-bold mb-3" style="color: var(--primary);"
        data-en="<?= htmlspecialchars($event['title_es']) ?> is already over!"
        data-es="<?= htmlspecialchars($event['title_es']) ?> ya ha finalizado!">
        <?= htmlspecialchars($event['title_es']) ?> ya ha finalizado!
      </h4>
      <div class="mx-auto mt-3 mb-4" style="width:60px; height:3px; background: var(--primary); border-radius:2px;"></div>
      <p class="text-muted" data-en="Join the newsletter to stay up to date with everything we do and not miss a thing."
        data-es="Únete a la newsletter para enterarte de todo lo que hacemos y no perderte nada.">
        Únete a la newsletter para enterarte de todo lo que hacemos y no perderte nada.</p>
      <section class="container-fluid mb-5" id="newsletter-section">
        <div class="row justify-content-center">
          <div class="col-md-8 col-lg-6">
            <div class=" border-0 form-card no-hover">
              <div id="form-error" class="card-body bg-muted p-4">
                <form method="POST" action="processing/phpmailer.php">
                  <!-- Name -->
                  <div class="mb-3">
                    <label for="name" class="form-label" data-en="Full name" data-es="Nombre y apellidos">Nombre y
                      apellidos</label>
                    <input type="text" class="form-control form-input" id="name" name="name"
                      data-es="Nombre y apellido(s)" data-en="Full name" placeholder="Michael Scott" required>
                  </div>

                  <!-- Email -->
                  <div class="mb-3">
                    <label for="email" class="form-label" data-en="E-mail" data-es="Correo electrónico">Correo
                      electrónico</label>
                    <input type="email" class="form-control form-input" id="email" name="email" data-en="name@example.com"
                      data-es="nombre@ejemplo.com" placeholder="name@example.com" required>
                  </div>
                  <!-- Consent -->
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
                  </div>

                  <!-- Send -->
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
  <?php endif; ?>


  <?php include('../assets/footer.php'); ?>

  <script src="/js/navbar.js"></script>
</body>

</html>