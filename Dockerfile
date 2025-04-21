# Use Apache with PHP 8.3
FROM php:8.3-apache-bookworm

ENV COMPOSER_ALLOW_SUPERUSER=1
ENV TZ=Europe/Warsaw

# Install necessary packages, tzdata, and PHP extensions
RUN apt-get -y update && apt-get -y upgrade && \
    apt-get -y install wget libpq-dev libzip-dev unzip libxml2-dev git bash cron supervisor tzdata && \
    ln -fs /usr/share/zoneinfo/Europe/Warsaw /etc/localtime && \
    dpkg-reconfigure -f noninteractive tzdata && \
    docker-php-ext-install pdo pdo_mysql opcache zip soap intl

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

# Copy the crontab file

RUN echo "date.timezone=Europe/Warsaw" > /usr/local/etc/php/conf.d/timezone.ini

COPY cronjob /etc/cron.d/symfony-cron
RUN chmod 0644 /etc/cron.d/symfony-cron && crontab /etc/cron.d/symfony-cron

# Give execution rights on the cron job file
RUN chmod 0644 /etc/cron.d/symfony-cron

# Apply the cron job
RUN crontab /etc/cron.d/symfony-cron

# Copy the entrypoint script
COPY entrypoint.sh /usr/local/bin/entrypoint.sh

# Make the entrypoint script executable
RUN chmod +x /usr/local/bin/entrypoint.sh

# Set the custom entrypoint
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Start Supervisor, which will manage both Apache and cron
CMD cron && apache2-foreground
