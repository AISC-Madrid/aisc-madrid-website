<?php
// Envuelve el contenido de cada correo en la plantilla común (layout.html), igual de
// sencilla en HTML y en texto, siguiendo el patrón {{token}} + str_replace ya usado en
// mails/reminder_events/event_reminder_template.html.

function ep_render_email(string $heading, string $bodyHtml, string $bodyText, ?string $ctaUrl = null, ?string $ctaLabel = null): array
{
    $layout = file_get_contents(__DIR__ . '/mail_templates/layout.html');

    $ctaRow = '';
    $textCta = '';
    if ($ctaUrl !== null) {
        $safeUrl = htmlspecialchars($ctaUrl, ENT_QUOTES);
        $safeLabel = htmlspecialchars($ctaLabel ?? 'Ver en el dashboard', ENT_QUOTES);
        $ctaRow = '<tr><td align="center" style="padding:0 20px 24px;">'
            . '<a href="' . $safeUrl . '" style="background-color:#20CCF1; color:#ffffff; text-decoration:none; '
            . 'padding:12px 24px; border-radius:5px; display:inline-block; font-size:14px; font-weight:bold;" target="_blank">'
            . $safeLabel . '</a></td></tr>';
        $textCta = "\n\n" . ($ctaLabel ?? 'Ver en el dashboard') . ': ' . $ctaUrl;
    }

    $html = str_replace(
        ['{{heading}}', '{{body_html}}', '{{cta_row}}'],
        [htmlspecialchars($heading, ENT_QUOTES), $bodyHtml, $ctaRow],
        $layout
    );

    $text = $heading . "\n\n" . $bodyText . $textCta;

    return ['html' => $html, 'text' => $text];
}

function ep_html_escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
