FROM php:8.3-apache

# Install system dependencies (gd + exif are needed to compress uploaded images to WebP)
RUN apt-get update \
    && apt-get install -y libzip-dev unzip libjpeg62-turbo-dev libpng-dev libwebp-dev \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install mysqli pdo pdo_mysql zip gd exif \
    && rm -rf /var/lib/apt/lists/*

# Allow event/project galleries with several phone photos per form
RUN printf "upload_max_filesize=20M\npost_max_size=64M\nmax_file_uploads=30\nmemory_limit=256M\n" \
    > /usr/local/etc/php/conf.d/uploads.ini

# Enable mod_rewrite
RUN a2enmod rewrite

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy the project
COPY . /var/www/html/

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80