#!/bin/bash

# Exit on error but continue on migration errors
set +e

echo "Starting the TV Channel Monitoring System..."

# Kill any existing server process
echo "Stopping any existing server..."
pkill -f "php artisan serve" || true

# Copy environment file
echo "Setting up environment..."
cp .env.docker .env
sed -i 's/APP_ENV=.*/APP_ENV=local/' .env
sed -i 's/APP_DEBUG=.*/APP_DEBUG=true/' .env
sed -i 's/APP_URL=.*/APP_URL=http:\/\/localhost:8080/' .env

# Configure SQLite database and file-based services
echo "Configuring local environment..."
sed -i 's/DB_CONNECTION=.*/DB_CONNECTION=sqlite/' .env
sed -i 's/CACHE_STORE=.*/CACHE_STORE=file/' .env
sed -i 's/QUEUE_CONNECTION=.*/QUEUE_CONNECTION=sync/' .env
sed -i 's/SESSION_DRIVER=.*/SESSION_DRIVER=file/' .env
sed -i 's/BROADCAST_CONNECTION=.*/BROADCAST_CONNECTION=log/' .env

# Create SQLite database
mkdir -p database
echo "Preparing database..."
rm -f database/database.sqlite
touch database/database.sqlite
chmod 666 database/database.sqlite

# Generate application key
echo "Generating application key..."
php artisan key:generate --force

# Run migrations
echo "Setting up the database..."
php artisan migrate:fresh --force || echo "Migration completed with some warnings."

# Clear caches
echo "Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Start the application without Docker
echo "Starting the application..."
php artisan serve --host=0.0.0.0 --port=8080

echo "Application is running at http://localhost:8080"  