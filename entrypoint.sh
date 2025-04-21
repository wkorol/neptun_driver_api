#!/bin/bash
# entrypoint.sh

set -e

# Create necessary directories
mkdir -p /var/www/html/var /var/www/html/var/cache /var/www/html/var/cache/prod /var/www/html/config/jwt /var/www/html/var/log

# Fix ownership only if current owner is not www-data
fix_permissions_if_needed() {
    local path=$1
    local owner=$(stat -c '%U' "$path" || echo unknown)

    if [ "$owner" != "www-data" ]; then
        echo "Fixing permissions for $path (owned by $owner)"
        chown -R www-data:www-data "$path"
        chmod -R 770 "$path"
    else
        echo "Permissions OK for $path"
    fi
}

fix_permissions_if_needed /var/www/html/var
fix_permissions_if_needed /var/www/html/var/cache
fix_permissions_if_needed /var/www/html/var/log
fix_permissions_if_needed /var/www/html/config/jwt

# Generate JWT keys if they don’t exist
if [ ! -f /var/www/html/config/jwt/private.pem ] || [ ! -f /var/www/html/config/jwt/public.pem ]; then
    echo "Generating JWT keys..."
    php bin/console lexik:jwt:generate-keypair --overwrite
fi

# Run database migrations
echo "Running Doctrine migrations..."
php bin/console doctrine:migrations:migrate --no-interaction

# Start Messenger consumer in background
php bin/console messenger:consume async --memory-limit=128M --no-interaction &

# Start Apache
exec "$@"
