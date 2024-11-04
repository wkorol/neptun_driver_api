#!/bin/bash
# entrypoint.sh

# Set ownership of the var directory
chown -R www-data:www-data /var/www/html/var

php bin/console lexik:jwt:generate-keypair --overwrite
php bin/console cache:clear
php bin/console doctrine:migrations:migrate --no-interaction

# Run any additional startup commands here, then start Apache
exec "$@"
