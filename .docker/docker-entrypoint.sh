#!/bin/bash
set -e

chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

ENV_FILE=".env"
if [ -n "$APP_ENV" ]; then
    ENV_FILE=".env.${APP_ENV}"
fi

if [ ! -f "$ENV_FILE" ]; then
    if [ -f ".env.production" ]; then
        cp .env.production "$ENV_FILE"
    else
        cp .env.example "$ENV_FILE"
    fi
fi

if ! grep -q "^APP_KEY=base64:" "$ENV_FILE" 2>/dev/null && ! grep -q "^APP_KEY=" "$ENV_FILE" 2>/dev/null; then
    echo "APP_KEY=" >> "$ENV_FILE"
fi

if ! grep -q "^APP_KEY=base64:" "$ENV_FILE"; then
    php artisan key:generate --force
fi

echo "Waiting for MySQL..."

until mysqladmin ping \
    -h"${DB_HOST}" \
    -u"${DB_USERNAME}" \
    -p"${DB_PASSWORD}" \
    --silent \
    --skip-ssl
do
    sleep 2
done

echo "MySQL ready."

php artisan optimize:clear || true

php artisan migrate --force || true

php artisan db:seed --force || true

exec apache2-foreground