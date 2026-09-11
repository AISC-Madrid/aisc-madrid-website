<?php

return [
    // Database
    'db_host' => getenv('DB_HOST') ?: 'localhost',
    'db_port' => getenv('DB_PORT') ?: 3306,
    'db_name' => getenv('DB_DATABASE') ?: '',
    'db_user' => getenv('DB_USERNAME') ?: '',
    'db_pass' => getenv('DB_PASSWORD') ?: '',

    // SMTP
    'smtp_user' => getenv('SMTP_USER') ?: '',
    'smtp_pass' => getenv('SMTP_PASS') ?: '',

    // Website
    'base_url' => getenv('BASE_URL') ?: 'https://aiscmadrid.com/',

    // Cloudinary
    'cloudinary_cloud_name' => getenv('CLOUDINARY_CLOUD_NAME') ?: '',
    'cloudinary_api_key' => getenv('CLOUDINARY_API_KEY') ?: '',
    'cloudinary_api_secret' => getenv('CLOUDINARY_API_SECRET') ?: '',
    'cloudinary_folder' => getenv('CLOUDINARY_FOLDER') ?: '',

    // Media (S3 / MinIO): DB stores keys, URL = media_base_url . '/' . key
    'media_base_url' => getenv('MEDIA_BASE_URL') ?: 'https://s3.aiscmadrid.com/aisc-public',

    // S3 / MinIO uploads
    's3_endpoint'   => getenv('S3_ENDPOINT') ?: 'https://s3.aiscmadrid.com',
    's3_region'     => getenv('S3_REGION') ?: 'us-east-1',
    's3_bucket'     => getenv('S3_BUCKET') ?: 'aisc-public',
    's3_access_key' => getenv('S3_ACCESS_KEY') ?: '',
    's3_secret_key' => getenv('S3_SECRET_KEY') ?: '',
];
