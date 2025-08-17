<!DOCTYPE html>
<html lang="en">

<?php include("assets/head.php"); ?>

<body class="d-flex flex-column min-vh-100">

<?php include("assets/nav.php"); ?>

<section>
  <div class="container scroll-margin">
    <div class="text-center mb-5 px-3 px-md-5">
      <h2 class="fw-bold mb-4" style="color: var(--muted);" data-en="Join the AISC Madrid team" data-es="Únete al equipo de AISC Madrid">
        Únete al equipo de AISC Madrid
      </h2>
      <div class="mx-auto mb-4" style="width:60px; height:3px; background: var(--primary); border-radius:2px;"></div>
    </div>
      <h6 class="lh-lg text-muted mx-auto" style="max-width: 700px"
      data-en="At AISC Madrid, we are looking for new members for the <stron>2025/2026 academic year.</strong>
      <br>
      If you are pursuing a Bachelor's or Master's degree at UC3M, are committed and enthusiastic about what you do, enjoy working in a team, and want to contribute your ideas, this is the place for you."
      data-es="En AISC Madrid buscamos nuevas incorporaciones para el <strong>curso 2025/2026.</strong>
      <br>
      Si estudias un Grado o Máster en la UC3M, eres comprometido y entusiasta con lo que haces, te gusta trabajar en equipo y quieres aportar tus ideas, ¡este es tu sitio!.">
      En AISC Madrid buscamos nuevas incorporaciones para el <strong>curso 2025/2026.</strong>
      <br>
      Si estudias un Grado o Máster en la UC3M, eres comprometido y entusiasta con lo que haces, te gusta trabajar en equipo y quieres aportar tus ideas, ¡este es tu sitio!.
      </h6>
    <section class="mb-4 mt-5" style="color: var(--primary);">
    <h5 class="fw-bold" data-es="Redes Sociales, Diseño y Marketing" data-en="Social Media, Design, and Marketing">
      Redes Sociales, Diseño y Marketing
    </h5>
      <p class="lh-lg" style="color: black" data-es="Personas creativas para gestionar redes, crear contenido y diseñar materiales. <br>
      Valoramos experiencia en edición de foto/vídeo y  gestión de redes (Instagram, LinkedIn)."
      data-en="Creative individuals to manage social media, create content, and design materials. <br>
      Experience in photo/video editing and social media management (Instagram, LinkedIn) is a plus.">
      Personas creativas para gestionar redes, crear contenido y diseñar materiales. <br>
      Valoramos experiencia en edición de foto/vídeo y  gestión de redes (Instagram, LinkedIn).
      </p>
    <h5 class="fw-bold mt-4" data-es="Desarrollo Web" data-en="Web Development">
      Desarrollo Web
    </h5>
      <p class="lh-lg" style="color: black" data-es="Interesados en mantener y mejorar la web de la asociación. <br>
      Conocimientos en HTML, CSS, JS, PHP y Git/GitHub son un plus."
      data-en="Interested in maintaining and improving the association’s website.<br>
      Knowledge of HTML, CSS, JS, PHP, and Git/GitHub is a plus.">
      Interesados en mantener y mejorar la web de la asociación.<br>
      Conocimientos en HTML, CSS, JS, PHP y Git/GitHub son un plus.
      </p>
    <h5 class="fw-bold mt-4" data-es="Eventos y Talleres" data-en="Events and Workshops">
      Eventos y Talleres
    </h5>
      <p class="lh-lg" style="color: black" data-es="Personas con iniciativa para diseñar y coordinar talleres y eventos, así como contactar con ponentes. <br>
      Si te motiva la divulgación tecnológica y coordinar eventos, este rol es para ti."
      data-en="IIndividuals with initiative to design and coordinate workshops and events, as well as reach out to speakers.<br>
      If you are motivated by tech outreach and event coordination, this role is for you.">
      Personas con iniciativa para diseñar y coordinar talleres y eventos, así como contactar con ponentes. <br>
      Si te motiva la divulgación tecnológica y coordinar eventos, este rol es para ti.

      </p>
    </section>
  </div>

