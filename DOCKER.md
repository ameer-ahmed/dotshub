# Docker Setup for Laravel (PHP 8.4)

This Laravel application is fully dockerized with PHP 8.4, Nginx, MySQL 8.0, and Redis.

## Prerequisites

- Docker
- Docker Compose

## Quick Start

### Option 1: Automated Setup (Recommended)

Run the setup script:

```bash
./docker-setup.sh
```

This will:
- Create your .env file
- Build Docker containers
- Install dependencies
- Generate application key
- Run database migrations
- Set proper permissions

### Option 2: Manual Setup

1. **Copy environment file:**
   ```bash
   cp .env.docker .env
   ```

2. **Build and start containers:**
   ```bash
   docker-compose up -d --build
   ```

3. **Install dependencies:**
   ```bash
   docker-compose exec app composer install
   ```

4. **Generate application key:**
   ```bash
   docker-compose exec app php artisan key:generate
   ```

5. **Run migrations:**
   ```bash
   docker-compose exec app php artisan migrate
   ```

## Accessing the Application

- **Web Application:** http://localhost:8000
- **MySQL:** localhost:3306
  - Database: laravel
  - Username: laravel
  - Password: secret
- **Redis:** localhost:6379

## Docker Services

The docker-compose setup includes:

- **app:** PHP 8.4-FPM with all required extensions
- **webserver:** Nginx web server
- **db:** MySQL 8.0 database
- **redis:** Redis for caching and sessions
- **queue:** Laravel queue worker

## Common Commands

### Container Management
```bash
# Start containers
docker-compose up -d

# Stop containers
docker-compose down

# Restart containers
docker-compose restart

# View logs
docker-compose logs -f

# View specific service logs
docker-compose logs -f app
docker-compose logs -f webserver
```

### Access Containers
```bash
# Access app container
docker-compose exec app bash

# Access database
docker-compose exec db mysql -u laravel -p
```

### Laravel Commands
```bash
# Run artisan commands
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear

# Run Composer
docker-compose exec app composer install
docker-compose exec app composer update

# Run tests
docker-compose exec app php artisan test
```

### Database Management
```bash
# Create migration
docker-compose exec app php artisan make:migration create_table_name

# Run migrations
docker-compose exec app php artisan migrate

# Rollback migrations
docker-compose exec app php artisan migrate:rollback

# Fresh migration (WARNING: drops all tables)
docker-compose exec app php artisan migrate:fresh
```

### Queue Management
```bash
# View queue worker logs
docker-compose logs -f queue

# Restart queue worker
docker-compose restart queue
```

### Build Assets
```bash
# Install NPM dependencies
docker-compose exec app npm install

# Build assets for production
docker-compose exec app npm run build

# Build assets for development
docker-compose exec app npm run dev
```

## Environment Variables

Key environment variables for Docker (in .env):

```env
DB_HOST=db                    # MySQL service name
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=secret

REDIS_HOST=redis              # Redis service name
REDIS_PORT=6379

CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

## Troubleshooting

### Permission Issues
```bash
docker-compose exec app chown -R www-data:www-data /var/www/html/storage
docker-compose exec app chown -R www-data:www-data /var/www/html/bootstrap/cache
docker-compose exec app chmod -R 775 /var/www/html/storage
docker-compose exec app chmod -R 775 /var/www/html/bootstrap/cache
```

### Database Connection Issues
```bash
# Check if MySQL is running
docker-compose ps

# Check MySQL logs
docker-compose logs db

# Verify database credentials in .env match docker-compose.yml
```

### Clear All Caches
```bash
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear
```

### Rebuild Containers
```bash
# Stop and remove containers
docker-compose down

# Rebuild and start
docker-compose up -d --build
```

### Reset Everything (WARNING: Deletes data)
```bash
# Stop containers and remove volumes
docker-compose down -v

# Rebuild
docker-compose up -d --build

# Reinstall and migrate
./docker-setup.sh
```

## Production Considerations

For production deployment, consider:

1. Update Dockerfile to remove development dependencies
2. Set `APP_ENV=production` and `APP_DEBUG=false`
3. Use secrets management for sensitive data
4. Configure proper SSL/TLS certificates
5. Set up proper backup strategy for database volumes
6. Adjust PHP-FPM and Nginx configuration for performance
7. Enable OPcache optimization (already configured in docker/php/local.ini)

## File Structure

```
.
├── Dockerfile                      # PHP 8.4-FPM image
├── docker-compose.yml              # Services configuration
├── docker-setup.sh                 # Automated setup script
├── .env.docker                     # Docker environment template
├── .dockerignore                   # Docker build exclusions
└── docker/
    ├── nginx/
    │   └── conf.d/
    │       └── default.conf        # Nginx configuration
    └── php/
        └── local.ini               # PHP configuration
```
