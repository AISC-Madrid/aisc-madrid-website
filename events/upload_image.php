<?php
// upload_image.php — Cloudinary-backed uploader.
// Compresses incoming images to WebP locally, then uploads to Cloudinary under the
// configured root folder. The path returned in ['path'] is the full Cloudinary
// secure_url, which is what gets stored in events.image_path / events.gallery_paths.

require_once __DIR__ . '/../assets/cloudinary.php';

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
 * Compress + upload a single $_FILES entry to Cloudinary.
 *
 * @param string $fileFieldName  $_FILES key
 * @param string $cloudinarySubfolder  Path inside the Cloudinary root folder, e.g. "events/event12" or "events/event12/gallery"
 * @return array  ['path' => secure_url, 'public_id' => ...] | ['error' => '...']
 */
function handleImageUpload(string $fileFieldName, string $cloudinarySubfolder): array
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

    $publicId = trim($cloudinarySubfolder, '/') . '/' . uniqid('img_', true);
    $result = cloudinary_upload($tmpWebp, $publicId);

    @unlink($tmpWebp);

    if (isset($result['error'])) {
        return ['error' => 'Error al subir a Cloudinary: ' . $result['error']];
    }

    return ['path' => $result['url'], 'public_id' => $result['public_id']];
}

/**
 * Compress + upload multiple files (a $_FILES[...] array of file inputs) to Cloudinary.
 */
function handleMultipleImageUpload(string $fileFieldName, string $cloudinarySubfolder): array
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

        $publicId = trim($cloudinarySubfolder, '/') . '/' . uniqid('img_', true);
        $result = cloudinary_upload($tmpWebp, $publicId);
        @unlink($tmpWebp);

        if (isset($result['error'])) {
            $errors[] = "$name: " . $result['error'];
            continue;
        }

        $savedPaths[] = $result['url'];
    }

    return ['paths' => $savedPaths, 'errors' => $errors];
}
