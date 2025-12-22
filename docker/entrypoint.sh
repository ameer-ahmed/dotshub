#!/bin/bash
set -e

# Ensure storage and cache directories exist and are writable
mkdir -p storage/app/public storage/framework/{sessions,views,cache} storage/logs bootstrap/cache
chmod -R 777 storage bootstrap/cache

# Fix permissions for application files that need to be readable by web server
chmod -R 755 routes config app database resources
find routes config app database resources -type f -exec chmod 644 {} \;

# Start supervisord
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf