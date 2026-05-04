<?php
/**
 * Migración de imágenes locales a Cloudinary.
 *
 * Uso:
 *   /dashboard/migrate_to_cloudinary.php          → ejecuta de verdad
 *   /dashboard/migrate_to_cloudinary.php?dry=1    → dry run (no sube ni modifica BD)
 *
 * Idempotente: las subidas usan overwrite=true y las filas con URLs absolutas se saltan.
 */

session_start();
if (!isset($_SESSION['activated']) || ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    die("Acceso no autorizado");
}

set_time_limit(0);
@ini_set('memory_limit', '512M');

require_once __DIR__ . '/../assets/cloudinary.php';
require_once __DIR__ . '/../assets/db.php';

$dryRun = !empty($_GET['dry']);
$base = realpath(__DIR__ . '/..');

header('Content-Type: text/html; charset=utf-8');
echo "<pre style=\"font-family:Consolas,monospace;font-size:13px;\">";
echo $dryRun
    ? "DRY RUN — nada se sube ni se escribe en la BD.\n\n"
    : "MIGRACIÓN REAL — se subirán archivos y se actualizará la BD.\n\n";
@ob_flush(); @flush();

$uploaded = 0;
$skipped = 0;
$errors = 0;
$dbUpdates = 0;

// Map local relative path ("images/...") -> Cloudinary secure URL.
$urlMap = [];

function isAbsoluteUrl(?string $s): bool
{
    return $s !== null && $s !== '' && preg_match('#^https?://#i', $s) === 1;
}

function publicIdFor(string $relPath): string
{
    // strip "images/" prefix and extension to mirror cdn() routing.
    $p = preg_replace('#^images/#', '', $relPath);
    return preg_replace('#\.[^./]+$#', '', $p);
}

function resourceTypeFor(string $path): string
{
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    return $ext === 'ico' ? 'raw' : 'image';
}

echo "=== 1. Subiendo archivos de images/ ===\n";
$imagesDir = "$base/images";
if (is_dir($imagesDir)) {
    $iter = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($imagesDir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    foreach ($iter as $file) {
        if ($file->isDir()) continue;
        $abs = $file->getPathname();
        $rel = str_replace('\\', '/', substr($abs, strlen($base) + 1)); // "images/..."

        $publicId = publicIdFor($rel);
        echo "  $rel → $publicId ... ";
        @ob_flush(); @flush();

        if ($dryRun) {
            echo "(dry)\n";
            $skipped++;
            continue;
        }

        $r = cloudinary_upload($abs, $publicId, ['resource_type' => resourceTypeFor($rel)]);
        if (isset($r['error'])) {
            echo "ERROR: " . htmlspecialchars($r['error']) . "\n";
            $errors++;
            continue;
        }

        echo "OK\n";
        $urlMap[$rel] = $r['url'];
        $uploaded++;
        @ob_flush(); @flush();
    }
} else {
    echo "  (la carpeta images/ no existe — saltando)\n";
}

/**
 * Reescribe filas que aún apunten a una ruta local. Solo se modifica si la ruta
 * está en $urlMap (es decir, el archivo existe localmente y se acaba de subir).
 *
 * @param string   $table        Nombre de tabla
 * @param string   $idCol        Columna PK
 * @param string   $imageCol     Columna de imagen principal
 * @param ?string  $galleryCol   Columna JSON de galería (o null)
 * @param string   $label        Etiqueta legible para los logs
 */
function rewriteRows(mysqli $conn, array $urlMap, bool $dryRun, string $table, string $idCol, string $imageCol, ?string $galleryCol, string $label, int &$dbUpdates): void
{
    echo "\n=== Reescribiendo BD: $table ===\n";

    $cols = $galleryCol ? "$idCol, $imageCol, $galleryCol" : "$idCol, $imageCol";
    $res = $conn->query("SELECT $cols FROM $table");
    if (!$res) {
        echo "  ERROR consultando $table: " . htmlspecialchars($conn->error) . "\n";
        return;
    }

    while ($row = $res->fetch_assoc()) {
        $changes = [];

        $newImage = $row[$imageCol];
        if ($newImage && !isAbsoluteUrl($newImage) && isset($urlMap[$newImage])) {
            $newImage = $urlMap[$newImage];
            $changes[] = $imageCol;
        }

        $newGallery = null;
        if ($galleryCol) {
            $newGallery = $row[$galleryCol];
            if ($newGallery) {
                $arr = json_decode($newGallery, true);
                if (is_array($arr)) {
                    $changedGallery = false;
                    foreach ($arr as $i => $p) {
                        if ($p && !isAbsoluteUrl($p) && isset($urlMap[$p])) {
                            $arr[$i] = $urlMap[$p];
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

rewriteRows($conn, $urlMap, $dryRun, 'events',   'id', 'image_path', 'gallery_paths', 'event',   $dbUpdates);
rewriteRows($conn, $urlMap, $dryRun, 'projects', 'id', 'image_path', 'gallery_paths', 'project', $dbUpdates);
rewriteRows($conn, $urlMap, $dryRun, 'members',  'id', 'image_path', null,            'member',  $dbUpdates);

echo "\n=== Resumen ===\n";
echo "  Archivos subidos:    $uploaded\n";
echo "  Saltados (dry run):  $skipped\n";
echo "  Errores de subida:   $errors\n";
echo "  Filas BD modificadas: $dbUpdates\n";
echo $dryRun
    ? "\n[Dry run — nada cambió. Vuelve a abrir sin ?dry=1 para ejecutar.]\n"
    : "\nHecho.\n";
echo "</pre>";

$conn->close();
