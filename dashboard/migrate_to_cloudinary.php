<?php
/**
 * Migración de imágenes locales a Cloudinary.
 *
 * Uso:
 *   /dashboard/migrate_to_cloudinary.php                  → sube + reescribe BD
 *   /dashboard/migrate_to_cloudinary.php?dry=1            → dry run (no toca nada)
 *   /dashboard/migrate_to_cloudinary.php?skip_upload=1    → solo reescribe la BD
 *                                                           (usa cuando ya subiste y solo falta la BD)
 *
 * Idempotente: las subidas usan overwrite=true; las filas con URL absoluta se saltan.
 *
 * Robusto en hosting compartido: la BD no se abre hasta el final (tras las subidas),
 * para evitar "MySQL server has gone away" por timeout de conexión durante uploads largos.
 */

session_start();
if (!isset($_SESSION['activated']) || ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    die("Acceso no autorizado");
}

set_time_limit(0);
@ini_set('memory_limit', '512M');

require_once __DIR__ . '/../assets/cloudinary.php';

$dryRun      = !empty($_GET['dry']);
$skipUpload  = !empty($_GET['skip_upload']);
$onlyMissing = !empty($_GET['only_missing']);
$base        = realpath(__DIR__ . '/..');

header('Content-Type: text/html; charset=utf-8');
echo "<pre style=\"font-family:Consolas,monospace;font-size:13px;\">";
if ($dryRun)          echo "DRY RUN — nada se sube ni se escribe en la BD.\n\n";
elseif ($skipUpload)  echo "SKIP UPLOAD — solo reescribe la BD (no sube archivos).\n\n";
elseif ($onlyMissing) echo "ONLY MISSING — sube solo los archivos que aún no están en Cloudinary.\n\n";
else                  echo "MIGRACIÓN REAL — se subirán archivos y se actualizará la BD.\n\n";
@ob_flush(); @flush();

$uploaded = 0;
$skipped  = 0;
$errors   = 0;
$dbUpdates = 0;

function isAbsoluteUrl(?string $s): bool
{
    return $s !== null && $s !== '' && preg_match('#^https?://#i', $s) === 1;
}

/**
 * Detecta una URL Cloudinary "rota" (con backslashes URL-encoded) generada por la
 * versión anterior de cdn(). Devuelve la ruta local equivalente, o null si la URL
 * no parece rota.
 */
function recoverLocalPathFromBrokenUrl(string $url): ?string
{
    if (strpos($url, '%5C') === false && strpos($url, '\\') === false) return null;

    if (!preg_match('#^https?://res\.cloudinary\.com/[^/]+/(?:image|raw)/upload/(?:v\d+/)?(.+)$#i', $url, $m)) {
        return null;
    }

    $path = urldecode($m[1]);
    $path = str_replace('\\', '/', $path);

    // Quita el folder raíz aisc_madrid/ si está presente.
    $path = preg_replace('#^aisc_madrid/#', '', $path);

    // Asegura prefijo "images/" para que cdn() lo trate como ruta legacy.
    if (strpos($path, 'images/') !== 0) {
        $path = 'images/' . $path;
    }
    return $path;
}

function publicIdFor(string $relPath): string
{
    $p = preg_replace('#^images/#', '', $relPath);
    return preg_replace('#\.[^./]+$#', '', $p);
}

function resourceTypeFor(string $path): string
{
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    return $ext === 'ico' ? 'raw' : 'image';
}

/**
 * Comprueba si una URL ya devuelve 200 (existe el archivo en Cloudinary).
 */
function cloudinaryAssetExists(string $url): bool
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_NOBODY         => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 10,
    ]);
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $code === 200;
}

