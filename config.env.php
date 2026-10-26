<?php
/**
 * Env-driven configuration used in Docker deployments.
 *
 * config.php is not tracked in git, so the Docker build copies this file to
 * config.php when no config.php exists. Set the values as environment
 * variables in the hosting panel (DB_HOST, DB_NAME, S3_SECRET_KEY, ...).
 */

$env = static function (string $key, ?string $default = null): ?string {
    $value = getenv($key);

    return ($value === false || $value === '') ? $default : $value;
};

return [
    // Database
    'db_host' => $env('DB_HOST', 'localhost'),
    'db_port' => (int) $env('DB_PORT', '3306'),
    'db_name' => $env('DB_NAME'),
    'db_user' => $env('DB_USER'),
    'db_pass' => $env('DB_PASS'),

    // SMTP (PHPMailer)
    'smtp_user' => $env('SMTP_USER'),
    'smtp_pass' => $env('SMTP_PASS'),

    // Public site URL (absolute links in emails)
    'base_url' => $env('BASE_URL', 'https://aiscmadrid.com/'),

    // Cloudinary
    'cloudinary_cloud_name' => $env('CLOUDINARY_CLOUD_NAME'),
    'cloudinary_api_key' => $env('CLOUDINARY_API_KEY'),
    'cloudinary_api_secret' => $env('CLOUDINARY_API_SECRET'),
    'cloudinary_folder' => $env('CLOUDINARY_FOLDER', 'aisc_madrid'),

    // Media storage (S3 / MinIO)
    'media_base_url' => $env('MEDIA_BASE_URL', 'https://s3.aiscmadrid.com/aisc-public'),
    's3_endpoint' => $env('S3_ENDPOINT', 'https://s3.aiscmadrid.com'),
    's3_region' => $env('S3_REGION', 'us-east-1'),
    's3_bucket' => $env('S3_BUCKET', 'aisc-public'),
    's3_access_key' => $env('S3_ACCESS_KEY'),
    's3_secret_key' => $env('S3_SECRET_KEY'),

    // Show PHP errors on public pages (keep off in production)
    'dev_mode' => $env('DEV_MODE') === '1',
];
