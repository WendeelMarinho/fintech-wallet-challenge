FROM node:20-bookworm-slim AS frontend

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY vite.config.js ./
COPY resources ./resources

RUN npm run build

RUN test -f public/build/manifest.json && test -n "$(ls -A public/build/assets 2>/dev/null)"

FROM php:8.2-cli-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring xml zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

COPY --from=frontend /app/public/build ./public/build

RUN test -f public/build/manifest.json && test -n "$(ls -A public/build/assets 2>/dev/null)"

RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts \
    && composer dump-autoload --optimize \
    && mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data storage/logs bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R ug+rwx storage bootstrap/cache

USER www-data

EXPOSE 8080

CMD ["sh", "-c", "php artisan route:clear && php artisan config:clear && php artisan view:clear && php artisan cache:clear && php artisan config:cache && php artisan route:cache && php artisan serve --host=0.0.0.0 --port=8080"]
