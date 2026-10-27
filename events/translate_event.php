<?php
// translate_event.php — translates the ES fields of the event form to EN with OpenRouter free models.
// Called by the "Traducir al inglés" button of create_event.php; returns JSON and saves nothing.

include('../assets/csrf.php');

header('Content-Type: application/json; charset=utf-8');

const OPENROUTER_URL = 'https://openrouter.ai/api/v1';

// Free models in order of preference. Free models come and go, so the ones no longer listed by
// OpenRouter are skipped; openrouter/free (router over whatever free models exist) is the last resort.
// Only free models, so it never costs money.
const DEFAULT_TRANSLATION_MODELS = [
    'google/gemma-4-31b-it:free',
    'nvidia/nemotron-3-super-120b-a12b:free',
    'google/gemma-4-26b-a4b-it:free',
    'thinkingmachines/inkling:free',
    'nvidia/nemotron-3-ultra-550b-a55b:free',
    'nvidia/nemotron-3.5-lightning:free',
    'thinkingmachines/inkling-small:free',
];
const FREE_ROUTER_MODEL = 'openrouter/free';

function translate_fail(int $code, string $message): void
{
    http_response_code($code);
    echo json_encode(['error' => $message]);
    exit();
}

/**
 * Keep only the models OpenRouter still lists, so rotated-out free models don't waste attempts.
 * If the catalog can't be fetched, the list is returned unchanged.
 */
function translate_available_models(array $models): array
{
    $ch = curl_init(OPENROUTER_URL . '/models');
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 5]);
    $catalog = json_decode((string) curl_exec($ch), true);
    curl_close($ch);

    if (empty($catalog['data'])) {
        return $models;
    }
    $listed = array_column($catalog['data'], 'id');
    return array_values(array_filter($models, fn($m) => in_array($m, $listed, true)));
}

/**
 * One chat completion with up to 3 models (OpenRouter's limit for the models fallback array).
 *
 * @return array  [http code, decoded response or null, raw body]
 */
function translate_request(string $apiKey, array $models, array $messages, string $referer): array
{
    $ch = curl_init(OPENROUTER_URL . '/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 45,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
            'HTTP-Referer: ' . $referer,
            'X-Title: AISC Madrid',
        ],
        CURLOPT_POSTFIELDS     => json_encode([
            'models'      => $models,
            'messages'    => $messages,
            'temperature' => 0.2,
        ]),
    ]);
    $raw = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($raw === false) {
        error_log('OpenRouter request failed: ' . curl_error($ch));
    }
    curl_close($ch);

    return [$httpCode, $raw === false ? null : json_decode($raw, true), (string) $raw];
}

/**
 * Extract the translation JSON from a model answer. Some models wrap it in markdown fences
 * or add text around it, so keep the outermost {...}.
 */
function translate_parse(string $content): ?array
{
    $start = strpos($content, '{');
    $end = strrpos($content, '}');
    if ($start === false || $end === false) {
        return null;
    }
    $translation = json_decode(substr($content, $start, $end - $start + 1), true);
    return is_array($translation) ? $translation : null;
}

$allowed_roles = ['admin', 'events'];
if (!isset($_SESSION['activated']) || !in_array($_SESSION['role'], $allowed_roles)) {
    translate_fail(403, 'Acceso no autorizado.');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    translate_fail(405, 'Método no permitido.');
}
if (!validate_csrf_token($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '')) {
    translate_fail(403, 'Token CSRF inválido. Recarga la página.');
}

$config = include(__DIR__ . '/../config.php');
$apiKey = $config['openrouter_api_key'] ?? '';
if ($apiKey === '') {
    translate_fail(503, 'La traducción automática no está configurada (falta OPENROUTER_API_KEY).');
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$source = [
    'title_es'       => trim((string) ($input['title_es'] ?? '')),
    'type_es'        => trim((string) ($input['type_es'] ?? '')),
    'description_es' => trim((string) ($input['description_es'] ?? '')),
];
if ($source['title_es'] === '' && $source['description_es'] === '') {
    translate_fail(400, 'Escribe al menos el título o la descripción en español.');
}

// OPENROUTER_MODELS (comma-separated) replaces the default preference list
$models = array_values(array_filter(array_map('trim', explode(',', $config['openrouter_models'] ?? ''))));
$models = translate_available_models($models ?: DEFAULT_TRANSLATION_MODELS);
$models[] = FREE_ROUTER_MODEL;

$systemPrompt = <<<PROMPT
You translate event listings of AISC Madrid, a university student association about artificial intelligence at UC3M (Madrid), from Spanish to English for its website.

You receive a JSON object with the Spanish fields title_es, type_es and description_es. Answer ONLY with a JSON object with the keys title_en, type_en and description_en, without markdown fences or any other text.

Rules:
- Translate faithfully and naturally, in the same tone. Do not add, remove or summarize information.
- Keep names of people, companies, products, places, URLs, emails, hashtags and emojis exactly as they are.
- Keep the line breaks and the formatting of the description.
- If a Spanish field is empty, return an empty string for its English key.
- type_en must be one of the types used on the website whenever the Spanish type matches one of them:
  "Evento" -> "Event"; "Taller" or "Workshop" -> "Workshop".
  If the Spanish type is something else, translate it naturally in one or two words (for example "Charla" -> "Talk", "Hackathon" -> "Hackathon").
PROMPT;

$messages = [
    ['role' => 'system', 'content' => $systemPrompt],
    ['role' => 'user', 'content' => json_encode($source, JSON_UNESCAPED_UNICODE)],
];

// Try the models in groups of 3 until one answers with a valid translation
$lastError = 'sin respuesta';
foreach (array_chunk($models, 3) as $group) {
    [$httpCode, $response, $raw] = translate_request($apiKey, $group, $messages, $config['base_url'] ?? 'https://aiscmadrid.com/');

    $errorMessage = $response['error']['message'] ?? '';
    if ($httpCode === 401) {
        error_log("OpenRouter auth error: $raw");
        translate_fail(502, 'La clave de OpenRouter no es válida (OPENROUTER_API_KEY).');
    }
    // The daily quota of free requests is per account: other models won't help
    if ($httpCode === 429 && stripos($errorMessage, 'per-day') !== false) {
        translate_fail(429, 'Se ha alcanzado el límite diario de traducciones gratuitas. Rellena el inglés a mano o inténtalo mañana.');
    }
    if ($httpCode !== 200 || $errorMessage !== '') {
        error_log('OpenRouter error with ' . implode(',', $group) . " ($httpCode): $raw");
        $lastError = $errorMessage ?: "HTTP $httpCode";
        continue;
    }

    $translation = translate_parse($response['choices'][0]['message']['content'] ?? '');
    if ($translation === null) {
        error_log('OpenRouter returned non-JSON content with ' . ($response['model'] ?? '?') . ": $raw");
        $lastError = 'respuesta sin el formato esperado';
        continue;
    }

    echo json_encode([
        'title_en'       => trim((string) ($translation['title_en'] ?? '')),
        'type_en'        => trim((string) ($translation['type_en'] ?? '')),
        'description_en' => trim((string) ($translation['description_en'] ?? '')),
        'model'          => $response['model'] ?? 'OpenRouter',
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

translate_fail(502, "El servicio de traducción ha fallado ($lastError). Inténtalo de nuevo o rellénalo a mano.");
