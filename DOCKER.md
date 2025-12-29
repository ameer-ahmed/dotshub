# Docker Setup for Gateway Service (Laravel PHP 8.4)

This is the Gateway service for the V6 microservices architecture. It's a Laravel application running on PHP 8.4 with Nginx, and Redis.

## Prerequisites

- Docker
- Docker Compose V2

## Quick Start

### Option 1: Automated Setup (Recommended)

From the **gateway** directory, run the setup script:

```bash
cd gateway
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

From the **gateway** directory:

1. **Copy environment file:**
   ```bash
   cp .env.docker .env
   ```

2. **Build and start containers:**
   ```bash
   docker compose up -d --build
   ```

3. **Install dependencies:**
   ```bash
   docker compose exec app composer install
   ```

4. **Generate application key:**
   ```bash
   docker compose exec app php artisan key:generate
   ```

5. **Run migrations:**
   ```bash
   docker compose exec app php artisan migrate
   ```

## Important Notes

- **Infrastructure Required**: The infrastructure services (MySQL, Kafka, Zookeeper, Redis) must be running first.
- **Start from Root**: For first-time setup, it's recommended to start all services from the root directory using `docker compose up -d`
- **Database Connection**: This service connects to the shared MySQL database on port 3307 (container name: `db`)
- **Kafka Connection**: This service connects to Kafka on port 9092 (container name: `kafka`)
- **Redis Connection**: This service connects to the shared Redis on port 6379 (container name: `redis`)

## Accessing the Application

When running from root (recommended):
- **Web Application:** http://localhost:8000
- **phpMyAdmin:** http://localhost:8080 (for database management)
- **Kafka UI:** http://localhost:8081 (for Kafka monitoring)
- **Redis:** localhost:6380 (shared infrastructure service, from host)

## Docker Services

The docker-compose setup includes:

- **app:** PHP 8.4-FPM with all required extensions
- **webserver:** Nginx web server

**Note**: Database (MySQL), Kafka, and Redis are provided by the infrastructure service.

## Common Commands

**All commands should be run from the `gateway` directory:**

### Container Management
```bash
# Start containers
docker compose up -d

# Stop containers
docker compose down

# Restart containers
docker compose restart

# View logs
docker compose logs -f

# View specific service logs
docker compose logs -f app
docker compose logs -f webserver

# Check running services
docker compose ps
```

### Access Containers
```bash
# Access app container
docker compose exec app bash

# Access database (from root or if infrastructure is running)
docker compose exec db mysql -u root -p
# Password: secret
```

### Laravel Commands
```bash
# Run artisan commands
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear
docker compose exec app php artisan route:clear
docker compose exec app php artisan view:clear

# Run Composer
docker compose exec app composer install
docker compose exec app composer update
docker compose exec app composer require package-name

# Run tests
docker compose exec app php artisan test
docker compose exec app ./vendor/bin/phpunit
```

### Database Management
```bash
# Create migration
docker compose exec app php artisan make:migration create_table_name

# Run migrations
docker compose exec app php artisan migrate

# Rollback migrations
docker compose exec app php artisan migrate:rollback

# Fresh migration (WARNING: drops all tables)
docker compose exec app php artisan migrate:fresh

# Seed database
docker compose exec app php artisan db:seed
```

### Build Assets
```bash
# Install NPM dependencies
docker compose exec app npm install

# Build assets for production
docker compose exec app npm run build

# Build assets for development
docker compose exec app npm run dev
```

## Environment Variables

Key environment variables for Docker (in .env):

```env
APP_NAME=Gateway
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=db                    # MySQL service name from infrastructure
DB_PORT=3306
DB_DATABASE=v6
DB_USERNAME=root
DB_PASSWORD=secret

REDIS_HOST=redis              # Redis service name from infrastructure (shared)
REDIS_PORT=6379

KAFKA_BROKERS=kafka:9092      # Kafka service name from infrastructure

CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

## Troubleshooting

### Permission Issues
```bash
docker compose exec app chown -R www-data:www-data /var/www/html/storage
docker compose exec app chown -R www-data:www-data /var/www/html/bootstrap/cache
docker compose exec app chmod -R 775 /var/www/html/storage
docker compose exec app chmod -R 775 /var/www/html/bootstrap/cache
```

### Database Connection Issues
```bash
# Ensure infrastructure is running
cd ../
docker compose ps

# Check if database service is running
docker compose logs db

# Verify database credentials in .env match infrastructure settings:
# DB_HOST=db
# DB_PORT=3306
# DB_DATABASE=v6
# DB_USERNAME=root
# DB_PASSWORD=secret
```

### Kafka Connection Issues
```bash
# Check if Kafka is running
cd ../
docker compose ps

# Check Kafka logs
docker compose logs kafka

# Verify Kafka connection in .env:
# KAFKA_BROKERS=kafka:9092
```

### Clear All Caches
```bash
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear
docker compose exec app php artisan route:clear
docker compose exec app php artisan view:clear
docker compose exec app composer dump-autoload
```

### Rebuild Containers
```bash
# Stop and remove containers
docker compose down

# Rebuild and start
docker compose up -d --build
```

### Reset Everything (WARNING: Deletes data)
```bash
# From root directory to reset all services
cd ../
docker compose down -v

# Rebuild all services
docker compose up -d --build

# Setup gateway again
cd gateway
./docker-setup.sh
```

## Integration with Infrastructure

This service depends on the infrastructure layer for:
- **MySQL Database** (shared)
- **Redis** (shared cache and sessions)
- **Kafka** (for inter-service messaging)
- **Zookeeper** (for Kafka)

The services are connected via the `v6-network` Docker bridge network.

## Production Considerations

For production deployment:

1. Update Dockerfile to remove development dependencies
2. Set `APP_ENV=production` and `APP_DEBUG=false`
3. Use secrets management for sensitive data
4. Configure proper SSL/TLS certificates
5. Set up proper backup strategy for database
6. Adjust PHP-FPM and Nginx configuration for performance
7. Enable OPcache optimization (already configured in docker/php/local.ini)
8. Configure Redis with proper persistence and eviction policies
9. Configure Kafka with proper replication and retention

## File Structure

```
gateway/
├── Dockerfile                      # PHP 8.4-FPM image
├── docker-compose.yml              # Gateway services configuration
├── docker-setup.sh                 # Automated setup script
├── .env.docker                     # Docker environment template
├── .env                           # Active environment (not in git)
├── .dockerignore                   # Docker build exclusions
├── app/                           # Laravel application code
├── config/                        # Laravel configuration
├── routes/                        # Application routes
├── database/                      # Migrations and seeders
└── docker/
    ├── nginx/
    │   └── conf.d/
    │       └── default.conf        # Nginx configuration
    └── php/
        └── local.ini               # PHP configuration
```

## Network Architecture

```
v6-network (Docker Bridge)
├── Infrastructure Layer
│   ├── kafka (9092)
│   ├── zookeeper (2181)
│   ├── db (3306)
│   ├── redis (6379)
│   ├── phpmyadmin (80)
│   └── kafka-ui (8080)
└── Gateway Service
    ├── app (PHP 8.4-FPM)
    └── webserver (Nginx:80)
```

All containers can communicate using their service names as hostnames.
