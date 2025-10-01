# Use the official PHP-FPM image
FROM php:8.2-fpm

# Install necessary packages for both Nginx and PHP
RUN apt-get update && apt-get install -y \
    nginx \
    git \
    unzip \
    nodejs npm \
    libzip-dev \
    libpq-dev \
    && docker-php-ext-install pdo_mysql zip

# Set the working directory for the application
WORKDIR /var/www/html

# Copy the application source code into the container
COPY . .

# Set permissions for the www-data user
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache


# Image and Laravel configuration variables
ENV APP_ENV production
ENV APP_DEBUG false
ENV LOG_CHANNEL stderr
ENV COMPOSER_ALLOW_SUPERUSER 1
ENV WEBROOT /var/www/html/public

RUN rm /etc/nginx/sites-enabled/default || true
# Copy Nginx config and build script
COPY ./nginx.conf /etc/nginx/conf.d/default.conf
COPY ./render-build.sh /usr/local/bin/render-build.sh

# Install Composer and run build script
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin --filename=composer
RUN chmod +x /usr/local/bin/render-build.sh \
    && /usr/local/bin/render-build.sh
    
RUN ls -la /var/www/html/public/index.php
# Expose port 8000 to the host machine
EXPOSE 8000

# Copy and set the entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Use the entrypoint script to start the services
CMD ["/usr/local/bin/docker-entrypoint.sh"]