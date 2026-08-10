# ---------- Frontend assets ----------
FROM node:22-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --ignore-scripts
COPY . .
RUN npm run build

# ---------- PHP dependencies ----------
FROM composer:2 AS vendor
WORKDIR /app
COPY . .
RUN composer install --no-dev --no-interaction --no-progress --prefer-dist --no-scripts --optimize-autoloader \
    && composer dump-autoload --no-dev --optimize

# ---------- Runtime ----------
FROM php:8.3-fpm-alpine AS app

RUN apk add --no-cache nginx supervisor bash gettext postgresql-dev \
    && docker-php-ext-install pdo_pgsql opcache bcmath \
    && docker-php-ext-enable pdo_pgsql opcache bcmath \
    && rm -rf /var/cache/apk/*

# Koyeb free instance tuning (512 MB RAM): on-demand workers + opcache.
COPY deploy/php-fpm/zz-koyeb.conf /usr/local/etc/php-fpm.d/zz-koyeb.conf
COPY deploy/php-fpm/zz-koyeb.ini  /usr/local/etc/php/conf.d/zz-koyeb.ini

WORKDIR /var/www/html

COPY --from=vendor /app/vendor ./vendor
COPY --from=vendor /app/composer.json ./composer.json
COPY --from=vendor /app/composer.lock ./composer.lock

COPY --from=frontend /app/public/build ./public/build

COPY . .

RUN mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data bootstrap/cache storage/logs \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rw storage bootstrap/cache

COPY deploy/nginx/default.conf.template /etc/nginx/conf.d/default.conf.template
COPY deploy/supervisor/supervisord.conf /etc/supervisor/supervisord.conf
COPY deploy/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Fixed value: Render sets $PORT only at runtime, so a variable EXPOSE would
# fail the build. The entrypoint substitutes the real $PORT into nginx config.
EXPOSE 8000

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
