#!/bin/bash

# Exit on error
set -e

# Display help message
if [ "$1" == "-h" ] || [ "$1" == "--help" ]; then
  echo "Usage: ./docker-deploy.sh [options]"
  echo "Options:"
  echo "  -h, --help     Display this help message"
  echo "  -p, --prod     Deploy in production mode"
  echo "  -d, --dev      Deploy in development mode (default)"
  echo "  -r, --rebuild  Rebuild containers"
  exit 0
fi

# Default values
ENVIRONMENT="development"
REBUILD=false

# Parse arguments
while [[ $# -gt 0 ]]; do
  case $1 in
    -p|--prod)
      ENVIRONMENT="production"
      shift
      ;;
    -d|--dev)
      ENVIRONMENT="development"
      shift
      ;;
    -r|--rebuild)
      REBUILD=true
      shift
      ;;
    *)
      echo "Unknown option: $1"
      echo "Use --help for usage information"
      exit 1
      ;;
  esac
done

echo "Deploying in $ENVIRONMENT mode..."

# Copy environment file
if [ "$ENVIRONMENT" == "production" ]; then
  echo "Using production environment..."
  cp .env.docker .env
  # Set APP_ENV to production
  sed -i 's/APP_ENV=.*/APP_ENV=production/' .env
  sed -i 's/APP_DEBUG=.*/APP_DEBUG=false/' .env
else
  echo "Using development environment..."
  cp .env.docker .env
  # Set APP_ENV to local
  sed -i 's/APP_ENV=.*/APP_ENV=local/' .env
  sed -i 's/APP_DEBUG=.*/APP_DEBUG=true/' .env
fi

# Build or rebuild containers
if [ "$REBUILD" = true ]; then
  echo "Rebuilding containers..."
  sudo docker compose down
  sudo docker compose build --no-cache
  sudo docker compose up -d
else
  echo "Starting containers..."
  sudo docker compose up -d
fi

# Generate application key if not exists
if grep -q "APP_KEY=" .env && grep -q "APP_KEY=$" .env; then
  echo "Generating application key..."
  sudo docker compose exec app php artisan key:generate
fi

# Run migrations
echo "Running migrations..."
sudo docker compose exec app php artisan migrate --force

# Optimize for production
if [ "$ENVIRONMENT" == "production" ]; then
  echo "Optimizing for production..."
  sudo docker compose exec app php artisan config:cache
  sudo docker compose exec app php artisan route:cache
  sudo docker compose exec app php artisan view:cache
fi

echo "Deployment completed successfully!"
echo "The application is now available at http://localhost" 