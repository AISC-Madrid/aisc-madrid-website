<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    /*
    | Media bucket (S3 / MinIO). The database stores object keys such as
    | "events-workshops/10/cover.webp"; the public URL is base_url/key.
    */
    'media' => [
        'base_url' => env('MEDIA_BASE_URL') ?: 'https://s3.aiscmadrid.com/aisc-public',
        'key_prefixes' => ['events-workshops/', 'projects/', 'members/'],
    ],

    /*
    | Legacy images that were never migrated to the bucket are still served
    | from Cloudinary (the old "images/" folder mirrored into this folder).
    */
    'cloudinary' => [
        'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
        'folder' => env('CLOUDINARY_FOLDER', 'aisc_madrid'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
