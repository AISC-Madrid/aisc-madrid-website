<?php
// event_form.php — helpers shared by insert_event.php and update_event.php.
// A draft only needs title_es; publishing requires everything the public pages rely on.

/**
 * Convert a datetime-local value (Europe/Madrid) to a UTC string for storage, or null if empty.
 */
function event_form_datetime_utc(?string $local): ?string
{
    if (empty($local)) {
        return null;
    }
    $dt = new DateTime($local, new DateTimeZone('Europe/Madrid'));
    return $dt->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
}

/**
 * Fields missing to publish the event.
 *
 * @return string[]  Human readable names of the missing fields (empty if it can be published)
 */
function event_publish_errors(array $post, bool $hasImage): array
{
    $required = [
        'title_es'       => 'Título (Español)',
        'title_en'       => 'Title (English)',
        'description_es' => 'Descripción (Español)',
        'description_en' => 'Description (English)',
        'start_datetime' => 'Fecha y hora de inicio',
        'end_datetime'   => 'Fecha y hora de fin',
    ];

    $missing = [];
    foreach ($required as $field => $label) {
        if (trim($post[$field] ?? '') === '') {
            $missing[] = $label;
        }
    }
    if (!$hasImage) {
        $missing[] = 'Imagen principal del evento';
    }
    return $missing;
}

/**
 * Stop with the list of fields missing to publish.
 */
function event_die_missing(array $missing): void
{
    $items = implode('', array_map(fn($m) => '<li>' . htmlspecialchars($m) . '</li>', $missing));
    die("<p style='color:red;'>❌ No se puede publicar, faltan estos campos:</p><ul>$items</ul>"
        . "<p>Vuelve atrás y complétalos, o guárdalo como borrador.</p>");
}
