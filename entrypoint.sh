#!/bin/bash
# entrypoint.sh

# Set ownership for var and log directories
chown -R www-data:www-data /var/www/html/var /var/www/html/var/log /var/www/html/var/cache 

# Ensure the JWT directory exists and set permissions
mkdir -p /var/www/html/config/jwt
chown -R www-data:www-data /var/www/html/config/jwt
chmod -R 770 /var/www/html/config/jwt

# Generate JWT keys if they don’t exist
if [ ! -f /var/www/html/config/jwt/private.pem ] || [ ! -f /var/www/html/config/jwt/public.pem ]; then
    php bin/console lexik:jwt:generate-keypair --overwrite
fi

# Run database migrations
php bin/console doctrine:migrations:migrate --no-interaction

# Start Apache
exec "$@"
