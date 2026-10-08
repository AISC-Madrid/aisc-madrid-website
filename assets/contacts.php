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
