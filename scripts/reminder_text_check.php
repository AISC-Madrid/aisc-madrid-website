<?php
// Comprueba cómo se construye el párrafo del recordatorio: php scripts/reminder_text_check.php
require __DIR__ . '/../mails/reminder_events/reminder_text.php';

$vars = ['event_type_label' => 'taller', 'event_name' => 'Git & GitHub', 'event_date' => '05/11/2026 18:00 - 05/11/2026 20:00'];

// Sin texto personalizado sale el mismo párrafo que tenía la plantilla
assert(build_reminder_html(null, $vars) === 'Te recordamos que estás registrado/a en el taller <strong>Git &amp; GitHub</strong>, que tendrá lugar <strong>este próximo 05/11/2026 18:00 - 05/11/2026 20:00</strong>.');

// Marcadores con el dato actual, negrita, enlaces (sin el punto final), saltos de línea y HTML escapado
$html = build_reminder_html(DEFAULT_REMINDER_TEXT . "\n**Instala Git** antes: https://git-scm.com/downloads. <b>x</b>", $vars);
assert(str_contains($html, '<strong>este próximo 05/11/2026 18:00 - 05/11/2026 20:00</strong>.<br />'));
assert(str_contains($html, '<strong>Instala Git</strong> antes: <a href="https://git-scm.com/downloads" target="_blank">https://git-scm.com/downloads</a>. &lt;b&gt;x&lt;/b&gt;'));

assert(build_reminder_plain(null, $vars) === 'Te recordamos que estás registrado/a en el taller Git & GitHub, que tendrá lugar este próximo 05/11/2026 18:00 - 05/11/2026 20:00.');

// Vacío o igual al de por defecto se guarda como NULL
assert(reminder_text_to_store("  ") === null);
assert(reminder_text_to_store(DEFAULT_REMINDER_TEXT . "\r\n") === null);
assert(reminder_text_to_store("Hola\r\nGit") === "Hola\nGit");

echo "OK\n";
