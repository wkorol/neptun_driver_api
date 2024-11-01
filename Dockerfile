# Use Apache with PHP 8.3
FROM php:8.3-apache-bookworm

ENV COMPOSER_ALLOW_SUPERUSER=1

# Install necessary packages and PHP extensions
RUN apt-get -y update && apt-get -y upgrade && apt-get -y install  wget sqlite3 libsqlite3-dev git bash libpq-dev libzip-dev unzip libxml2-dev \
    && docker-php-ext-install pdo pdo_sqlite opcache zip soap intl

# Enable Apache modules
RUN a2enmod rewrite ssl socache_shmcb

# Install Composer
COPY --from=composer/composer:2.7.7-bin /composer /usr/bin/composer

# Set the working directory inside the container
WORKDIR /var/www/html

# Copy your application files into the container
COPY . /var/www/html

COPY 000-default.conf /etc/apache2/sites-available/000-default.conf
#
RUN echo "ServerName neptun-api" >> /etc/apache2/conf-available/servername.conf && \
    a2enconf servername

RUN composer install --no-interaction --optimize-autoloader --no-dev --no-scripts

RUN mkdir -p var/cache var/log && \
    chown -R www-data:www-data var

# Start Apache in the foreground
CMD ["apache2-foreground"]
