FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    && docker-php-ext-install pdo pdo_mysql zip mbstring exif pcntl

# Install Composer from Composer's official image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy the entire application at once
COPY . .

# Install Composer dependencies
RUN composer install --no-dev --optimize-autoloader

# Generate application key and optimize
RUN cp .env.example .env && \
    php artisan key:generate && \
    php artisan optimize

# Set correct permissions
RUN chown -R www-data:www-data . \
    && chmod -R 775 storage bootstrap/cache

# Run PHP-FPM
CMD ["php-fpm"]