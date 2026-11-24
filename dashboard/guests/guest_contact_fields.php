<?php
// Campos del perfil de contacto de un guest, compartidos por create_guest.php y edit_guest.php.
// Espera $contact_values: array con 'email', 'phone' y 'linkedin_url' (lo enviado o lo guardado).
$contact_values ??= [];
$escaped_field = fn(string $field) => htmlspecialchars((string) ($contact_values[$field] ?? ''));
?>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                        <input type="email" name="email" class="form-control" maxlength="<?= GUEST_EMAIL_MAX_LENGTH ?>"
                                            placeholder="ej: juan@example.com"
                                            value="<?= $escaped_field('email') ?>">
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Teléfono</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                        <input type="text" name="phone" class="form-control"
                                            maxlength="<?= GUEST_PHONE_MAX_LENGTH ?>" placeholder="ej: +34 600 000 000"
                                            value="<?= $escaped_field('phone') ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">LinkedIn</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-linkedin"></i></span>
                                    <input type="url" name="linkedin_url" class="form-control" maxlength="<?= GUEST_LINKEDIN_MAX_LENGTH ?>"
                                        placeholder="ej: https://www.linkedin.com/in/juangarcia"
                                        value="<?= $escaped_field('linkedin_url') ?>">
                                </div>
                                <small class="text-muted">Datos de contacto opcionales</small>
                            </div>
