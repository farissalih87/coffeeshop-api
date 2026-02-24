#!/bin/bash

echo "Running migrations..."
php artisan storage:link --force
php artisan migrate --seed --force

echo "Clearing cache..."
php artisan config:clear
php artisan cache:clear

echo "Starting Laravel server..."
php artisan serve --host=0.0.0.0 --port=10000


