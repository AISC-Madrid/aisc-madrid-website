<?php

namespace App\Concerns;

use Illuminate\Support\Str;

/**
 * Turns a stored image value into a public URL, like cdn_from_image_path() on the legacy site:
 *  - absolute http(s) URLs are returned as-is,
 *  - media bucket keys (events-workshops/…, projects/…, members/…) point to the S3 bucket,
 *  - anything else is a legacy local path served from Cloudinary (or the app's public folder).
 */
trait ResolvesMediaUrls
{
    public static function mediaUrl(?string $path): ?string
    {
        if ($path === null || trim($path) === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        $path = ltrim(str_replace('\\', '/', trim($path)), '/');

        if (Str::startsWith($path, config('services.media.key_prefixes', []))) {
            return rtrim((string) config('services.media.base_url'), '/').'/'.self::encodePath($path);
        }

        return self::legacyImageUrl($path);
    }

    private static function legacyImageUrl(string $path): string
    {
        $cloudName = config('services.cloudinary.cloud_name');

        if (blank($cloudName)) {
            return asset($path);
        }

        $resourceType = Str::lower(pathinfo($path, PATHINFO_EXTENSION)) === 'ico' ? 'raw' : 'image';
        $folder = trim((string) config('services.cloudinary.folder'), '/');
        $relativePath = Str::startsWith($path, 'images/') ? Str::after($path, 'images/') : $path;
        $fullPath = $folder === '' ? $relativePath : "{$folder}/{$relativePath}";

        return "https://res.cloudinary.com/{$cloudName}/{$resourceType}/upload/".self::encodePath($fullPath);
    }

    private static function encodePath(string $path): string
    {
        return implode('/', array_map('rawurlencode', explode('/', $path)));
    }
}
