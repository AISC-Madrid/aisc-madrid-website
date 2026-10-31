<?php
session_start(); // Start the session

// Check if the user is logged in
$allowed_roles = ['admin', 'events'];
if (!isset($_SESSION['activated']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: /");
    exit();
}


include('../assets/db.php'); // Your $conn mysqli connection
include('../assets/csrf.php'); // Token for the translate endpoint
require_once __DIR__ . '/../mails/reminder_events/reminder_text.php';

// Initialize variables for the form
$title_es = $title_en = $type_es = $type_en = '';
$description_es = $description_en = $location = '';
$start_datetime = $end_datetime = $image_path = '';
$speaker = '';
$company = '';
$requires_registration = 0;
$reminder_enabled = 0;
$reminder_days_before = 2;
$reminder_custom_text = '';
$status = 'draft';
$registration_count = 0;


// Check if an ID is passed
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $event_id = (int)$_GET['id'];

    $stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $event = $result->fetch_assoc();

    if ($event) {
        $status = $event['status'];
        $title_es = $event['title_es'];
        $title_en = $event['title_en'] ?? '';
        $type_es = $event['type_es'] ?? '';
        $type_en = $event['type_en'] ?? '';
        $speaker = $event['speaker'];
        $company = $event['company'] ?? '';
        $description_es = $event['description_es'] ?? '';
        $description_en = $event['description_en'] ?? '';
        $location = $event['location'] ?? '';
        // 1. Convertimos el inicio de UTC a Madrid para el formulario (un borrador puede no tener fechas)
        if (!empty($event['start_datetime'])) {
            $start_dt = new DateTime($event['start_datetime'], new DateTimeZone('UTC'));
            $start_dt->setTimezone(new DateTimeZone('Europe/Madrid'));
            $start_datetime = $start_dt->format('Y-m-d\TH:i'); // Formato requerido por datetime-local
        }

        // 2. Convertimos el fin de UTC a Madrid para el formulario
        if (!empty($event['end_datetime'])) {
            $end_dt = new DateTime($event['end_datetime'], new DateTimeZone('UTC'));
            $end_dt->setTimezone(new DateTimeZone('Europe/Madrid'));
            $end_datetime = $end_dt->format('Y-m-d\TH:i');
        }
        $image_path = $event['image_path'] ?? '';
        $youtube_url = $event['youtube_url'];
        $requires_registration = $event['requires_registration'];
        $reminder_enabled = $event['reminder_enabled'];
        $reminder_days_before = $event['reminder_days_before'];
        $reminder_custom_text = $event['reminder_custom_text'] ?? '';

        // Shown in the confirmation before unpublishing
        $count_stmt = $conn->prepare("SELECT COUNT(*) FROM event_registrations WHERE event_id = ?");
        $count_stmt->bind_param("i", $event_id);
        $count_stmt->execute();
        $registration_count = (int) $count_stmt->get_result()->fetch_row()[0];
        $count_stmt->close();
    }
    
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Crear/Editar evento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container my-5">
        <div class="row wrap">
            <h1 class="mb-4"><?= isset($event_id) ? 'Editar Evento' : 'Crear Nuevo Evento' ?></h1>

            <?php if ($status === 'draft'): ?>
                <div class="alert alert-warning">
                    <strong>Borrador.</strong> Este evento no es visible en la web hasta que lo publiques.
                    Para guardarlo como borrador basta con el título en español; para publicarlo hacen falta
                    títulos y descripciones en ambos idiomas, fechas e imagen principal.
                </div>
            <?php endif; ?>

            <form id="event-form" action="<?= isset($event_id) ? 'update_event.php' : 'insert_event.php' ?>" method="POST" enctype="multipart/form-data">
                <?php if (isset($event_id)): ?>
                    <input type="hidden" name="id" value="<?= $event_id ?>">
                <?php endif; ?>

                    <div class="row justify-content-center">
                    <!-- ES -> EN translation (OpenRouter) -->
                    <div class="mb-3 col-12 d-flex align-items-center gap-3 flex-wrap">
                        <button type="button" id="translate-btn" class="btn btn-outline-primary">
                            Traducir al inglés
                        </button>
                        <small class="text-muted">Rellena título, tipo y descripción en español y genera la versión en inglés. Revísala antes de guardar.</small>
                        <div id="translate-status" class="small w-100" role="status"></div>
                    </div>

                    <!-- Spanish title -->
                    <div class="mb-3 col-6">
                        <label class="form-label">Título (Español)</label>
                        <input type="text" name="title_es" id="title_es" class="form-control" required value="<?= htmlspecialchars($title_es) ?>">
                    </div>

                    <!-- English title -->
                    <div class="mb-3 col-6">
                        <label class="form-label">Title (English)</label>
                        <input type="text" name="title_en" id="title_en" class="form-control" required value="<?= htmlspecialchars($title_en) ?>">
                    </div>

                    <!-- Spanish type -->
                    <div class="mb-3 col-6">
                        <label class="form-label">Tipo (Español)</label>
                        <input type="text" name="type_es" id="type_es" class="form-control" value="<?= htmlspecialchars($type_es) ?>">
                    </div>

                    <!-- English type -->
                    <div class="mb-3 col-6">
                        <label class="form-label">Type (English)</label>
                        <input type="text" name="type_en" id="type_en" class="form-control" value="<?= htmlspecialchars($type_en) ?>">
                    </div>

                    <!-- Speaker -->
                    <div class="mb-3 col-6">
                        <label class="form-label">Speaker</label>
                        <input type="text" name="speaker" class="form-control" value="<?= htmlspecialchars($speaker) ?>">
                    </div>

                    <!-- Company -->
                    <div class="mb-3 col-6">
                        <label class="form-label">Empresa</label>
                        <input type="text" name="company" class="form-control" placeholder="Spotify"
                            value="<?= htmlspecialchars($company) ?>">
                    </div>

                    <!-- Spanish description -->
                    <div class="mb-3 col-6">
                        <label class="form-label">Descripción (Español)</label>
                        <textarea required name="description_es" id="description_es" class="form-control" rows="4"><?= htmlspecialchars($description_es) ?></textarea>
                    </div>

                    <!-- English description -->
                    <div class="mb-3 col-6">
                        <label class="form-label">Description (English)</label>
                        <textarea required name="description_en" id="description_en" class="form-control" rows="4"><?= htmlspecialchars($description_en) ?></textarea>
                    </div>

                    <!-- Location -->
                    <div class="mb-3">
                        <label class="form-label">Ubicación</label>
                        <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($location) ?>">
                    </div>

                    <!-- Start date/time -->
                    <div class="mb-3 col-6">
                        <label class="form-label">Fecha y hora de inicio</label>
                        <input type="datetime-local" name="start_datetime" class="form-control" required value="<?= $start_datetime ?>">
                    </div>

                    <!-- End date/time -->
                    <div class="mb-3 col-6">
                        <label class="form-label">Fecha y hora de fin</label>
                        <input type="datetime-local" name="end_datetime" class="form-control" required value="<?= $end_datetime ?>">
                    </div>

                    <!-- Image upload -->
                    <div class="mb-3">
                        <label class="form-label">Imagen principal del evento</label>
                        <input type="file" name="image" class="form-control" accept="image/*" <?= empty($image_path) ? 'required' : '' ?>>
                    </div>

                    <!-- Multiple event photos -->
                    <div class="mb-3">
                        <label class="form-label">Fotos del evento</label>
                        <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
                    </div>
                        


                    <!-- Youtube URL -->
                    <div class="mb-3">
                        <label class="form-label">YT URL (empty if none)</label>
                        <input type="url" name="youtube_url" class="form-control" 
                        value="<?= htmlspecialchars($youtube_url ?? '') ?>">
                    </div>

                    <!-- Requires Registration -->
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="requires_registration" class="form-check-input" id="requires_registration" value="1" <?= !empty($requires_registration) && $requires_registration ? 'checked' : '' ?>>
                        <label class="form-check-label" for="requires_registration">Requiere inscripción</label>
                    </div>

                    <!-- Reminder Settings -->
                    <div class="mb-3 p-3 border rounded bg-light">
                        <h5 class="mb-3">Recordatorio por email</h5>
                        <div class="form-check mb-2">
                            <input type="checkbox" name="reminder_enabled" class="form-check-input" id="reminder_enabled" value="1" <?= !empty($reminder_enabled) && $reminder_enabled ? 'checked' : '' ?>>
                            <label class="form-check-label" for="reminder_enabled">Enviar recordatorio antes del evento</label>
                        </div>
                        <div class="mt-2">
                            <label class="form-label" for="reminder_days_before">Días antes del evento para enviar el recordatorio</label>
                            <select name="reminder_days_before" id="reminder_days_before" class="form-select" style="max-width: 200px;">
                                <?php for ($i = 1; $i <= 7; $i++): ?>
                                    <option value="<?= $i ?>" <?= $reminder_days_before == $i ? 'selected' : '' ?>><?= $i ?> día<?= $i > 1 ? 's' : '' ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="mt-3">
                            <label class="form-label" for="reminder_custom_text">Texto del correo</label>
                            <textarea name="reminder_custom_text" id="reminder_custom_text" class="form-control" rows="5"><?= htmlspecialchars($reminder_custom_text ?: DEFAULT_REMINDER_TEXT) ?></textarea>
                            <div class="form-text">
                                Párrafo que va después de "¡Hola {nombre}!". Marcadores: <code>{{event_type_label}}</code>, <code>{{event_name}}</code>, <code>{{event_date}}</code>.
                                <code>**texto**</code> = negrita. Las URLs se convierten en enlaces. Vacíalo para volver al texto por defecto.
                            </div>
                        </div>
                    </div>

                    <!-- Submit: drafts skip the browser validation, only title_es is checked server-side -->
                    <div class="col-12 d-flex gap-2 flex-wrap">
                        <?php if ($status === 'draft'): ?>
                            <button type="submit" name="action" value="draft" class="btn btn-outline-secondary" formnovalidate>
                                Guardar borrador
                            </button>
                            <button type="submit" name="action" value="publish" class="btn btn-primary">
                                Publicar
                            </button>
                        <?php else: ?>
                            <button type="submit" name="action" value="save" class="btn btn-primary">
                                Guardar cambios
                            </button>
                            <button type="submit" name="action" value="unpublish" class="btn btn-outline-danger" formnovalidate
                                onclick="return confirm(<?= htmlspecialchars(json_encode($registration_count > 0
                                    ? "Este evento tiene $registration_count inscritos. Dejará de ser visible en la web y no se enviarán recordatorios. ¿Despublicarlo?"
                                    : 'El evento dejará de ser visible en la web. ¿Despublicarlo?'), ENT_QUOTES) ?>)">
                                Despublicar
                            </button>
                        <?php endif; ?>
                    </div>
        </div>
        </form>


    </div>
    </div>

    <script>
        // Fills the EN fields from the ES ones via translate_event.php; nothing is saved until the form is submitted
        (function () {
            const btn = document.getElementById('translate-btn');
            const statusBox = document.getElementById('translate-status');
            const csrfToken = <?= json_encode(generate_csrf_token()) ?>;
            const pairs = [['title_es', 'title_en'], ['type_es', 'type_en'], ['description_es', 'description_en']];

            function setStatus(text, cls) {
                statusBox.textContent = text;
                statusBox.className = 'small w-100 ' + cls;
            }

            btn.addEventListener('click', async function () {
                const source = {};
                pairs.forEach(([es]) => source[es] = document.getElementById(es).value.trim());

                if (!source.title_es && !source.description_es) {
                    setStatus('Escribe al menos el título o la descripción en español.', 'text-danger');
                    return;
                }

                const overwritten = pairs.filter(([es, en]) => source[es] && document.getElementById(en).value.trim());
                if (overwritten.length && !confirm('Algunos campos en inglés ya tienen texto y se sobrescribirán. ¿Continuar?')) {
                    return;
                }

                btn.disabled = true;
                setStatus('Traduciendo… puede tardar unos segundos.', 'text-muted');

                try {
                    const response = await fetch('/events/translate_event.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
                        body: JSON.stringify(source)
                    });
                    const data = await response.json();
                    if (!response.ok) {
                        throw new Error(data.error || 'Error al traducir.');
                    }

                    pairs.forEach(([es, en]) => {
                        if (source[es] && data[en]) {
                            document.getElementById(en).value = data[en];
                        }
                    });
                    setStatus('Traducción generada con ' + data.model + '. Revísala antes de guardar.', 'text-success');
                } catch (err) {
                    setStatus(err.message || 'Error al traducir.', 'text-danger');
                } finally {
                    btn.disabled = false;
                }
            });
        })();
    </script>
</body>

</html>
