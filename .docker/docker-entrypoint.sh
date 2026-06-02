#!/bin/bash
set -e

chown -R www-data:www-data storage bootstrap/cache public
chmod -R 775 storage bootstrap/cache

if [ ! -f .env ]; then
    cp .env.docker .env
    php artisan key:generate --force
fi

echo "Waiting for MySQL..."
until php -r "new PDO('mysql:host=${DB_HOST:-db};port=${DB_PORT:-3306}', '${DB_USERNAME:-root}', '${DB_PASSWORD:-laravel}');" 2>/dev/null; do
    sleep 2
done
echo "MySQL ready."

php artisan optimize:clear 2>/dev/null || true
php artisan migrate --force
php artisan db:seed --force

exec apache2-foreground
