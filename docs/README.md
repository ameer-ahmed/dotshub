# DotsHub Documentation

Welcome to the DotsHub documentation. DotsHub is a multi-tenant SaaS platform built with Laravel 12, designed for managing multiple merchants with isolated databases, role-based access control, and multi-platform support.

## Table of Contents

1. [Overview](#overview)
2. [Technology Stack](#technology-stack)
3. [Key Features](#key-features)
4. [Documentation Structure](#documentation-structure)
5. [Quick Links](#quick-links)

## Overview

DotsHub is an enterprise-level Laravel application implementing a sophisticated multi-tenant architecture. Each merchant gets their own isolated database, custom domain, and complete role-permission system. The platform is designed to scale horizontally and support multiple client platforms (Web, Mobile).

## Technology Stack

- **Framework:** Laravel 12 (PHP 8.2+)
- **Multi-Tenancy:** Stancl/Tenancy v3.9
- **Authentication:** JWT (Tymon/JWT-Auth v2.2)
- **Authorization:** Laratrust v8.5 (Roles & Permissions)
- **Caching:** Redis with Predis
- **Frontend:** Tailwind CSS v4.0, Vite v6.2.4
- **Internationalization:** Spatie/Laravel-Translatable v6.11

## Key Features

### 1. Multi-Tenancy Architecture
- **Database-per-tenant** isolation for maximum security
- **Domain-based** tenant identification
- **Automatic** database creation and migration
- **Tenant-specific** caching and file storage

### 2. Dual Authentication System
- **Admin Authentication** - Central database users (platform administrators)
- **Merchant Authentication** - Tenant database users (merchant staff)
- **JWT-based** token authentication
- **Separate guards** for each user type

### 3. Advanced Authorization
- **Role-Based Access Control (RBAC)**
- **Dynamic permission assignment**
- **Ownership-based** access control
- **Public/Private** role distinction

### 4. Platform Abstraction
- **Version-based API** (currently v1)
- **Multi-platform support** (Web, Mobile-ready)
- **Dynamic service resolution** based on platform
- **Scalable architecture** for future platforms

### 5. Clean Architecture
- **Repository Pattern** for data access
- **Service Layer** for business logic
- **Request Validation** for data integrity
- **API Resources** for consistent responses

### 6. Internationalization
- **Multi-language support** (English, Arabic)
- **Translatable models** for content
- **Automatic locale detection** from headers

## Documentation Structure

This documentation is organized into the following sections:

### Getting Started
- [Installation & Setup](./GETTING_STARTED.md) - Step-by-step setup guide
- [Configuration](./GETTING_STARTED.md#configuration) - Environment and config setup

### Architecture
- [System Architecture](./ARCHITECTURE.md) - Overall system design
- [Multi-Tenancy Guide](./MULTI_TENANCY.md) - How tenancy works
- [Platform Abstraction](./ARCHITECTURE.md#platform-abstraction) - Platform system explained

### Core Components
- [Providers](./PROVIDERS.md) - Service providers documentation
- [Services](./SERVICES.md) - Business logic layer
- [Repositories](./REPOSITORIES.md) - Data access layer

### API Documentation
- [API Overview](./API.md) - API structure and conventions
- [Authentication APIs](./API.md#authentication) - Sign up, sign in, sign out
- [Role Management APIs](./API.md#role-management) - CRUD operations for roles

### Development
- [Custom Artisan Commands](./COMMANDS.md) - Code generation commands
- [make:platform-service](./COMMANDS.md#makeplatform-service) - Generate complete feature
- [make:repo](./COMMANDS.md#makerepo) - Generate repository pattern

## Quick Links

### Essential Documentation
- [How to create a new tenant](./MULTI_TENANCY.md#creating-a-tenant)
- [How to add a new service](./SERVICES.md#creating-a-service)
- [How to add a new repository](./REPOSITORIES.md#creating-a-repository)
- [How to use code generation commands](./COMMANDS.md)
- [How authentication works](./API.md#authentication)
- [How to add roles and permissions](./API.md#role-management)

### Code Examples
- [Admin Authentication Example](./API.md#admin-authentication-example)
- [Merchant Registration Example](./API.md#merchant-registration-example)
- [Role Creation Example](./API.md#role-creation-example)
- [Custom Service Example](./SERVICES.md#example-implementation)

## Project Structure

```
dotshub/
├── app/
│   ├── Console/Commands/       # Custom Artisan commands
│   ├── Enums/                  # Type-safe enumerations
│   ├── Http/
│   │   ├── Controllers/        # API controllers (versioned)
│   │   ├── Helpers/            # Helper classes
│   │   ├── Middleware/         # Custom middleware
│   │   ├── Requests/           # Form validation requests
│   │   ├── Resources/          # API response resources
│   │   └── Services/           # Business logic layer
│   ├── Models/                 # Eloquent models
│   ├── Providers/              # Service providers
│   ├── Repository/             # Repository pattern implementation
│   └── Traits/                 # Reusable traits
├── config/                     # Configuration files
├── database/
│   ├── migrations/             # Central database migrations
│   │   └── tenant/            # Tenant database migrations
│   └── seeders/               # Database seeders
├── docs/                       # Documentation (you are here)
├── lang/                       # Translations (en, ar)
├── routes/                     # Route definitions
└── tests/                      # Test suites
```

## System Requirements

- PHP 8.2 or higher
- Composer 2.x
- MySQL 8.0+ or MariaDB 10.3+
- Redis 6.0+
- Node.js 18+ (for frontend assets)

## Support

For questions or issues:
1. Check the relevant documentation section
2. Review code examples in the docs
3. Examine the existing implementations in the codebase

## License

[Your License Information]

## Next Steps

1. Read the [Getting Started Guide](./GETTING_STARTED.md) to set up your development environment
2. Understand the [Architecture](./ARCHITECTURE.md) to grasp the system design
3. Explore [API Documentation](./API.md) to learn about available endpoints
4. Review [Multi-Tenancy Guide](./MULTI_TENANCY.md) to understand tenant management

---

*Last Updated: 2025-11-25*