</section>
<div class="text-center mt-4">
  <h5 class="fw-bold"style="color: var(--secondary);" data-es="Rellena el formulario y únete a nuestro equipo" data-en="Fill out the form and join our team">
    Rellena el formulario y únete a nuestro equipo
  </h5>
  <p class="text-muted mb-0" style="max-width: 600px; margin: 0 auto;" data-es="Estamos deseando conocerte y contar con tu talento en la comunidad AISC Madrid." data-en="We can’t wait to meet you and have your talent in the AISC Madrid community.">
    Estamos deseando conocerte y contar con tu talento en la comunidad AISC Madrid.
  </p>
</div>

<!-- Join Form Section -->
<section class="container-fluid mb-5 scroll-margin" id="get-involved" style="margin-top: 0;">
  <div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
      <div class="border-0 form-card no-hover">
        <div id="form-error" class="card-body bg-muted p-4">

          <?php
          // Get pre-filled values from GET parameters
          $name_value = $_GET['name'] ?? '';
          $email_value = $_GET['email'] ?? '';
          $position_value = $_GET['position'] ?? '';
          $reason_value = $_GET['reason'] ?? '';
          $consent_value = isset($_GET['consent']) ? true : false;

          // Error messages
          if (isset($_GET['error_name'])) echo '<div class="alert alert-danger">Por favor completa tu nombre.</div>';
          if (isset($_GET['error_email'])) echo '<div class="alert alert-danger">Por favor introduce un correo válido.</div>';
          if (isset($_GET['error_position'])) echo '<div class="alert alert-danger">Por favor selecciona un rol.</div>';
          if (isset($_GET['error_reason'])) echo '<div class="alert alert-danger">Por favor escribe tu motivación (máx. 1000 caracteres).</div>';
          if (isset($_GET['error_consent'])) echo '<div class="alert alert-danger">Debes dar tu consentimiento para continuar.</div>';
          if (isset($_GET['error_duplicate'])) echo '<div class="alert alert-danger">Solo se acepta una inscripción por correo.</div>';
          if (isset($_GET['success'])) echo '<div class="alert alert-success">¡Gracias! Hemos recibido tus datos correctamente. En breves nos pondremos en contacto.</div>';
          ?>

          <form method="POST" action="processing/recruiting.php">
            <div class="mb-3">
              <label for="name" class="form-label" style="color: black">Nombre completo</label>
              <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name_value); ?>" required>
            </div>
            <div class="mb-3">
              <label for="email" class="form-label" style="color: black">Correo electrónico</label>
              <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email_value); ?>" required>
            </div>
            <div class="mb-3">
              <label for="position" class="form-label" style="color: black">¿Qué rol te interesa?</label>
              <select class="form-select" id="position" name="position" required>
                <option value="" disabled <?php echo $position_value === '' ? 'selected' : ''; ?>>Selecciona una opción</option>
                <option value="diseno" <?php echo $position_value === 'diseno' ? 'selected' : ''; ?>>Redes Sociales, Diseño y Marketing</option>
                <option value="web" <?php echo $position_value === 'web' ? 'selected' : ''; ?>>Desarrollo web</option>
                <option value="events" <?php echo $position_value === 'events' ? 'selected' : ''; ?>>Eventos y Talleres</option>
                <option value="various" <?php echo $position_value === 'various' ? 'selected' : ''; ?>>Varios (especificar en siguiente apartado)</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="reason" class="form-label" style="color: black">¿Por qué te interesa el puesto?</label>
              <textarea class="form-control" id="reason" name="reason" rows="4" maxlength="1000" required><?php echo htmlspecialchars($reason_value); ?></textarea>
                <div id="char-count" class="form-text text-end" style="color: gray;">
                  0 / 1000 characters
                </div>
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

<?php include('assets/footer.php'); ?>
<script src="js/char_counter.js"></script>
</body>
</html>
