#!/bin/bash

set -e

if [ ! -d "vendor" ]; then
    composer install --no-interaction --optimize-autoloader
fi

echo "Waiting for database..."
until php artisan db:monitor > /dev/null 2>&1; do
    echo "Database not ready yet..."
    sleep 2
done
echo "Database is ready!"

php artisan migrate --force

exec "$@"
