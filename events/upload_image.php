<?php
// upload_image.php

/**
 * Maneja la subida de una sola imagen
 */
function handleImageUpload(string $fileFieldName, string $targetFolder): array
{
    $maxSize = 1 * 1024 * 1024; // 1 MB
    $allowedTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP];

    if (!isset($_FILES[$fileFieldName]) || $_FILES[$fileFieldName]['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'No se subió ningún archivo o hubo un error en la subida.'];
    }

    $file = $_FILES[$fileFieldName];

    if ($file['size'] > $maxSize) {
        return ['error' => 'Archivo demasiado grande. Máximo permitido: 1 MB.'];
    }

    $imgType = @exif_imagetype($file['tmp_name']);
    if ($imgType === false || !in_array($imgType, $allowedTypes, true)) {
        return ['error' => 'Tipo de archivo inválido. Solo se permiten JPG, PNG, GIF y WebP.'];
    }

    if (!is_dir($targetFolder)) {
        mkdir($targetFolder, 0755, true);
    }

    $extension = ltrim(image_type_to_extension($imgType), '.');
    $newName = uniqid('img_', true) . '.' . $extension;
    $targetPath = rtrim($targetFolder, '/') . '/' . $newName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['path' => $targetPath];
    }

    return ['error' => 'Error al guardar el archivo subido.'];
}

/**
 * Maneja la subida de múltiples imágenes
 */
function handleMultipleImageUpload(string $fileFieldName, string $targetFolder): array
{
    $maxSize = 1 * 1024 * 1024; // 1 MB
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

        if ($file['size'] > $maxSize) {
            $errors[] = "{$file['name']}: excede el tamaño máximo de 1 MB.";
            continue;
        }

        $imgType = @exif_imagetype($file['tmp_name']);
        if ($imgType === false || !in_array($imgType, $allowedTypes, true)) {
            $errors[] = "{$file['name']}: tipo de archivo inválido.";
            continue;
        }

        $extension = ltrim(image_type_to_extension($imgType), '.');
        $newName = uniqid('img_', true) . '.' . $extension;
        $targetPath = rtrim($targetFolder, '/') . '/' . $newName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $savedPaths[] = $targetPath;
        } else {
            $errors[] = "{$file['name']}: no se pudo guardar en el servidor.";
        }
    }

    return ['paths' => $savedPaths, 'errors' => $errors];
}
