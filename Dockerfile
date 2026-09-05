FROM php:8.3-apache

# Install system dependencies
RUN apt-get update \
    && apt-get install -y libzip-dev unzip \
    && docker-php-ext-install mysqli pdo pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*

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