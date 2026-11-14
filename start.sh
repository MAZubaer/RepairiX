#!/bin/bash

# Optimizing configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations (must be forced in production)
php artisan migrate --force

# Link storage directory for public access
php artisan storage:link --force

# Start the Laravel queue worker in the background, restarting it whenever it exits
# (crash, memory limit, or the hourly --max-time recycle) so queued emails keep sending.
(
  while true; do
    php artisan queue:work --tries=3 --max-time=3600
    echo "Queue worker exited with status $?; restarting in 5 seconds..." >&2
    sleep 5
  done
) &

# Start Apache in FOREGROUND
apache2-foreground
