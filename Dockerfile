FROM php:8.4-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip libzip-dev libsqlite3-dev \
    && docker-php-ext-install pdo_mysql pdo_sqlite zip \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN cp -n .env.example .env \
    && sed -i \
        -e 's/^DB_CONNECTION=.*/DB_CONNECTION=mysql/' \
        -e 's/^# DB_HOST=.*/DB_HOST=db/' \
        -e 's/^# DB_PORT=.*/DB_PORT=3306/' \
        -e 's/^# DB_DATABASE=.*/DB_DATABASE=aisc_madrid/' \
        -e 's/^# DB_USERNAME=.*/DB_USERNAME=aisc/' \
        -e 's/^# DB_PASSWORD=.*/DB_PASSWORD=secret/' \
        .env \
    && composer install --no-interaction --prefer-dist \
    && npm install && npm run build

EXPOSE 8000

CMD php artisan key:generate --force \
    && php artisan migrate --force \
    && php artisan serve --host=0.0.0.0 --port=8000
