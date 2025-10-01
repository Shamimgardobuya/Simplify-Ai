#!/bin/sh
set -e

echo "Running artisan caches..."
php artisan config:clear

echo "Running migrations"
php artisan migrate --force

php artisan cache:clear
php artisan route:clear
php artisan view:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan inspector:test


echo "Starting PHP-FPM..."
php-fpm -D

echo "Starting Nginx..."
nginx -g "daemon off;"