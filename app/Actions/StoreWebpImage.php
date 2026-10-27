<?php

namespace App\Actions;

use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Compress an uploaded image to WebP (under ~1 MB, like the legacy upload_image.php)
 * and store it in the media bucket. Returns the object key, e.g. "events-workshops/12/img_abc.webp".
 *
 * Photos are scaled down to MAX_DIMENSION first. If this PHP build cannot write WebP,
 * the original file is uploaded unchanged instead of failing.
 */
class StoreWebpImage
{
    private const MAX_BYTES = 1_048_576;

    private const MAX_DIMENSION = 2000;

    /**
     * Phone photos (12–50 MP) need far more than PHP's default 128 MB once decoded by GD.
     */
    private const MEMORY_LIMIT = '512M';

    public function __invoke(UploadedFile $file, string $folder): string
    {
        $folder = trim($folder, '/');

        if (! $this->canConvertToWebp($file)) {
            return $this->storeOriginal($file, $folder);
        }

        $previousMemoryLimit = ini_get('memory_limit');
        ini_set('memory_limit', self::MEMORY_LIMIT);

        try {
            $image = $this->scaleDown($this->createImage($file));

            $quality = 90;

            do {
                ob_start();
                imagewebp($image, null, $quality);
                $webp = (string) ob_get_clean();
                $quality -= 10;
            } while (strlen($webp) > self::MAX_BYTES && $quality > 10);

            unset($image);
        } finally {
            ini_set('memory_limit', (string) $previousMemoryLimit);
        }

        $key = $folder.'/img_'.Str::lower((string) Str::ulid()).'.webp';

        Storage::disk('s3')->put($key, $webp, ['ContentType' => 'image/webp']);

        return $key;
    }

    private function canConvertToWebp(UploadedFile $file): bool
    {
        $reader = match ($file->getMimeType()) {
            'image/jpeg' => 'imagecreatefromjpeg',
            'image/png' => 'imagecreatefrompng',
            'image/gif' => 'imagecreatefromgif',
            'image/webp' => 'imagecreatefromwebp',
            default => null,
        };

        return $reader !== null && function_exists($reader) && function_exists('imagewebp');
    }

    private function storeOriginal(UploadedFile $file, string $folder): string
    {
        $extension = $file->guessExtension() ?: Str::lower($file->getClientOriginalExtension()) ?: 'jpg';
        $key = $folder.'/img_'.Str::lower((string) Str::ulid()).'.'.$extension;

        Storage::disk('s3')->put($key, (string) file_get_contents($file->getRealPath()), [
            'ContentType' => $file->getMimeType() ?: 'application/octet-stream',
        ]);

        return $key;
    }

    private function createImage(UploadedFile $file): GdImage
    {
        $path = $file->getRealPath();

        $image = match ($file->getMimeType()) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/gif' => @imagecreatefromgif($path),
            'image/webp' => @imagecreatefromwebp($path),
            default => false,
        };

        if (! $image instanceof GdImage) {
            throw new RuntimeException("Could not read image: {$file->getClientOriginalName()}");
        }

        if (! imageistruecolor($image)) {
            imagepalettetotruecolor($image);
        }

        imagealphablending($image, true);
        imagesavealpha($image, true);

        return $image;
    }

    private function scaleDown(GdImage $image): GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $longestSide = max($width, $height);

        if ($longestSide <= self::MAX_DIMENSION) {
            return $image;
        }

        $ratio = self::MAX_DIMENSION / $longestSide;
        $scaled = imagescale($image, (int) round($width * $ratio), (int) round($height * $ratio), IMG_BICUBIC);

        if (! $scaled instanceof GdImage) {
            return $image;
        }

        imagealphablending($scaled, true);
        imagesavealpha($scaled, true);

        return $scaled;
    }
}
