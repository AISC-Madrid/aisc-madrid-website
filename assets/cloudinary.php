<?php
/**
 * Cloudinary helper for AISC Madrid.
 *
 * Provides:
 *   cloudinary_config()                     -> array with cloud credentials/folder
 *   cloudinary_upload($localPath, $publicId, $opts = [])  -> ['url' => '...', 'public_id' => '...', 'version' => N] | ['error' => '...']
 *   cdn($localPath)                          -> Cloudinary delivery URL for static assets that live under images/
 *   cdn_from_image_path($imagePath)          -> Same as cdn() but tolerates already-absolute Cloudinary URLs (mixed legacy/new rows)
 *
 * No external dependencies — uses curl + sha1 (Cloudinary signed upload spec).
 */

function cloudinary_config(): array
{
    static $cfg = null;
    if ($cfg !== null) return $cfg;

    $configPath = __DIR__ . '/../config.php';
    if (!file_exists($configPath)) {
        throw new RuntimeException('config.php not found');
    }
    $config = include $configPath;

    foreach (['cloudinary_cloud_name', 'cloudinary_api_key', 'cloudinary_api_secret'] as $k) {
        if (empty($config[$k])) {
            throw new RuntimeException("Missing $k in config.php");
        }
    }

    $cfg = [
        'cloud_name' => $config['cloudinary_cloud_name'],
        'api_key'    => $config['cloudinary_api_key'],
        'api_secret' => $config['cloudinary_api_secret'],
        'folder'     => $config['cloudinary_folder'] ?? 'aisc_madrid',
    ];
    return $cfg;
}

/**
 * Upload a local file to Cloudinary using a signed POST request.
 *
 * @param string $localPath  Absolute path of file to upload.
 * @param string $publicId   Path inside the Cloudinary root folder (no extension, e.g. "events/event12/main").
 * @param array  $opts       Optional: ['resource_type' => 'image'|'raw'|'video', 'overwrite' => bool, 'invalidate' => bool]
 * @return array             ['url' => secure_url, 'public_id' => full public_id, 'version' => N] or ['error' => string]
 */
function cloudinary_upload(string $localPath, string $publicId, array $opts = []): array
{
    if (!file_exists($localPath)) {
        return ['error' => "File not found: $localPath"];
    }

    $cfg = cloudinary_config();
    $resourceType = $opts['resource_type'] ?? 'image';
    $overwrite = $opts['overwrite'] ?? true;
    $invalidate = $opts['invalidate'] ?? true;

    // Normalise public_id: trim slashes, no extension expected
    $publicId = trim($publicId, '/');
    $folder = trim($cfg['folder'], '/');
    $fullPublicId = $folder === '' ? $publicId : "$folder/$publicId";

    $timestamp = time();

    // Params to sign (alphabetical, only the ones included in upload). Order matters for SHA-1.
    $signParams = [
        'invalidate' => $invalidate ? 'true' : 'false',
        'overwrite'  => $overwrite ? 'true' : 'false',
        'public_id'  => $fullPublicId,
        'timestamp'  => $timestamp,
    ];

    ksort($signParams);
    $toSign = [];
    foreach ($signParams as $k => $v) {
        $toSign[] = $k . '=' . $v;
    }
    $signature = sha1(implode('&', $toSign) . $cfg['api_secret']);

    $postFields = [
        'api_key'    => $cfg['api_key'],
        'file'       => new CURLFile($localPath),
        'invalidate' => $signParams['invalidate'],
        'overwrite'  => $signParams['overwrite'],
        'public_id'  => $fullPublicId,
        'signature'  => $signature,
        'timestamp'  => $timestamp,
    ];

    $url = "https://api.cloudinary.com/v1_1/{$cfg['cloud_name']}/{$resourceType}/upload";

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $postFields,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 120,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        return ['error' => "cURL error: $curlErr"];
    }

    $data = json_decode($response, true);
    if ($httpCode >= 400 || !is_array($data)) {
        $msg = is_array($data) && isset($data['error']['message']) ? $data['error']['message'] : "HTTP $httpCode";
        return ['error' => "Cloudinary upload failed: $msg"];
    }

    return [
        'url'       => $data['secure_url'] ?? $data['url'] ?? '',
        'public_id' => $data['public_id'] ?? $fullPublicId,
        'version'   => $data['version'] ?? null,
        'format'    => $data['format'] ?? null,
    ];
}

/**
 * Build a Cloudinary delivery URL for a static asset that lives under the local images/ folder.
 *
 * Example: cdn('images/logos/PNG/AISC Logo Color.png')
 *       -> https://res.cloudinary.com/dpchpoort/image/upload/aisc_madrid/logos/PNG/AISC%20Logo%20Color.png
 *
 * For files inside images/, the leading "images/" is stripped because the Cloudinary
 * folder "aisc_madrid" mirrors the contents of that directory.
 */
function cdn(string $localPath): string
{
    $cfg = cloudinary_config();
    $resourceType = 'image';

    // Treat .ico / .svg fallbacks: Cloudinary handles SVG as image, ICO as raw.
    $ext = strtolower(pathinfo($localPath, PATHINFO_EXTENSION));
    if ($ext === 'ico') {
        $resourceType = 'raw';
    }

    $path = ltrim($localPath, '/');
    if (strpos($path, 'images/') === 0) {
        $path = substr($path, strlen('images/'));
    }

    $folder = trim($cfg['folder'], '/');
    $fullPath = $folder === '' ? $path : "$folder/$path";

    // URL-encode each segment so spaces and special chars work.
    $encoded = implode('/', array_map('rawurlencode', explode('/', $fullPath)));

    return "https://res.cloudinary.com/{$cfg['cloud_name']}/{$resourceType}/upload/{$encoded}";
}

/**
 * Resolve a value stored in the database (image_path / gallery item).
 *
 * - If it already looks like an absolute http(s) URL, return as-is.
 * - Otherwise treat it as a legacy local path and map it through cdn().
 *
 * This keeps templates working during/after the migration without further changes.
 */
function cdn_from_image_path(?string $imagePath): string
{
    if ($imagePath === null || $imagePath === '') return '';
    if (preg_match('#^https?://#i', $imagePath)) return $imagePath;
    return cdn($imagePath);
}
