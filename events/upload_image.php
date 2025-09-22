<?php
// upload_image.php

/**
 * Redimensiona/comprime la imagen y la convierte a WebP hasta que pese < 1 MB
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

    $quality = 90; // empezamos con buena calidad
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
 * Maneja la subida de una sola imagen
 */
function handleImageUpload(string $fileFieldName, string $targetFolder): array
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

    if (!is_dir($targetFolder)) {
        mkdir($targetFolder, 0755, true);
    }

    $newName = uniqid('img_', true) . '.webp';
    $targetPath = rtrim($targetFolder, '/') . '/' . $newName;

    if (compressImageToWebP($file['tmp_name'], $targetPath, $imgType)) {
        return ['path' => $targetPath];
    }

    return ['error' => 'Error al procesar la imagen.'];
}

/**
 * Maneja la subida de múltiples imágenes
 */
function handleMultipleImageUpload(string $fileFieldName, string $targetFolder): array
{
    $allowedTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP];
    $savedPaths = [];
    $errors = [];

    if (!isset($_FILES[$fileFieldName])) {
        return ['paths' => [], 'errors' => ['No se subieron archivos.']];
    }

    $files = $_FILES[$fileFieldName];
    $count = count($files['name']);

    if (!is_dir($targetFolder)) {
        mkdir($targetFolder, 0755, true);
    }

    for ($i = 0; $i < $count; $i++) {
        $file = [
            'name' => $files['name'][$i],
            'tmp_name' => $files['tmp_name'][$i],
            'error' => $files['error'][$i],
            'size' => $files['size'][$i]
        ];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "{$file['name']}: error al subir el archivo.";
            continue;
        }

        $imgType = @exif_imagetype($file['tmp_name']);
        if ($imgType === false || !in_array($imgType, $allowedTypes, true)) {
            $errors[] = "{$file['name']}: tipo de archivo inválido.";
            continue;
        }

        $newName = uniqid('img_', true) . '.webp';
        $targetPath = rtrim($targetFolder, '/') . '/' . $newName;

        if (compressImageToWebP($file['tmp_name'], $targetPath, $imgType)) {
            $savedPaths[] = $targetPath;
        } else {
            $errors[] = "{$file['name']}: no se pudo procesar la imagen.";
        }
    }

    return ['paths' => $savedPaths, 'errors' => $errors];
}
