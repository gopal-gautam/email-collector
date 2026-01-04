#!/usr/bin/env bash
set -e

# Set file permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true

# Copy env if not exists (helpful for first-run)
if [ ! -f /var/www/html/.env ] && [ -f /var/www/html/.env.docker ]; then
  cp /var/www/html/.env.docker /var/www/html/.env
fi

cd /var/www/html

# Generate app key if missing
if grep -q "APP_KEY=" .env && [ "$(grep "^APP_KEY=" .env | cut -d= -f2)" = "" ]; then
  php artisan key:generate --ansi || true
fi

# Optionally run migrations (controlled by env var)
if [ "$RUN_MIGRATIONS" = "true" ]; then
  php artisan migrate --force || true
fi

# Start php-fpm (CMD from Dockerfile will run php-fpm)
exec "$@"
