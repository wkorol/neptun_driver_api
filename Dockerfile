# Use Apache with PHP 8.3
FROM php:8.3-apache-bookworm

ENV COMPOSER_ALLOW_SUPERUSER=1

# Install necessary packages and PHP extensions
RUN apt-get -y update && apt-get -y upgrade && apt-get -y install wget sqlite3 libsqlite3-dev git bash libpq-dev libzip-dev unzip libxml2-dev \
    && docker-php-ext-install pdo pdo_sqlite opcache zip soap intl

# Enable Apache modules
RUN a2enmod rewrite ssl socache_shmcb

# Install Composer
COPY --from=composer/composer:2.7.7-bin /composer /usr/bin/composer

# Set the working directory inside the container
WORKDIR /var/www/html

# Copy your application files into the container
COPY . /var/www/html

# Copy Apache configuration
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

RUN echo "ServerName neptun-api" >> /etc/apache2/conf-available/servername.conf && \
    a2enconf servername

# Install PHP dependencies
RUN composer install --no-interaction --optimize-autoloader --no-dev --no-scripts

# Create necessary directories and set permissions
RUN mkdir -p var/cache var/log config/jwt && \
    chown -R www-data:www-data var config/jwt && \
    chmod -R 775 var config/jwt

# Set permissions for /tmp
RUN chmod -R 1777 /tmp

# Switch to www-data to ensure the correct user permissions
USER www-data

# Generate JWT keys
RUN php bin/console lexik:jwt:generate-keypair --overwrite

# Clear cache
RUN php bin/console cache:clear

# Revert back to root to start Apache
USER root

# Start Apache in the foreground
CMD ["apache2-foreground"]
