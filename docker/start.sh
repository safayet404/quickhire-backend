#!/bin/sh
set -e

echo "==> Caching config..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Running migrations..."
php artisan migrate --force

echo "==> Starting services..."
exec supervisord -c /etc/supervisor/conf.d/supervisord.conf
