<!DOCTYPE html>
<html lang="en">

<?php include("assets/head.php"); ?>

<body class="d-flex flex-column min-vh-100">

<?php include("assets/nav.php"); ?>

<div class="container scroll-margin">
    <p style="color: black">Join AISC Madrid</p>
</div>

<section class="container-fluid mb-5 scroll-margin" id="get-involved">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="border-0 form-card no-hover">
                <div id="form-error" class="card-body bg-muted p-4">

                    <?php
                    // Mostrar mensajes de error según parámetro GET
                    if (isset($_GET['error'])) {
                        $errorType = $_GET['error'];
                        if ($errorType === 'validation') {
                            echo '<div class="alert alert-danger">Por favor completa todos los campos correctamente.</div>';
                        } elseif ($errorType === 'duplicate') {
                            echo '<div class="alert alert-danger">Tu correo ya está en nuestra base de datos.</div>';
                        }
                    } elseif (isset($_GET['success'])) {
                        echo '<div class="alert alert-success">¡Gracias! Hemos recibido tus datos correctamente.</div>';
                    }
                    ?>

                    <form method="POST" action="processing/recruiting.php" class="needs-validation" novalidate>
                        <!-- Nombre -->
                        <div class="mb-3">
                            <label for="name" class="form-label" data-en="Full name" data-es="Nombre completo">Nombre completo</label>
                            <input type="text" class="form-control form-input" id="name" name="name"
                                   data-es="Nombre y apellido(s)" data-en="Your name and surname"
                                   placeholder="Nombre y apellido(s)" required>
                            <div class="invalid-feedback" data-en="Please, introduce your name and surname."
                                 data-es="Por favor, introduce tu nombre y apellido(s).">Por favor, introduce tu nombre y apellido(s).
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label" data-en="E-mail" data-es="Correo electrónico">Correo electrónico</label>
                            <input type="email" class="form-control form-input" id="email" name="email"
                                   data-en="name@example.com" data-es="nombre@ejemplo.com"
                                   placeholder="nombre@ejemplo.com" required>
                            <div class="invalid-feedback" data-en="Please, introduce a valid e-mail"
                                 data-es="Por favor, introduce un correo válido.">Por favor, introduce un correo válido.
                            </div>
                        </div>

                        <!-- Position Options -->
                        <div class="mb-3">
                            <label for="position" class="form-label" data-en="Which role are you interested in?"
                                   data-es="¿Qué rol te interesa?">¿Qué rol te interesa?</label>
                            <select class="form-select form-input" id="position" name="position" required>
                                <option value="" disabled selected data-en="Select an option" data-es="Selecciona una opción">
                                    Selecciona una opción
                                </option>
                                <option value="diseno" data-en="Marketing, design and social media"
                                        data-es="Marketing, diseño y redes sociales">Marketing, diseño y redes sociales
                                </option>
                                <option value="web" data-en="Web development" data-es="Desarrollo web">Desarrollo web
                                </option>
                                <option value="dos" data-en="Both" data-es="Las dos">Las dos</option>
                                <option value="otro" data-en="Other" data-es="Otro">Otro</option>
                            </select>
                            <div class="invalid-feedback" data-en="Please select an option"
                                 data-es="Por favor, selecciona una opción.">Por favor, selecciona una opción.
                            </div>
                        </div>

                        <!-- Motivo -->
                        <div class="mb-3">
                            <label for="reason" class="form-label" data-en="Why are you interested in this role?"
                                   data-es="¿Por qué te interesa el puesto?">¿Por qué te interesa el puesto?</label>
                            <textarea class="form-control form-input" id="reason" name="reason" rows="4"
                                      placeholder="Describe brevemente tu motivación (1000 caracteres máximo)"
                                      data-en="Describe briefly your motivation (1000 characters maximum)"
                                      data-es="Describe brevemente tu motivación (1000 caracteres máximo)"
                                      maxlength="1000" required></textarea>
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
                            <button type="submit" class="btn btn-primary form-btn fw-semibold"
                                    data-en="Send" data-es="Enviar">Enviar
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</section>

<?php include('assets/footer.php'); ?>
<script src="js/language.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
