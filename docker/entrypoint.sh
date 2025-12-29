#!/bin/bash
set -e

# Ensure storage and cache directories exist and are writable
mkdir -p storage/app/public storage/framework/{sessions,views,cache} storage/logs bootstrap/cache
chmod -R 777 storage bootstrap/cache 2>/dev/null || true

# Start supervisord as appuser
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
