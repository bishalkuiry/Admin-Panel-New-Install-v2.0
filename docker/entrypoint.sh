#!/bin/bash
set -e

cd /var/www/html

if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
fi

if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# Only the primary "app" container warms caches; queue/scheduler share the same
# bind-mounted bootstrap/cache and would otherwise race on the same cache files.
if [ "$APP_ROLE" = "worker" ]; then
    exec "$@"
fi

php artisan storage:link >/dev/null 2>&1 || true
php artisan config:clear >/dev/null 2>&1
php artisan route:clear >/dev/null 2>&1
php artisan view:clear >/dev/null 2>&1
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"
