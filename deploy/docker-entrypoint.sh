#!/bin/sh
# Entrypoint tuned for a single-instance Koyeb free-tier deploy.
# Migrations run on boot so no manual step is needed after first deploy.
set -e

cd /var/www/html

: "${PORT:=8000}"
envsubst '${PORT}' < /etc/nginx/conf.d/default.conf.template > /etc/nginx/conf.d/default.conf

mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data bootstrap/cache storage/logs
chown -R www-data:www-data storage bootstrap/cache

# Generate an app key if Koyeb env vars don't provide one (best-effort).
# Note: prefer setting APP_KEY in Koyeb env vars so it survives redeploys.
[ -f .env ] || touch .env
[ -z "${APP_KEY}" ] && php artisan key:generate --force > /dev/null 2>&1 || true

# Fail loudly and let the container restart/retry if the DB is unreachable.
php artisan migrate --force

# Seed demo data once: set APP_SEED=true for the first boot, then remove the
# env var on redeploys (the seeder wipes tickets/customers).
if [ "${APP_SEED}" = "true" ]; then
    php artisan db:seed --force
fi

php artisan storage:link --force > /dev/null 2>&1 || true
php artisan config:cache > /dev/null 2>&1 || true

exec supervisord -c /etc/supervisor/supervisord.conf
