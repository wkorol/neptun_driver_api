# Use Apache with PHP 8.3
FROM php:8.3-apache-bookworm

ENV COMPOSER_ALLOW_SUPERUSER=1
ENV TZ=Europe/Warsaw

# Install necessary packages, tzdata, and PHP extensions
RUN apt-get -y update && apt-get -y upgrade && \
    apt-get -y install wget curl libpq-dev libzip-dev unzip libxml2-dev git bash cron supervisor tzdata && \
    ln -fs /usr/share/zoneinfo/Europe/Warsaw /etc/localtime && \
    dpkg-reconfigure -f noninteractive tzdata && \
    docker-php-ext-install pdo pdo_pgsql opcache zip soap intl

# Enable Apache modules
RUN a2enmod rewrite ssl socache_shmcb

# Install Composer
COPY --from=composer/composer:2.7.7-bin /composer /usr/bin/composer

# Set the working directory inside the container
WORKDIR /var/www/html

# Choose cron environment at build time
ARG CRON_ENV=prod

# Copy application files and Apache configuration
COPY . /var/www/html
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

# Configure Apache server name
RUN echo "ServerName neptun-api" >> /etc/apache2/conf-available/servername.conf && \
    a2enconf servername

# Install PHP dependencies
RUN composer install --no-interaction --optimize-autoloader --no-dev --no-scripts

# Set PHP timezone config
RUN echo "date.timezone=Europe/Warsaw" > /usr/local/etc/php/conf.d/timezone.ini

# Copy and set up cron
COPY cronjob.prod /etc/cron.d/symfony-cron.prod
COPY cronjob.dev /etc/cron.d/symfony-cron.dev
RUN if [ "$CRON_ENV" = "dev" ]; then cp /etc/cron.d/symfony-cron.dev /etc/cron.d/symfony-cron; \
    else cp /etc/cron.d/symfony-cron.prod /etc/cron.d/symfony-cron; fi && \
    chmod 0644 /etc/cron.d/symfony-cron && crontab /etc/cron.d/symfony-cron

# Copy the entrypoint script
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Import loop (production)
COPY docker/dev/import-orders-loop.sh /usr/local/bin/import-orders-loop.sh
RUN chmod +x /usr/local/bin/import-orders-loop.sh
ENV IMPORT_LOOP_URL=https://apineptun-ij5mx.ondigitalocean.app/api/proxy/import-orders/5
ENV IMPORT_LOOP_INTERVAL=3

# Set the custom entrypoint
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Start cron, import loop, and Apache
CMD ["bash", "-lc", "/usr/local/bin/import-orders-loop.sh & cron && apache2-foreground"]
