# syntax=docker/dockerfile:1
FROM php:8.4-apache-bookworm AS php-base
RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip curl libpq-dev libzip-dev libicu-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev libsqlite3-dev libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo_pgsql pdo_sqlite bcmath intl zip gd pcntl exif mbstring opcache \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
WORKDIR /var/www/html
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY --chmod=755 docker/entrypoint.sh /usr/local/bin/hotel-entrypoint
RUN sed -i 's/\r$//' /usr/local/bin/hotel-entrypoint
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
ENTRYPOINT ["hotel-entrypoint"]
CMD ["apache2-foreground"]

FROM php-base AS dependencies
ENV COMPOSER_ALLOW_SUPERUSER=1
COPY composer.json composer.lock ./
RUN composer install --prefer-dist --no-interaction --no-progress --no-scripts --no-autoloader
COPY . .
RUN cp .env.example .env \
    && composer dump-autoload --no-scripts --optimize \
    && php artisan package:discover --ansi \
    && php artisan filament:upgrade

FROM node:22-bookworm-slim AS frontend
WORKDIR /build
ENV HUSKY=0
COPY package.json package-lock.json ./
COPY .husky/install.mjs .husky/install.mjs
RUN npm ci --no-audit --no-fund
COPY resources ./resources
COPY app ./app
COPY public ./public
COPY vite.config.ts tsconfig.json .gitignore ./
COPY --from=dependencies /var/www/html/vendor ./vendor
RUN npm run build

FROM dependencies AS tooling
COPY --from=frontend /usr/local/bin/node /usr/local/bin/node
COPY --from=frontend /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && ln -s /usr/local/lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx
COPY --from=frontend /build/node_modules ./node_modules
COPY --from=frontend /build/public/build ./public/build
COPY docker/php.ini /usr/local/etc/php/conf.d/hotel.ini
ENV HUSKY=0

FROM dependencies AS production-dependencies
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts --optimize-autoloader \
    && rm -f bootstrap/cache/packages.php bootstrap/cache/services.php \
    && php artisan package:discover --ansi

FROM php-base AS runtime
COPY --from=production-dependencies /var/www/html /var/www/html
COPY --from=frontend /build/public/build /var/www/html/public/build
RUN chown -R www-data:www-data storage bootstrap/cache
COPY docker/php.ini /usr/local/etc/php/conf.d/hotel.ini
EXPOSE 80

FROM php-base AS production
ARG APP_REVISION=development
LABEL org.opencontainers.image.title="Hotel Bugambilia" \
      org.opencontainers.image.source="https://github.com/juanjose23/hotel-bugambilia" \
      org.opencontainers.image.revision="${APP_REVISION}"
ENV APP_ENV=production APP_DEBUG=false LOG_CHANNEL=stderr
COPY --from=production-dependencies /var/www/html/app ./app
COPY --from=production-dependencies /var/www/html/bootstrap ./bootstrap
COPY --from=production-dependencies /var/www/html/config ./config
COPY --from=production-dependencies /var/www/html/database ./database
COPY --from=production-dependencies /var/www/html/lang ./lang
COPY --from=production-dependencies /var/www/html/public ./public
COPY --from=production-dependencies /var/www/html/resources/views ./resources/views
COPY --from=production-dependencies /var/www/html/routes ./routes
COPY --from=production-dependencies /var/www/html/storage ./storage
COPY --from=production-dependencies /var/www/html/stubs ./stubs
COPY --from=production-dependencies /var/www/html/vendor ./vendor
COPY --from=production-dependencies /var/www/html/artisan /var/www/html/composer.json /var/www/html/composer.lock ./
COPY --from=frontend /build/public/build ./public/build
COPY docker/validate-production.php ./docker/validate-production.php
COPY docker/php.ini /usr/local/etc/php/conf.d/hotel.ini
COPY docker/php-production.ini /usr/local/etc/php/conf.d/production.ini
RUN rm -f /usr/local/bin/composer \
    && chown -R www-data:www-data storage bootstrap/cache \
    && ln -s /var/www/html/storage/app/public public/storage \
    && printf '%s\n' "$APP_REVISION" > public/health-release.txt
HEALTHCHECK --interval=15s --timeout=5s --start-period=60s --retries=5 \
    CMD curl --fail --silent http://127.0.0.1/up > /dev/null || exit 1
