# Use the official PHP image with Apache
FROM php:8.2-apache

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
<<<<<<< HEAD
    libzip-dev \
    zip \
    unzip \
    git \
    default-mysql-client \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Install additional PHP extensions
RUN docker-php-ext-configure gd \
    && docker-php-ext-install -j$(nproc) gd
=======
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd
>>>>>>> c9b2c661bcbb73e4f6c89002e1786c3996b58132

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html

<<<<<<< HEAD
# Copy custom Apache config
RUN echo '<Directory /var/www/html/public>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/storage
=======
# Copy custom Apache config for Laravel (if needed)
# COPY ./docker/apache/vhost.conf /etc/apache2/sites-available/000-default.conf

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html
>>>>>>> c9b2c661bcbb73e4f6c89002e1786c3996b58132

# Install Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

<<<<<<< HEAD
# Set Composer environment variables
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_NO_INTERACTION=1

# Copy composer files first
COPY composer.json composer.lock ./

# Update composer and install dependencies
RUN composer update --no-scripts --no-autoloader \
    && composer require myfatoorah/laravel-package \
    && composer install --no-interaction --no-scripts --prefer-dist \
    && composer dump-autoload

# Copy the rest of the application
COPY . .

# Generate application key and optimize
RUN php artisan key:generate --force \
    && php artisan cache:clear \
    && php artisan config:clear \
    && php artisan view:clear \
    && php artisan route:clear

=======
>>>>>>> c9b2c661bcbb73e4f6c89002e1786c3996b58132
# Expose port 80
EXPOSE 80

# Start Apache in foreground
CMD ["apache2-foreground"]
