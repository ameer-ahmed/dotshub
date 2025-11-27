#!/bin/bash

echo "Setting up Laravel with Docker (PHP 8.4)..."

# Copy environment file
if [ ! -f .env ]; then
    echo "Creating .env file from .env.docker..."
    cp .env.docker .env
else
    echo ".env file already exists, skipping..."
fi

# Build and start containers
echo "Building Docker containers..."
docker-compose up -d --build

# Wait for MySQL to be ready
echo "Waiting for MySQL to be ready..."
sleep 10

# Install Composer dependencies
echo "Installing Composer dependencies..."
docker-compose exec app composer install

# Generate application key
echo "Generating application key..."
docker-compose exec app php artisan key:generate

# Run migrations
echo "Running database migrations..."
docker-compose exec app php artisan migrate --force

# Clear and cache config
echo "Optimizing application..."
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan view:clear

# Set permissions
echo "Setting permissions..."
docker-compose exec app chown -R www-data:www-data /var/www/html/storage
docker-compose exec app chown -R www-data:www-data /var/www/html/bootstrap/cache
docker-compose exec app chmod -R 775 /var/www/html/storage
docker-compose exec app chmod -R 775 /var/www/html/bootstrap/cache

echo ""
echo "Setup complete!"
echo "Your Laravel application is now running at: http://localhost:8000"
echo ""
echo "Useful commands:"
echo "  docker-compose up -d        - Start containers"
echo "  docker-compose down         - Stop containers"
echo "  docker-compose logs -f      - View logs"
echo "  docker-compose exec app bash - Access app container"
echo ""
