#!/bin/bash
# entrypoint.sh

# Set ownership of the var directory to avoid permission issues
chown -R www-data:www-data /var/www/html/var

# Ensure JWT keys exist, generate if missing
if [ ! -f /var/www/html/config/jwt/private.pem ] || [ ! -f /var/www/html/config/jwt/public.pem ]; then
    php bin/console lexik:jwt:generate-keypair --overwrite
fi

# Run database migrations
php bin/console doctrine:migrations:migrate --no-interaction

# Start Apache
exec "$@"
