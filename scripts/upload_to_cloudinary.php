<?php
/**
 * One-shot migration: upload a local image tree to Cloudinary.
 *
 * Usage (PowerShell, from project root):
 *   php scripts/upload_to_cloudinary.php [source-dir]
 *
 * source-dir defaults to "images". The directory is walked recursively
 * and every image file is uploaded with a public_id that mirrors its
 * path under cdn()'s conventions, so cdn() / cdn_from_image_path()
 * resolve to it without further changes.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("CLI only.\n");
}

require_once __DIR__ . '/../assets/cloudinary.php';

$projectRoot = realpath(__DIR__ . '/..');
$sourceArg = $argv[1] ?? 'images';
$sourceDir = realpath($projectRoot . DIRECTORY_SEPARATOR . $sourceArg)
    ?: realpath($sourceArg);

if (!$sourceDir || !is_dir($sourceDir)) {
    fwrite(STDERR, "Source directory not found: $sourceArg\n");
    exit(1);
}

$imageExt = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
$rawExt   = ['ico'];

$it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($sourceDir, FilesystemIterator::SKIP_DOTS)
);

$ok = $skip = $fail = 0;

foreach ($it as $file) {
    if (!$file->isFile()) continue;
    $ext = strtolower($file->getExtension());

    $isRaw = in_array($ext, $rawExt, true);
    if (!$isRaw && !in_array($ext, $imageExt, true)) { $skip++; continue; }

    $abs = $file->getPathname();
    $rel = ltrim(str_replace('\\', '/', substr($abs, strlen($projectRoot) + 1)), '/');

    // Mirror cdn(): drop leading "images/" so the asset lives under the
    // configured Cloudinary root folder directly.
    $cloudRel = $rel;
    if (strpos($cloudRel, 'images/') === 0) {
        $cloudRel = substr($cloudRel, strlen('images/'));
    }

    // image public_ids omit the extension; raw public_ids keep it.
    $publicId = $isRaw ? $cloudRel : preg_replace('/\.[^.\/]+$/', '', $cloudRel);
    $resourceType = $isRaw ? 'raw' : 'image';

    echo "[$resourceType] $rel\n  -> $publicId\n";

    $res = cloudinary_upload($abs, $publicId, ['resource_type' => $resourceType]);

    if (isset($res['error'])) {
        echo "  ERROR: {$res['error']}\n";
        $fail++;
    } else {
        echo "  OK: {$res['url']}\n";
        $ok++;
    }
}

echo "\nDone. uploaded=$ok skipped=$skip failed=$fail\n";
exit($fail ? 2 : 0);
