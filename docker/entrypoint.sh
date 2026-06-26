#!/usr/bin/env bash
set -e

# Railway injects a dynamic $PORT — make Apache listen on it (default 8080 locally).
PORT="${PORT:-8080}"
sed -ri "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Ensure a public storage symlink exists (for receipt images).
if [ ! -e public/storage ]; then
    php artisan storage:link || true
fi

# Run migrations against the production database.
php artisan migrate --force

# Cache config/routes/views for performance (env vars are present at runtime).
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec apache2-foreground
