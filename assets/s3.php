<?php
/**
 * Minimal S3 client (AWS Signature V4) for the MinIO media bucket.
 *
 * Provides:
 *   s3_config()                                    -> array with endpoint/region/bucket/credentials
 *   s3_put_object($localPath, $key, $contentType)  -> ['key' => '...'] | ['error' => '...']
 *   s3_put_contents($body, $key, $contentType)     -> ['key' => '...'] | ['error' => '...']
 *   s3_get_object($key)                            -> ['body' => '...'] | ['error' => '...', 'missing' => bool]
 *   s3_list_keys($prefix)                          -> ['keys' => [...]] | ['error' => '...']
 *   s3_delete_object($key)                         -> ['key' => '...'] | ['error' => '...']
 *
 * No external dependencies — uses curl + hash_hmac (path-style requests, as MinIO expects).
 */

function s3_config(): array
{
    static $cfg = null;
    if ($cfg !== null) return $cfg;

    $configPath = __DIR__ . '/../config.php';
    if (!file_exists($configPath)) {
        throw new RuntimeException('config.php not found');
    }
    $config = include $configPath;

    foreach (['s3_endpoint', 's3_bucket', 's3_access_key', 's3_secret_key'] as $k) {
        if (empty($config[$k])) {
            throw new RuntimeException("Missing $k in config.php");
        }
    }

    $cfg = [
        'endpoint'   => rtrim($config['s3_endpoint'], '/'),
        'region'     => $config['s3_region'] ?: 'us-east-1',
        'bucket'     => $config['s3_bucket'],
        'access_key' => $config['s3_access_key'],
        'secret_key' => $config['s3_secret_key'],
    ];
    return $cfg;
}

/**
 * Send a signed request for an object in the configured bucket.
 *
 * @return array ['status' => HTTP code, 'body' => string] or ['error' => string]
 */
function s3_request(string $method, string $key, string $body = '', array $headers = [], array $query = []): array
{
    $cfg = s3_config();

    $uri = '/' . rawurlencode($cfg['bucket']) . '/'
         . implode('/', array_map('rawurlencode', explode('/', ltrim($key, '/'))));

    // Query string must be sorted and RFC 3986 encoded for the signature
    ksort($query);
    $canonicalQuery = implode('&', array_map(
        fn($k, $v) => rawurlencode($k) . '=' . rawurlencode((string) $v),
        array_keys($query),
        $query
    ));

    // Host must match what curl sends, including a non-default port.
    $host = parse_url($cfg['endpoint'], PHP_URL_HOST);
    $port = parse_url($cfg['endpoint'], PHP_URL_PORT);
    if ($port) $host .= ":$port";

    $amzDate = gmdate('Ymd\THis\Z');
    $date = substr($amzDate, 0, 8);
    $payloadHash = hash('sha256', $body);

    $headers = array_change_key_case($headers, CASE_LOWER) + [
        'host'                 => $host,
        'x-amz-content-sha256' => $payloadHash,
        'x-amz-date'           => $amzDate,
    ];
    ksort($headers);

    $canonicalHeaders = '';
    foreach ($headers as $k => $v) {
        $canonicalHeaders .= $k . ':' . trim($v) . "\n";
    }
    $signedHeaders = implode(';', array_keys($headers));

    $canonicalRequest = "$method\n$uri\n$canonicalQuery\n$canonicalHeaders\n$signedHeaders\n$payloadHash";
    $scope = "$date/{$cfg['region']}/s3/aws4_request";
    $stringToSign = "AWS4-HMAC-SHA256\n$amzDate\n$scope\n" . hash('sha256', $canonicalRequest);

    $kDate    = hash_hmac('sha256', $date, 'AWS4' . $cfg['secret_key'], true);
    $kRegion  = hash_hmac('sha256', $cfg['region'], $kDate, true);
    $kService = hash_hmac('sha256', 's3', $kRegion, true);
    $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);
    $signature = hash_hmac('sha256', $stringToSign, $kSigning);

    $curlHeaders = ['Expect:']; // no 100-continue round trip
    foreach ($headers as $k => $v) {
        if ($k !== 'host') $curlHeaders[] = "$k: $v"; // curl sets Host from the URL
    }
    $curlHeaders[] = "authorization: AWS4-HMAC-SHA256 Credential={$cfg['access_key']}/$scope, "
                   . "SignedHeaders=$signedHeaders, Signature=$signature";

    $ch = curl_init($cfg['endpoint'] . $uri . ($canonicalQuery !== '' ? "?$canonicalQuery" : ''));
    $opts = [
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_HTTPHEADER     => $curlHeaders,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 120,
    ];
    if ($body !== '') $opts[CURLOPT_POSTFIELDS] = $body;
    curl_setopt_array($ch, $opts);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        return ['error' => "cURL error: $curlErr"];
    }
    return ['status' => $httpCode, 'body' => $response];
}

