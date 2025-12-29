# Gateway Service - V6 Microservices

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

This is the **Gateway Service** for the V6 microservices architecture. It serves as the API Gateway built with Laravel (PHP 8.4), providing a unified entry point for client applications.

## Architecture Overview

The Gateway service is part of a larger microservices ecosystem:

```
V6 Microservices
├── Infrastructure (Kafka, MySQL, Zookeeper)
└── Gateway (This Service) - Laravel API Gateway
```

## Features

- **RESTful API Gateway** - Unified API endpoint for client applications
- **Kafka Integration** - Event-driven architecture for inter-service communication
- **Shared Infrastructure** - Connects to shared MySQL, Redis, and Kafka
- **Docker Support** - Fully containerized with Docker Compose

## Quick Start

### Prerequisites

- Docker and Docker Compose V2
- The infrastructure services must be running

### Setup

1. **Start from root** (recommended for first-time setup):
   ```bash
   cd ..
   docker compose up -d
   cd gateway
   ```

2. **Or start gateway services only**:
   ```bash
   docker compose up -d
   ```

3. **Run setup script** (first time only):
   ```bash
   ./docker-setup.sh
   ```

   Or manually:
   ```bash
   docker compose exec gateway composer install
   docker compose exec gateway php artisan key:generate
   docker compose exec gateway php artisan migrate
   ```

4. **Access the application**:
   - API: http://localhost:8000

## Documentation

- **[DOCKER.md](DOCKER.md)** - Detailed Docker setup and commands
- **[V6-README.md](../V6-README.md)** - Overall V6 architecture documentation

## Development

### Common Commands

```bash
# Run migrations
docker compose exec gateway php artisan migrate

# Create a new migration
docker compose exec gateway php artisan make:migration create_users_table

# Run tests
docker compose exec gateway php artisan test

# Clear cache
docker compose exec gateway php artisan cache:clear

# Access container shell
docker compose exec gateway bash

# View logs
docker compose logs -f gateway
```

### Running Tests

```bash
docker compose exec gateway php artisan test
```

### Code Quality

```bash
# Run PHP CodeSniffer
docker compose exec gateway ./vendor/bin/phpcs

# Run PHP Stan
docker compose exec gateway ./vendor/bin/phpstan analyse
```

## Technology Stack

- **PHP 8.4** - Modern PHP with latest features
- **Laravel 11.x** - Web application framework
- **MySQL 8.0** - Shared relational database (from infrastructure)
- **Redis** - Shared in-memory data store (from infrastructure)
- **Kafka** - Distributed event streaming platform (from infrastructure)
- **Nginx** - High-performance web server
- **Docker** - Containerization platform

## Environment Configuration

Key environment variables (see `.env.docker` for template):

```env
# Application
APP_NAME=Gateway
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database (Shared)
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=v6
DB_USERNAME=root
DB_PASSWORD=secret

# Redis
REDIS_HOST=redis
REDIS_PORT=6379

# Kafka
KAFKA_BROKERS=kafka:9092
```

## API Documentation

API documentation will be available at:
- Swagger/OpenAPI: `/api/documentation` (when configured)

## Project Structure

```
gateway/
├── app/
│   ├── Http/
│   │   ├── Controllers/       # API Controllers
│   │   ├── Middleware/        # Custom Middleware
│   │   └── Services/          # Business Logic Services
│   ├── Models/               # Eloquent Models
│   └── Events/               # Event Classes
├── config/                   # Configuration files
├── database/
│   ├── migrations/          # Database migrations
│   └── seeders/             # Database seeders
├── routes/
│   ├── api.php             # API routes
│   └── web.php             # Web routes
├── tests/
│   ├── Feature/            # Feature tests
│   └── Unit/               # Unit tests
├── docker/                 # Docker configuration
│   ├── nginx/             # Nginx configs
│   └── php/               # PHP configs
├── .env.docker            # Environment template
├── docker-compose.yml     # Docker services
├── Dockerfile            # PHP image definition
└── DOCKER.md            # Docker documentation
```

## Integration Points

### Kafka Events

The gateway publishes and subscribes to Kafka events for:
- User authentication events
- Order processing events
- System notifications

### Database

Connects to shared MySQL database (`v6`) provided by infrastructure layer.

### Redis

Uses shared Redis from infrastructure for:
- Session management
- Cache storage
- Queue management

**Note**: Redis is shared across all microservices for consistent caching and session management.

## Troubleshooting

### Container Issues
```bash
# Check running containers
docker compose ps

# View logs
docker compose logs -f gateway

# Restart services
docker compose restart
```

### Database Connection
```bash
# Verify database is accessible
docker compose exec gateway php artisan migrate:status

# Check database connection
docker compose exec db mysql -u root -p
```

### Clear All Caches
```bash
docker compose exec gateway php artisan optimize:clear
```

For more troubleshooting, see [DOCKER.md](DOCKER.md).

## Contributing

1. Create a feature branch from `master`
2. Make your changes
3. Write tests for new functionality
4. Ensure all tests pass
5. Submit a pull request

## License

This project is part of the V6 microservices architecture.

---

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects.

For more information about Laravel, visit [laravel.com](https://laravel.com).

### Learning Laravel

- [Laravel Documentation](https://laravel.com/docs)
- [Laravel Bootcamp](https://bootcamp.laravel.com)
- [Laracasts](https://laracasts.com)

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
