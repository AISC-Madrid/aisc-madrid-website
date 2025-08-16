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
                    // Recuperar valores previos
                    $name_value = $_GET['name'] ?? '';
                    $email_value = $_GET['email'] ?? '';
                    $position_value = $_GET['position'] ?? '';
                    $reason_value = $_GET['reason'] ?? '';
                    $consent_value = isset($_GET['consent']) ? true : false;

                    // Mensajes de error
                    if (isset($_GET['error_name'])) {
                        echo '<div class="alert alert-danger">Por favor completa tu nombre.</div>';
                    }
                    if (isset($_GET['error_email'])) {
                        echo '<div class="alert alert-danger">Por favor introduce un correo válido.</div>';
                    }
                    if (isset($_GET['error_position'])) {
                        echo '<div class="alert alert-danger">Por favor selecciona un rol.</div>';
                    }
                    if (isset($_GET['error_reason'])) {
                        echo '<div class="alert alert-danger">Por favor escribe tu motivación (máx. 1000 caracteres).</div>';
                    }
                    if (isset($_GET['error_consent'])) {
                        echo '<div class="alert alert-danger">Debes dar tu consentimiento para continuar.</div>';
                    }
                    if (isset($_GET['error_duplicate'])) {
                        echo '<div class="alert alert-danger">Solo se acepta una inscripción por correo.</div>';
                    }
                    if (isset($_GET['success'])) {
                        echo '<div class="alert alert-success">¡Gracias! Hemos recibido tus datos correctamente.</div>';
                    }
                    ?>

                    <form method="POST" action="processing/recruiting.php">
                        <!-- Nombre -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Nombre completo</label>
                            <input type="text" class="form-control" id="name" name="name"
                                   value="<?php echo htmlspecialchars($name_value); ?>" required>
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Correo electrónico</label>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?php echo htmlspecialchars($email_value); ?>" required>
                        </div>

                        <!-- Position Options -->
                        <div class="mb-3">
                            <label for="position" class="form-label">¿Qué rol te interesa?</label>
                            <select class="form-select" id="position" name="position" required>
                                <option value="" disabled <?php echo $position_value === '' ? 'selected' : ''; ?>>Selecciona una opción</option>
                                <option value="diseno" <?php echo $position_value === 'diseno' ? 'selected' : ''; ?>>Marketing, diseño y redes sociales</option>
                                <option value="web" <?php echo $position_value === 'web' ? 'selected' : ''; ?>>Desarrollo web</option>
                                <option value="dos" <?php echo $position_value === 'dos' ? 'selected' : ''; ?>>Las dos</option>
                                <option value="otro" <?php echo $position_value === 'otro' ? 'selected' : ''; ?>>Otro</option>
                            </select>
                        </div>

                        <!-- Motivo -->
                        <div class="mb-3">
                            <label for="reason" class="form-label">¿Por qué te interesa el puesto?</label>
                            <textarea class="form-control" id="reason" name="reason" rows="4" maxlength="1000"
                                      required><?php echo htmlspecialchars($reason_value); ?></textarea>
                        </div>

                        <!-- Consentimiento -->
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="consent" name="consent" <?php echo $consent_value ? 'checked' : ''; ?> required>
                            <label class="form-check-label" for="consent">
                                Doy mi consentimiento para que AISC Madrid almacene mis datos enviados para contactarme.
                            </label>
                        </div>

                        <!-- Enviar -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Enviar</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</section>

<?php include('assets/footer.php'); ?>
</body>
</html>
