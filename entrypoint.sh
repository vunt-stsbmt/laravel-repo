#!/usr/bin/env sh
set -e

if [ ! -f /var/www/.env ] && [ -f /var/www/.env.example ]; then
  cp /var/www/.env.example /var/www/.env
fi

mkdir -p /var/www/storage /var/www/bootstrap/cache
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R ug+rwx /var/www/storage /var/www/bootstrap/cache

if [ ! -d /var/www/vendor ]; then
  if command -v composer >/dev/null 2>&1; then
    composer install --no-interaction --prefer-dist --optimize-autoloader
  else
    echo "composer not found; skip vendor install" >&2
  fi
fi

if [ -z "${APP_KEY}" ]; then
  if grep -q "^APP_KEY=" /var/www/.env; then
    php /var/www/artisan key:generate --force
  fi
fi

if [ "${RUN_MIGRATIONS}" = "true" ]; then
  php /var/www/artisan migrate --force
fi

if [ "${APP_ENV}" = "production" ]; then
  php /var/www/artisan config:cache || true
  php /var/www/artisan route:cache || true
  php /var/www/artisan view:cache || true
fi

exec "$@"
