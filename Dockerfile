# Use Serversideup image optimized for Laravel
FROM serversideup/php:8.2-fpm-apache

# Switch to root for installation
USER root

# Set working directory
WORKDIR /var/www/html

# Install PostgreSQL driver
RUN apt-get update \
    && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo_pgsql pgsql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copy application files
COPY --chown=www-data:www-data . .

# Install Composer dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Create storage directories and set permissions
RUN mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Set Apache document root
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

# Switch back to www-data
USER www-data

# Expose port (Render uses PORT env)
EXPOSE 80

# Start command
CMD ["sh", "-c", "php artisan config:cache && php artisan route:cache && php artisan migrate --force && apache2-foreground"]
