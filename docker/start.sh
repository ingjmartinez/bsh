#!/bin/sh
set -eu

mkdir -p /var/www/storage/app/public /var/www/storage/logs /var/www/storage/framework/cache /var/www/storage/framework/sessions /var/www/storage/framework/views
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
runuser -u www-data -- php artisan storage:link --no-interaction || true

if [ "${SCHEDULER_ENABLED:-true}" = "true" ]; then
    runuser -u www-data -- php artisan schedule:work --no-interaction &
    scheduler_pid=$!
    trap 'kill "$scheduler_pid" 2>/dev/null || true' EXIT TERM INT
fi

"$@" &
app_pid=$!
wait "$app_pid"
