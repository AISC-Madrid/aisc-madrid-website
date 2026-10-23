<?php
/**
 * Email templates come from two places:
 *   - git:  ../mails/<folder>/<name>.html (value is that relative path, as before)
 *   - S3:   mails saved from the Mail-Builder, key "mails/difusion/<name>.html"
 *           (value is "s3:<key>", so it never collides with a local path)
 * Paths are relative to dashboard/, where these helpers are used.
 */

require_once __DIR__ . '/s3.php';

const MAIL_S3_PREFIX = 'mails/difusion/';
const MAIL_S3_VALUE_PREFIX = 's3:';

/**
 * Is $key a valid key for a Mail-Builder mail in S3?
 */
function is_saved_mail_key(string $key): bool
{
    return (bool) preg_match('#^' . preg_quote(MAIL_S3_PREFIX, '#') . '[A-Za-z0-9_-]{1,100}\.html$#', $key);
}

/**
 * Every template available to send, git ones first.
 *
 * @return array  ['templates' => [['value', 'label', 'preview'], ...], 'error' => ?string]
 */
function list_mail_templates(): array
{
    $templates = [];
    foreach (glob('../mails/*/*.html') as $file) {
        $templates[] = ['value' => $file, 'label' => basename($file), 'preview' => $file];
    }

    $error = null;
    try {
        $result = s3_list_keys(MAIL_S3_PREFIX);
    } catch (Throwable $e) {
        $result = ['error' => $e->getMessage()];
    }

    if (isset($result['error'])) {
        $error = "No se pudieron cargar los mails guardados en S3: {$result['error']}";
    } else {
        $keys = array_filter($result['keys'], 'is_saved_mail_key');
        sort($keys);
        foreach ($keys as $key) {
            $templates[] = [
                'value'   => MAIL_S3_VALUE_PREFIX . $key,
                'label'   => basename($key) . ' (Mail-Builder)',
                'preview' => 'view_mail.php?key=' . rawurlencode($key),
            ];
        }
    }

    return ['templates' => $templates, 'error' => $error];
}

/**
 * HTML of a template value returned by list_mail_templates(), or null if it can't be read.
 */
function load_mail_template(string $value): ?string
{
    if (strpos($value, MAIL_S3_VALUE_PREFIX) === 0) {
        $key = substr($value, strlen(MAIL_S3_VALUE_PREFIX));
        if (!is_saved_mail_key($key)) {
            return null;
        }
        $result = s3_get_object($key);
        return $result['body'] ?? null;
    }

    $html = @file_get_contents($value);
    return $html === false ? null : $html;
}
