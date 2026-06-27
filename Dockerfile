# ---------- Stage 1: build front-end assets (Vite/Tailwind for Breeze pages) ----------
FROM node:20-alpine AS assets
WORKDIR /app
COPY package*.json vite.config.js tailwind.config.js postcss.config.js ./
RUN npm ci
COPY resources ./resources
RUN npm run build

# ---------- Stage 2: PHP + Apache runtime ----------
FROM php:8.2-apache

# System libraries + PHP extensions Laravel needs (pdo_mysql, zip, gd).
RUN apt-get update && apt-get install -y \
        libzip-dev libpng-dev libjpeg-dev libfreetype6-dev unzip git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql zip gd \
    && rm -rf /var/lib/apt/lists/*

# mod_php requires exactly ONE MPM (prefork). Disable any others to avoid
# "AH00534: More than one MPM loaded", then enable prefork + rewrite.
RUN a2dismod mpm_event mpm_worker 2>/dev/null || true \
 && a2enmod mpm_prefork rewrite \
 && [ "$(apache2ctl -M 2>/dev/null | grep -ci 'mpm_')" = "1" ]

# Composer (copied from the official Composer image).
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install PHP deps first (better build caching).
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

# App source + built assets.
COPY . .
COPY --from=assets /app/public/build ./public/build
RUN composer dump-autoload --optimize

# Point Apache at Laravel's /public directory.
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Laravel needs these writable.
RUN chown -R www-data:www-data storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

ENTRYPOINT ["entrypoint"]
