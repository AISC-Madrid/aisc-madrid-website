<?php
// Párrafo principal del correo de recordatorio (después de "¡Hola {nombre}!").
// Se edita por evento desde create_event.php y se construye al enviar en cron_event_reminder.php.
// Marcadores: {{event_type_label}}, {{event_name}}, {{event_date}}. **texto** = negrita. Las URLs se convierten en enlaces.

const DEFAULT_REMINDER_TEXT = "Te recordamos que estás registrado/a en el {{event_type_label}} **{{event_name}}**, que tendrá lugar **este próximo {{event_date}}**.";

// Texto a guardar en events.reminder_custom_text: NULL si está vacío o es el de por defecto
function reminder_text_to_store($text)
{
    $text = trim(str_replace("\r\n", "\n", (string) $text));
    return ($text === '' || $text === DEFAULT_REMINDER_TEXT) ? null : $text;
}

function build_reminder_html($text, array $vars)
{
    $html = htmlspecialchars($text ?: DEFAULT_REMINDER_TEXT);
    foreach ($vars as $key => $value) {
        $html = str_replace('{{' . $key . '}}', htmlspecialchars($value), $html);
    }
    $html = preg_replace('~\*\*(.+?)\*\*~s', '<strong>$1</strong>', $html);
    $html = preg_replace('~https?://[^\s<]*[^\s<.,;:!?)]~', '<a href="$0" target="_blank">$0</a>', $html);
    return nl2br($html);
}

function build_reminder_plain($text, array $vars)
{
    $plain = $text ?: DEFAULT_REMINDER_TEXT;
    foreach ($vars as $key => $value) {
        $plain = str_replace('{{' . $key . '}}', $value, $plain);
    }
    return str_replace('**', '', $plain);
}
