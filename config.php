<?php

return [
    // Database
    'db_host' => getenv('DB_HOST') ?: 'localhost',
    'db_port' => getenv('DB_PORT') ?: '3306',
    'db_name' => getenv('DB_DATABASE') ?: '',
    'db_user' => getenv('DB_USERNAME') ?: '',
    'db_pass' => getenv('DB_PASSWORD') ?: '',

    // SMTP
    'smtp_user' => getenv('SMTP_USER') ?: '',
    'smtp_pass' => getenv('SMTP_PASS') ?: '',

    // Website
    'base_url' => getenv('BASE_URL') ?: 'http://localhost/',

    // Cloudinary
    'cloudinary_cloud_name' => getenv('CLOUDINARY_CLOUD_NAME') ?: '',
    'cloudinary_api_key' => getenv('CLOUDINARY_API_KEY') ?: '',
    'cloudinary_api_secret' => getenv('CLOUDINARY_API_SECRET') ?: '',
    'cloudinary_folder' => getenv('CLOUDINARY_FOLDER') ?: '',
];
