# Use Apache with PHP 8.3
FROM php:8.3-apache-bookworm

ENV COMPOSER_ALLOW_SUPERUSER=1

# Install necessary packages and PHP extensions
RUN apt-get -y update && apt-get -y upgrade && apt-get -y install wget libpq-dev libzip-dev unzip libxml2-dev git bash \
    && docker-php-ext-install pdo pdo_mysql opcache zip soap intl

# Install Cron
RUN apt-get -y install cron

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

RUN mkdir -p var/cache var/log config/jwt

# Set ownership for each directory separately to avoid permission issues
RUN chown -R www-data:www-data var/
# Set permissions for /tmp
RUN chmod -R 1777 /tmp

# Add your cron job
COPY cronjob /etc/cron.d/my-cron-job
RUN chmod 0644 /etc/cron.d/my-cron-job
RUN crontab /etc/cron.d/my-cron-job

# Combine all runtime commands in CMD
CMD php bin/console lexik:jwt:generate-keypair --overwrite && \
    php bin/console cache:clear && \
    php bin/console doctrine:migrations:migrate --no-interaction && \
    cron && apache2-foreground
