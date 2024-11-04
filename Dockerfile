# Use Apache with PHP 8.3
FROM php:8.3-apache-bookworm

ENV COMPOSER_ALLOW_SUPERUSER=1

# Install necessary packages and PHP extensions
RUN apt-get -y update && apt-get -y upgrade && apt-get -y install wget libpq-dev libzip-dev unzip libxml2-dev git bash \
    && docker-php-ext-install pdo pdo_mysql opcache zip soap intl

# Enable Apache modules
RUN a2enmod rewrite ssl socache_shmcb

# Install Composer
COPY --from=composer/composer:2.7.7-bin /composer /usr/bin/composer

# Set the working directory inside the container
WORKDIR /var/www/html

# Copy application files and Apache configuration
COPY . /var/www/html
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

# Configure Apache server name
RUN echo "ServerName neptun-api" >> /etc/apache2/conf-available/servername.conf && \
    a2enconf servername

# Install PHP dependencies
RUN composer install --no-interaction --optimize-autoloader --no-dev --no-scripts

# Create necessary directories and set permissions during build
RUN mkdir -p /var/www/html/var /var/www/html/config/jwt /var/www/html/var/log && \
    chown -R www-data:www-data /var/www/html/var /var/www/html/config/jwt && \
    chmod -R 770 /var/www/html/var /var/www/html/config/jwt

# Copy and set permissions for entrypoint script
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Set entrypoint
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Start Apache after entrypoint runs
CMD ["apache2-foreground"]
