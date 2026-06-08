# ── Stage 1: Build Vite assets ────────────────────────────────────────────────
FROM node:20-alpine AS assets
WORKDIR /app
COPY package*.json ./
RUN npm ci --prefer-offline
COPY . .
RUN npm run build

# ── Stage 2: PHP runtime (PHP-FPM + Nginx + Supervisor) ───────────────────────
FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
        nginx \
        supervisor \
        curl \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        libzip-dev \
        icu-dev \
        oniguruma-dev \
        libxml2-dev \
    && docker-php-ext-configure gd \
            --with-freetype \
            --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
            pdo_mysql \
            mbstring \
            xml \
            dom \
            bcmath \
            gd \
            zip \
            intl \
            opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install PHP dependencies first for better layer caching
COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-interaction \
        --prefer-dist

# Copy application source
COPY . .

# Copy Vite-built assets from Stage 1
COPY --from=assets /app/public/build ./public/build

# Rebuild optimised autoloader with app code present
RUN composer dump-autoload --no-dev --optimize --no-interaction

RUN mkdir -p /var/log/supervisor \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

COPY docker/nginx.conf       /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh    /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
