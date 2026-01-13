# Use official PHP 8.2 with Apache
FROM php:8.2-apache

# 1. Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 2. Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql pgsql mbstring exif pcntl bcmath gd zip intl opcache

# 3. Get Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 4. Set working directory
WORKDIR /var/www/html

# 5. Copy composer files first (for Better Layer Caching)
COPY composer.json composer.lock ./

# 6. Install dependencies WITHOUT scrips (Prevents errors if app isn't ready)
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-progress

# 7. Copy the rest of the application
COPY . .

# 8. Create production .env (Required for artisan commands)
RUN cp .env.example .env

# 9. Dump autoload (This runs the scripts now that everything is ready)
RUN composer dump-autoload --optimize

# 10. Set permissions
RUN chown -R www-data:www-data \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache \
    && chmod -R 775 \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache

# 11. Configure Apache
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN a2enmod rewrite

# 12. Handle PORT environment variable from Render
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# 13. Start command
CMD ["sh", "-c", "php artisan config:cache && php artisan route:cache && php artisan migrate --force && apache2-foreground"]
