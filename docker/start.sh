#!/bin/sh
set -e

echo "==> Caching config..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Running migrations..."
php artisan migrate --force

echo "==> Seeding data..."
# Check if jobs already seeded to avoid duplicates on redeploy
JOB_COUNT=$(php artisan tinker --execute="echo \App\Models\Job::count();" 2>/dev/null | tail -1)
if [ "$JOB_COUNT" = "0" ]; then
  echo "==> Fresh database detected, running all seeders..."
  php artisan db:seed --force
else
  echo "==> Data exists ($JOB_COUNT jobs), running safe seeders only..."
  php artisan db:seed --class=AdminSeeder --force
fi

echo "==> Starting services..."
exec supervisord -c /etc/supervisor/conf.d/supervisord.conf