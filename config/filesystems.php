<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim((string) env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        // Media bucket (MinIO at s3.aiscmadrid.com). Stored keys look like
        // "events-workshops/10/cover.webp"; Storage::disk('s3')->url($key) gives the public URL.
        // `?:` so empty values left over from the default .env still use the bucket defaults, and the
        // legacy site's variable names (S3_ACCESS_KEY, S3_SECRET_KEY, S3_BUCKET...) also work.
        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID') ?: env('S3_ACCESS_KEY'),
            'secret' => env('AWS_SECRET_ACCESS_KEY') ?: env('S3_SECRET_KEY'),
            'region' => env('AWS_DEFAULT_REGION') ?: (env('S3_REGION') ?: 'us-east-1'),
            'bucket' => env('AWS_BUCKET') ?: (env('S3_BUCKET') ?: 'aisc-public'),
            'url' => env('AWS_URL') ?: (env('MEDIA_BASE_URL') ?: 'https://s3.aiscmadrid.com/aisc-public'),
            'endpoint' => env('AWS_ENDPOINT') ?: (env('S3_ENDPOINT') ?: 'https://s3.aiscmadrid.com'),
            // MinIO only understands path-style URLs (s3.aiscmadrid.com/aisc-public/key). Laravel's default
            // .env sets AWS_USE_PATH_STYLE_ENDPOINT=false, which would break uploads, so it is always on.
            'use_path_style_endpoint' => true,
            // AWS SDK 3.337+ adds CRC32 checksums / aws-chunked uploads to every request by default,
            // which MinIO behind a proxy rejects. Only send them when an operation requires it.
            'request_checksum_calculation' => 'when_required',
            'response_checksum_validation' => 'when_required',
            'throw' => true,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
