#!/bin/bash

# Optimizing configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations (must be forced in production)
php artisan migrate --force

# Link storage directory for public access
php artisan storage:link

# Start Apache in FOREGROUND
apache2-foreground
