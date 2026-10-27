<?php

namespace App\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Compress an uploaded image to WebP (under ~1 MB, like the legacy upload_image.php)
 * and store it in the media bucket. Returns the object key, e.g. "events-workshops/12/img_abc.webp".
 */
class StoreWebpImage
{
    private const MAX_BYTES = 1_048_576;

    public function __invoke(UploadedFile $file, string $folder): string
    {
        $image = $this->createImage($file);

        $quality = 90;

        do {
            ob_start();
            imagewebp($image, null, $quality);
            $webp = (string) ob_get_clean();
            $quality -= 10;
        } while (strlen($webp) > self::MAX_BYTES && $quality > 10);

        $key = trim($folder, '/').'/img_'.Str::lower((string) Str::ulid()).'.webp';

        Storage::disk('s3')->put($key, $webp, ['ContentType' => 'image/webp']);

        return $key;
    }

    /**
     * @return \GdImage
     */
    private function createImage(UploadedFile $file): mixed
    {
        $path = $file->getRealPath();

        $image = match ($file->getMimeType()) {
            'image/jpeg' => imagecreatefromjpeg($path),
            'image/png' => imagecreatefrompng($path),
            'image/gif' => imagecreatefromgif($path),
            'image/webp' => imagecreatefromwebp($path),
            default => false,
        };

        if ($image === false) {
            throw new RuntimeException("Unsupported image: {$file->getClientOriginalName()}");
        }

        if (! imageistruecolor($image)) {
            imagepalettetotruecolor($image);
        }

        imagealphablending($image, true);
        imagesavealpha($image, true);

        return $image;
    }
}
