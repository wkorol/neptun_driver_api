#!/bin/bash
# entrypoint.sh

# Set ownership of the var directory
chown -R www-data:www-data /var/www/html/var

# Run any additional startup commands here, then start Apache
exec "$@"
