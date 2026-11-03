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
else
    if [ ! -L public/storage ] && [ ! -e public/storage ]; then
        php artisan storage:link || true
    fi
    if [ "${AUTO_MIGRATE:-false}" = "true" ]; then
        echo "[hotel-entrypoint] AUTO_MIGRATE activado. Verificando base de datos y ejecutando migraciones..."
        php artisan migrate --force --no-interaction

        if [ "${AUTO_SEED:-false}" = "true" ]; then
            USERS_COUNT=$(php -r '
                require "vendor/autoload.php";
                $app = require_once "bootstrap/app.php";
                $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
                $kernel->bootstrap();
                try {
                    echo \Illuminate\Support\Facades\DB::table("users")->count();
                } catch (\Throwable $e) {
                    echo "0";
                }
            ' 2>/dev/null || echo "0")

            if [ "$USERS_COUNT" = "0" ]; then
                echo "[hotel-entrypoint] AUTO_SEED activado y base vacía. Sembrando datos iniciales..."
                php artisan db:seed --force --no-interaction
                echo "[hotel-entrypoint] Datos iniciales sembrados con éxito (admin@hotel.com / password)."
            fi
        fi
    fi
fi
exec docker-php-entrypoint "$@"
