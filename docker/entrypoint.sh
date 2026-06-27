#!/usr/bin/env bash
set -e

# Railway injects a dynamic $PORT — make Apache listen on it (default 8080 locally).
PORT="${PORT:-8080}"
sed -ri "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf

# Write a correct vhost on every boot (cache-immune). DocumentRoot MUST be
# Laravel's public/ dir, and that dir must be granted access — otherwise Apache
# serves the project root and 403s with "AH01630: client denied". We write the
# config and ALSO write it directly into sites-enabled (and disable any other
# enabled site) because Apache loads from sites-enabled, not sites-available.
VHOST_CONF='<VirtualHost *:__PORT__>
    DocumentRoot /var/www/html/public
    <Directory /var/www/html/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>'

# substitute the real port
VHOST_CONF="${VHOST_CONF/__PORT__/${PORT}}"

# remove every existing enabled site, then write ours as the only one
rm -f /etc/apache2/sites-enabled/*
printf '%s\n' "$VHOST_CONF" > /etc/apache2/sites-available/000-default.conf
printf '%s\n' "$VHOST_CONF" > /etc/apache2/sites-enabled/000-default.conf

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

# --- Force exactly ONE MPM (prefork) at RUNTIME -----------------------------
# A stale cached image layer can leave both mpm_event and mpm_prefork enabled,
# which makes Apache abort with "AH00534: More than one MPM loaded". Doing this
# here (not just in the Dockerfile) guarantees it runs on every boot, immune to
# Docker/Railway build-layer caching. Removing the event/worker symlinks from
# mods-enabled leaves prefork as the only enabled MPM.
rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.*
[ -e /etc/apache2/mods-enabled/mpm_prefork.load ] || a2enmod mpm_prefork || true

# --- MPM diagnostic: confirm exactly one MPM is now enabled ------------------
echo "===== MPM DIAGNOSTIC START ====="
echo "--- enabled MPM symlinks in mods-enabled ---"
ls -la /etc/apache2/mods-enabled/ 2>&1 | grep -i mpm || echo "(none)"
echo "===== MPM DIAGNOSTIC END ====="

exec apache2-foreground
