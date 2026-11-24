<?php
// Perfil de contacto de un guest (email, teléfono, LinkedIn): lectura y validación del formulario,
// compartida entre crear y editar. Todos los campos son opcionales; vacío = NULL.

const GUEST_PHONE_MAX_LENGTH = 30;
const GUEST_EMAIL_MAX_LENGTH = 255;
const GUEST_LINKEDIN_MAX_LENGTH = 255;

/**
 * Lee los datos de contacto de un formulario.
 * Devuelve ['email' => ?string, 'phone' => ?string, 'linkedin_url' => ?string, 'error' => ?string].
 */
function guest_contact_from_input(array $input): array
{
    $contact = [];
    foreach (['email', 'phone', 'linkedin_url'] as $field) {
        $value = trim((string) ($input[$field] ?? ''));
        $contact[$field] = $value === '' ? null : $value;
    }

    $contact['error'] = null;
    // FILTER_VALIDATE_EMAIL ya rechaza más de 254 caracteres, así que cabe en la columna
    if ($contact['email'] !== null && filter_var($contact['email'], FILTER_VALIDATE_EMAIL) === false) {
        $contact['error'] = "El email no tiene un formato válido.";
    } elseif ($contact['phone'] !== null && mb_strlen($contact['phone']) > GUEST_PHONE_MAX_LENGTH) {
        $contact['error'] = "El teléfono no puede superar los " . GUEST_PHONE_MAX_LENGTH . " caracteres.";
    } elseif ($contact['linkedin_url'] !== null && !guest_is_linkedin_url($contact['linkedin_url'])) {
        $contact['error'] = "El LinkedIn debe ser una URL de linkedin.com.";
    }

    return $contact;
}

/** URL http(s) cuyo host es linkedin.com o un subdominio suyo. */
function guest_is_linkedin_url(string $url): bool
{
    if (strlen($url) > GUEST_LINKEDIN_MAX_LENGTH || filter_var($url, FILTER_VALIDATE_URL) === false) {
        return false;
    }
    $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
    $host = strtolower((string) parse_url($url, PHP_URL_HOST));
    return in_array($scheme, ['http', 'https'], true)
        && ($host === 'linkedin.com' || str_ends_with($host, '.linkedin.com'));
}
