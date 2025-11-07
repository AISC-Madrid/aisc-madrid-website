<!DOCTYPE html>
<html lang="en">

<?php include("assets/head.php"); ?>

<body class="d-flex flex-column min-vh-100">

<?php include("assets/nav.php"); ?>

  <div class="container scroll-margin">
    <div class="text-center mb-5 px-3 px-md-5">
      <h2 class="fw-bold mb-4" style="color: var(--muted);" data-en="Join the AISC Madrid team" data-es="Únete al equipo de AISC Madrid">
        Únete al equipo de AISC Madrid
      </h2>
      <div class="mx-auto mb-4" style="width:60px; height:3px; background: var(--primary); border-radius:2px;"></div>
    </div>

<div class="text-center" style="margin-top: 2rem;" id="collaboration-form">
  <h4 class="fw-bold"style="color: var(--secondary);" data-es="Rellena el formulario para proponer un evento o taller" data-en="Fill out the form to propose an event or workshop">
    Rellena el formulario para proponer un evento o taller
  </h4>
  <p class="text-muted mb-0" style="max-width: 600px; margin: 0 auto;" data-es="Desde AISC estamos siempre a abiertos a nuevas ideas para eventos y talleres con estudiantes, investigadores o miembros del sector.
    <br>
    ¡Estamos deseando oír tus ideas y poder colaborar!"
    data-en="At AISC, we are always open to new ideas for events and workshops with students, researchers or industry members.
    <br>
    We’re looking forward to hearing your ideas and collaborating!">
    Desde AISC estamos siempre a abiertos a nuevas ideas para eventos y talleres con estudiantes, investigadores o miembros del sector.
    <br>
    ¡Estamos deseando oír tus ideas y poder colaborar!
  </p>
</div>

<!-- Join Form Section -->
<section class="container-fluid mb-5" style="margin-top: 0;">
  <div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
      <div class="border-0 form-card no-hover">
        <div id="form-error" class="card-body bg-muted p-4">

          <?php
          // Get pre-filled values from GET parameters
          $name = $_GET['name'] ?? '';
          $email = $_GET['email'] ?? '';
          $event_name = $_GET['event_name'] ?? '';
          $description = $_GET['description'] ?? '';
          $language = $_GET['language'] ?? '';
          $consent_value = isset($_GET['consent']) ? true : false;

          // Error messages
          if (isset($_GET['error_name'])) echo '<div class="alert alert-danger">Por favor completa tu nombre.</div>';
          if (isset($_GET['error_email'])) echo '<div class="alert alert-danger">Por favor introduce un correo válido.</div>';
          if (isset($_GET['error_event_name'])) echo '<div class="alert alert-danger">Por favor escribe un nombre de evento/taller (máx. 100 caracteres).</div>';
          if (isset($_GET['error_description'])) echo '<div class="alert alert-danger">Por favor escribe una descripción (máx. 1000 caracteres).</div>';
          if (isset($_GET['error_language'])) echo '<div class="alert alert-danger">Debes elegir un idioma para continuar.</div>';
          if (isset($_GET['error_consent'])) echo '<div class="alert alert-danger">Debes dar tu consentimiento para continuar.</div>';
          if (isset($_GET['error_duplicate'])) echo '<div class="alert alert-danger">Solo se acepta una inscripción por correo.</div>';
          if (isset($_GET['success'])) echo '<div class="alert alert-success">¡Gracias! Hemos recibido tus datos correctamente. En breves nos pondremos en contacto.</div>';
          ?>

          <form method="POST" action="processing/collaborations.php">
            <div class="mb-3">
              <label for="name" class="form-label" style="color: black"
              data-en="Full name"
              data-es="Nombre y apellidos">Nombre y apellidos</label>
              <input type="text" class="form-control" id="name" name="name" placeholder="Michael Scott" value="<?php echo htmlspecialchars($name); ?>" required>
            </div>
            <div class="mb-3">
              <label for="email" class="form-label" style="color: black"
              data-en="Email"
              data-es="Correo electrónico">Correo electrónico</label>
              <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div class="mb-3">
              <label for="event_name" class="form-label" style="color: black"
              data-en="Provisional name of the event/workshop"
              data-es="Nombre orientativo del evento/taller">Nombre orientativo del evento/taller</label>
              <textarea class="form-control" id="event_name" name="event_name" rows="4" placeholder="Max. 100 characters" maxlength="100" required><?php echo htmlspecialchars($event_name); ?></textarea>
              <div id="char-count" class="form-text text-end" style="color: gray;">
                0 / 100 characters
              </div>
            </div>
            <div class="mb-3">
              <label for="description" class="form-label" style="color: black"
              data-en="Brief description of the event/workshop"
              data-es="Descripcón breve del evento/taller">Descripcón breve del evento/taller</label>
              <textarea class="form-control" id="description" name="description" rows="4" placeholder="Max. 1000 characters" maxlength="1000" required><?php echo htmlspecialchars($description); ?></textarea>
                <div id="char-count" class="form-text text-end" style="color: gray;">
                  0 / 1000 characters
                </div>
            </div>
            <div class="mb-3">
          <label for="language" class="form-label" style="color: black"
            data-en="Language of the event/workshop"
            data-es="Idioma del evento/taller">
            Idioma del evento/taller
          </label>

          <select class="form-select" id="language" name="language" required>
            <option value="" selected disabled
              data-en="Select the language"
              data-es="Selecciona el idioma">
              Selecciona el idioma
            </option>
            <option value="spanish"
              data-en="Spanish"
              data-es="Español">
              Español
            </option>
            <option value="english"
              data-en="English"
              data-es="Inglés">
              Inglés
            </option>
          </select>
        </div>
            <!-- Consent -->
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
            <div class="d-grid">
              <button type="submit" class="btn btn-primary form-btn fw-semibold"
              data-es="Enviar"
              data-en="Send"
              >Enviar</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
</div>

<?php include('assets/footer.php'); ?>

<script src="js/language.js"></script>
<script src="js/navbar.js"></script>
<script src="js/char_counter.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
