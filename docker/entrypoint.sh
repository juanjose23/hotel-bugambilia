#!/bin/sh
set -eu
mkdir -p storage/app/public storage/app/private storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
if [ "$(id -u)" = "0" ]; then
    chown -R www-data:www-data storage bootstrap/cache
fi
if [ "${APP_ENV:-local}" = "production" ]; then
    php docker/validate-production.php
    php artisan config:cache --no-interaction
    php artisan event:cache --no-interaction
    php artisan route:cache --no-interaction
    php artisan view:cache --no-interaction
fi
exec docker-php-entrypoint "$@"
