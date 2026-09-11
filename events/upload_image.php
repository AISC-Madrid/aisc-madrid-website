<?php
// upload_image.php — image uploader shared by events/ and projects/.
// Compresses incoming images to WebP locally, then stores them depending on the destination:
//   - media bucket prefix (e.g. "events-workshops/12", "projects/3") -> S3; ['path'] is the object key
//   - any other folder                                              -> Cloudinary; ['path'] is the secure_url
// Either value is what gets stored in image_path / gallery_paths; cdn_from_image_path() resolves both.

require_once __DIR__ . '/../assets/cloudinary.php';
require_once __DIR__ . '/../assets/s3.php';

/**
 * Compress an uploaded image to WebP and store it at $destPath until size < $maxSize.
 */
function compressImageToWebP(string $srcPath, string $destPath, int $imgType, int $maxSize = 1048576): bool
{
    switch ($imgType) {
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($srcPath);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($srcPath);
            imagepalettetotruecolor($image);
            break;
        case IMAGETYPE_GIF:
            $image = imagecreatefromgif($srcPath);
            imagepalettetotruecolor($image);
            break;
        case IMAGETYPE_WEBP:
            $image = imagecreatefromwebp($srcPath);
            break;
        default:
            return false;
    }

    if (!$image) return false;

    $quality = 90;
    do {
        ob_start();
        imagewebp($image, null, $quality);
        $data = ob_get_clean();
        $size = strlen($data);
        $quality -= 10;
    } while ($size > $maxSize && $quality > 10);

    return file_put_contents($destPath, $data) !== false;
}

/**
 * Store a compressed WebP under $folder with a unique name, in S3 or Cloudinary (see header).
 *
 * @return array  ['path' => key or secure_url] | ['error' => '...']
 */
function storeWebP(string $tmpWebp, string $folder): array
{
    $folder = trim($folder, '/');
    $name = uniqid('img_', true);

    if (is_media_key("$folder/")) {
        $result = s3_put_object($tmpWebp, "$folder/$name.webp", 'image/webp');
        return isset($result['error']) ? $result : ['path' => $result['key']];
    }

    $result = cloudinary_upload($tmpWebp, "$folder/$name");
    return isset($result['error']) ? $result : ['path' => $result['url']];
}

/**
 * Compress + store a single $_FILES entry.
 *
 * @param string $fileFieldName  $_FILES key
 * @param string $folder         "events-workshops/12" / "projects/3/gallery" (S3) or any other folder (Cloudinary)
 * @return array  ['path' => key or secure_url] | ['error' => '...']
 */
function handleImageUpload(string $fileFieldName, string $folder): array
{
    $allowedTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP];

    if (!isset($_FILES[$fileFieldName]) || $_FILES[$fileFieldName]['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'No se subió ningún archivo o hubo un error en la subida.'];
    }

    $file = $_FILES[$fileFieldName];
    $imgType = @exif_imagetype($file['tmp_name']);
    if ($imgType === false || !in_array($imgType, $allowedTypes, true)) {
        return ['error' => 'Tipo de archivo inválido. Solo se permiten JPG, PNG, GIF y WebP.'];
    }

    $tmpWebp = tempnam(sys_get_temp_dir(), 'aisc_') . '.webp';
    if (!compressImageToWebP($file['tmp_name'], $tmpWebp, $imgType)) {
        return ['error' => 'Error al procesar la imagen.'];
    }

    $result = storeWebP($tmpWebp, $folder);
    @unlink($tmpWebp);

    if (isset($result['error'])) {
        return ['error' => 'Error al subir la imagen: ' . $result['error']];
    }

    return $result;
}

/**
 * Compress + store multiple files (a $_FILES[...] array of file inputs).
 */
function handleMultipleImageUpload(string $fileFieldName, string $folder): array
{
    $allowedTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP];
    $savedPaths = [];
    $errors = [];

    if (!isset($_FILES[$fileFieldName])) {
        return ['paths' => [], 'errors' => ['No se subieron archivos.']];
    }

    $files = $_FILES[$fileFieldName];
    $count = count($files['name']);

    for ($i = 0; $i < $count; $i++) {
        $name = $files['name'][$i];
        $tmpName = $files['tmp_name'][$i];
        $err = $files['error'][$i];

        if ($err !== UPLOAD_ERR_OK) {
            $errors[] = "$name: error al subir el archivo.";
            continue;
        }

        $imgType = @exif_imagetype($tmpName);
        if ($imgType === false || !in_array($imgType, $allowedTypes, true)) {
            $errors[] = "$name: tipo de archivo inválido.";
            continue;
        }

        $tmpWebp = tempnam(sys_get_temp_dir(), 'aisc_') . '.webp';
        if (!compressImageToWebP($tmpName, $tmpWebp, $imgType)) {
            $errors[] = "$name: no se pudo procesar la imagen.";
            @unlink($tmpWebp);
            continue;
        }

        $result = storeWebP($tmpWebp, $folder);
        @unlink($tmpWebp);

        if (isset($result['error'])) {
            $errors[] = "$name: " . $result['error'];
            continue;
        }

        $savedPaths[] = $result['path'];
    }

    return ['paths' => $savedPaths, 'errors' => $errors];
}
