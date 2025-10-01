#!/usr/bin/env bash
set -e  # fail fast

echo "Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --working-dir=/var/www/html



echo "Building frontend..."
npm install --prefix /var/www/html
npm run build --prefix /var/www/html

echo "Build script finished."