/**
 * Upload a local file to the media bucket.
 *
 * @param string $key  Object key, e.g. "events-workshops/12/img_65f1c2.webp"
 * @return array       ['key' => $key] or ['error' => string]
 */
function s3_put_object(string $localPath, string $key, string $contentType = 'application/octet-stream'): array
{
    if (!file_exists($localPath)) {
        return ['error' => "File not found: $localPath"];
    }

    // Keys are unique per upload, so objects never change and can be cached forever.
    return s3_put_contents(file_get_contents($localPath), $key, $contentType, 'public, max-age=31536000, immutable');
}

/**
 * Upload a string to the media bucket (e.g. an email template that can be overwritten later).
 *
 * @return array  ['key' => $key] or ['error' => string]
 */
function s3_put_contents(string $body, string $key, string $contentType = 'application/octet-stream', string $cacheControl = 'no-cache'): array
{
    $r = s3_request('PUT', $key, $body, [
        'content-type'  => $contentType,
        'cache-control' => $cacheControl,
    ]);
    if (isset($r['error'])) return $r;

    if ($r['status'] !== 200) {
        $msg = preg_match('#<Message>(.*?)</Message>#s', $r['body'], $m) ? $m[1] : "HTTP {$r['status']}";
        return ['error' => "S3 upload failed: $msg"];
    }
    return ['key' => $key];
}

/**
 * Download an object from the media bucket.
 *
 * @return array  ['body' => string] or ['error' => string, 'missing' => bool]
 */
function s3_get_object(string $key): array
{
    $r = s3_request('GET', $key);
    if (isset($r['error'])) return $r + ['missing' => false];

    if ($r['status'] === 404) {
        return ['error' => "S3 object not found: $key", 'missing' => true];
    }
    if ($r['status'] !== 200) {
        $msg = preg_match('#<Message>(.*?)</Message>#s', $r['body'], $m) ? $m[1] : "HTTP {$r['status']}";
        return ['error' => "S3 download failed: $msg", 'missing' => false];
    }
    return ['body' => $r['body']];
}

/**
 * List every object key under a prefix (follows pagination).
 *
 * @return array  ['keys' => string[]] or ['error' => string]
 */
function s3_list_keys(string $prefix): array
{
    $keys = [];
    $token = null;

    do {
        $query = ['list-type' => '2', 'prefix' => $prefix];
        if ($token !== null) $query['continuation-token'] = $token;

        $r = s3_request('GET', '', '', [], $query);
        if (isset($r['error'])) return $r;

        if ($r['status'] !== 200) {
            $msg = preg_match('#<Message>(.*?)</Message>#s', $r['body'], $m) ? $m[1] : "HTTP {$r['status']}";
            return ['error' => "S3 list failed: $msg"];
        }

        preg_match_all('#<Key>(.*?)</Key>#s', $r['body'], $m);
        foreach ($m[1] as $k) {
            $keys[] = html_entity_decode($k, ENT_QUOTES | ENT_XML1);
        }

        $truncated = preg_match('#<IsTruncated>true</IsTruncated>#', $r['body']);
        $token = $truncated && preg_match('#<NextContinuationToken>(.*?)</NextContinuationToken>#s', $r['body'], $t)
            ? html_entity_decode($t[1], ENT_QUOTES | ENT_XML1)
            : null;
    } while ($token !== null);

    return ['keys' => $keys];
}

/**
 * Delete an object from the media bucket. Deleting a missing key is not an error.
 *
 * @return array  ['key' => $key] or ['error' => string]
 */
function s3_delete_object(string $key): array
{
    $r = s3_request('DELETE', $key);
    if (isset($r['error'])) return $r;

    if ($r['status'] !== 204 && $r['status'] !== 200) {
        $msg = preg_match('#<Message>(.*?)</Message>#s', $r['body'], $m) ? $m[1] : "HTTP {$r['status']}";
        return ['error' => "S3 delete failed: $msg"];
    }
    return ['key' => $key];
}