// --- Fase 1: subir archivos ---
echo "=== 1. Subiendo archivos de images/ ===\n";
if ($skipUpload) {
    echo "  (omitido por skip_upload=1)\n";
} else {
    $imagesDir = "$base/images";
    if (is_dir($imagesDir)) {
        $iter = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($imagesDir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        foreach ($iter as $file) {
            if ($file->isDir()) continue;
            $abs = $file->getPathname();
            $rel = str_replace('\\', '/', substr($abs, strlen($base) + 1));

            $publicId = publicIdFor($rel);
            echo "  $rel → $publicId ... ";
            @ob_flush(); @flush();

            if ($dryRun) {
                echo "(dry)\n";
                $skipped++;
                continue;
            }

            if ($onlyMissing && cloudinaryAssetExists(cdn($rel))) {
                echo "ya existe (skip)\n";
                $skipped++;
                @ob_flush(); @flush();
                continue;
            }

            $r = cloudinary_upload($abs, $publicId, ['resource_type' => resourceTypeFor($rel)]);
            if (isset($r['error'])) {
                echo "ERROR: " . htmlspecialchars($r['error']) . "\n";
                $errors++;
                continue;
            }

            echo "OK\n";
            $uploaded++;
            @ob_flush(); @flush();
        }
    } else {
        echo "  (la carpeta images/ no existe — saltando)\n";
    }
}

// --- Fase 2: reescribir BD ---
// La conexión se abre AHORA, no al inicio del script, para no morir por timeout.
echo "\n=== 2. Reescribiendo BD ===\n";
@ob_flush(); @flush();

if ($dryRun) {
    require_once __DIR__ . '/../assets/db.php';
} else {
    require_once __DIR__ . '/../assets/db.php';
    // Por si la conexión llegara muerta por algún motivo, hacer ping ligero.
    if (!@$conn->ping()) {
        echo "  Reabriendo conexión MySQL...\n";
        $config = include(__DIR__ . '/../config.php');
        $conn = new mysqli('localhost', $config['db_user'], $config['db_pass'], $config['db_name'], 3306);
        if ($conn->connect_error) {
            die("ERROR conectando MySQL: " . $conn->connect_error);
        }
    }
}

/**
 * Reescribe filas con paths locales a sus URLs Cloudinary calculadas vía cdn().
 *
 * @param string  $imageCol    Columna de imagen principal
 * @param ?string $galleryCol  Columna JSON de galería (o null)
 */
function rewriteRows(mysqli $conn, bool $dryRun, string $table, string $idCol, string $imageCol, ?string $galleryCol, string $label, int &$dbUpdates): void
{
    echo "\n  --- $table ---\n";
    @ob_flush(); @flush();

    $cols = $galleryCol ? "$idCol, $imageCol, $galleryCol" : "$idCol, $imageCol";
    $res = $conn->query("SELECT $cols FROM $table");
    if (!$res) {
        echo "  ERROR consultando $table: " . htmlspecialchars($conn->error) . "\n";
        return;
    }

    while ($row = $res->fetch_assoc()) {
        $changes = [];

        $newImage = $row[$imageCol];
        if ($newImage) {
            if (!isAbsoluteUrl($newImage)) {
                // Ruta legacy (local) → genera URL Cloudinary.
                $newImage = cdn($newImage);
                $changes[] = $imageCol;
            } elseif ($recovered = recoverLocalPathFromBrokenUrl($newImage)) {
                // URL rota previa (con %5C) → regenera bien.
                $newImage = cdn($recovered);
                $changes[] = "$imageCol (reparada)";
            }
        }

        $newGallery = null;
        if ($galleryCol) {
            $newGallery = $row[$galleryCol];
            if ($newGallery) {
                $arr = json_decode($newGallery, true);
                if (is_array($arr)) {
                    $changedGallery = false;
                    foreach ($arr as $i => $p) {
                        if (!$p) continue;
                        if (!isAbsoluteUrl($p)) {
                            $arr[$i] = cdn($p);
                            $changedGallery = true;
                        } elseif ($recovered = recoverLocalPathFromBrokenUrl($p)) {
                            $arr[$i] = cdn($recovered);
                            $changedGallery = true;
                        }
                    }
                    if ($changedGallery) {
                        $newGallery = json_encode($arr);
                        $changes[] = $galleryCol;
                    }
                }
            }
        }

        if (!$changes) continue;

        echo "  $label #{$row[$idCol]}: " . implode(', ', $changes) . "\n";
        @ob_flush(); @flush();
        $dbUpdates++;
        if ($dryRun) continue;

        if ($galleryCol) {
            $stmt = $conn->prepare("UPDATE $table SET $imageCol = ?, $galleryCol = ? WHERE $idCol = ?");
            $stmt->bind_param("ssi", $newImage, $newGallery, $row[$idCol]);
        } else {
            $stmt = $conn->prepare("UPDATE $table SET $imageCol = ? WHERE $idCol = ?");
            $stmt->bind_param("si", $newImage, $row[$idCol]);
        }
        if (!$stmt->execute()) {
            echo "    ERROR UPDATE: " . htmlspecialchars($stmt->error) . "\n";
        }
        $stmt->close();
    }
}

rewriteRows($conn, $dryRun, 'events',   'id', 'image_path', 'gallery_paths', 'event',   $dbUpdates);
rewriteRows($conn, $dryRun, 'projects', 'id', 'image_path', 'gallery_paths', 'project', $dbUpdates);
rewriteRows($conn, $dryRun, 'members',  'id', 'image_path', null,            'member',  $dbUpdates);

echo "\n=== Resumen ===\n";
echo "  Archivos subidos:      $uploaded\n";
echo "  Saltados (dry/skip):   $skipped\n";
echo "  Errores de subida:     $errors\n";
echo "  Filas BD modificadas:  $dbUpdates\n";
echo $dryRun
    ? "\n[Dry run — nada cambió.]\n"
    : "\nHecho.\n";
echo "</pre>";

$conn->close();
