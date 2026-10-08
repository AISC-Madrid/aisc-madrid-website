<?php
// contacts.php — categories for the contacts table (dashboard/contacts/ and send_test_email.php).
// To add a category, add a key => label here; the DB column is a plain VARCHAR.

const CONTACT_CATEGORIES = [
    'degree_director'  => 'Directores de titulación',
    'teacher'          => 'Profesores',
    'university_staff' => 'Personal UC3M',
    'event_contact'    => 'Contactos de eventos',
    'other'            => 'Otros',
];

function contact_category_label(string $category): string
{
    return CONTACT_CATEGORIES[$category] ?? $category;
}

/**
 * Name used in "Buenos días ___": greeting_name, or the first word of full_name.
 */
function contact_greeting_name(array $contact): string
{
    $greeting = trim((string) ($contact['greeting_name'] ?? ''));
    return $greeting !== '' ? $greeting : explode(' ', trim($contact['full_name']))[0];
}

/**
 * Spanish long date without year, e.g. "lunes 17 de noviembre".
 */
function spanish_long_date(DateTime $date): string
{
    $days = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
    $months = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    return $days[(int) $date->format('w')] . ' ' . $date->format('j') . ' de ' . $months[(int) $date->format('n') - 1];
}
