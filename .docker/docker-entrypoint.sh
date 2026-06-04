#!/bin/bash
set -e

chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

if [ ! -f .env ]; then
    cp .env.production .env
fi

if ! grep -q "^APP_KEY=base64:" .env; then
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

php artisan storage:link --force || true

php artisan optimize:clear || true

php artisan migrate --force || true

php artisan db:seed --force || true

exec apache2-foreground