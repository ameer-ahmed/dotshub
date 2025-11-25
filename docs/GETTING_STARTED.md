# Getting Started with DotsHub

This guide will help you set up DotsHub on your local development environment and get your first tenant running.

## Prerequisites

Before you begin, ensure you have the following installed:

- **PHP 8.2 or higher** with extensions:
  - OpenSSL
  - PDO
  - Mbstring
  - Tokenizer
  - XML
  - Ctype
  - JSON
  - BCMath
  - Redis
- **Composer 2.x**
- **MySQL 8.0+** or **MariaDB 10.3+**
- **Redis 6.0+**
- **Node.js 18+** and **npm**

## Installation

### Step 1: Clone the Repository

```bash
git clone <repository-url> dotshub
cd dotshub
```

### Step 2: Install PHP Dependencies

```bash
composer install
```

### Step 3: Install Node Dependencies

```bash
npm install
```

### Step 4: Environment Configuration

Copy the example environment file:

```bash
cp .env.example .env
```

Edit `.env` file with your configuration:

```env
APP_NAME=DotsHub
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

# Database Configuration (Central DB)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dotshub_central
DB_USERNAME=root
DB_PASSWORD=

# Redis Configuration
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# JWT Configuration
JWT_SECRET=
JWT_TTL=60

# Tenancy Configuration
TENANCY_DATABASE_PREFIX=tenant
TENANCY_DATABASE_AUTO_DELETE=true
```

### Step 5: Generate Application Key

```bash
php artisan key:generate
```

### Step 6: Generate JWT Secret

```bash
php artisan jwt:secret
```

### Step 7: Create Central Database

Create the central database in MySQL:

```sql
CREATE DATABASE dotshub_central CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Step 8: Run Central Migrations

```bash
php artisan migrate
```

This will create tables in the central database:
- admins
- merchants
- plans
- domains

### Step 9: (Optional) Seed Central Database

```bash
php artisan db:seed
```

### Step 10: Compile Frontend Assets

For development:
```bash
npm run dev
```

For production:
```bash
npm run build
```

### Step 11: Start the Development Server

```bash
php artisan serve
```

Your application should now be running at `http://localhost:8000`

## Configuration

### Multi-Tenancy Configuration

Edit `config/tenancy.php` if needed:

```php
return [
    'tenant_model' => \App\Models\Merchant::class,

    'id_generator' => \Stancl\Tenancy\UUIDGenerator::class,

    'database' => [
        'prefix' => env('TENANCY_DATABASE_PREFIX', 'tenant'),
        'suffix' => env('TENANCY_DATABASE_SUFFIX', ''),
    ],

    'central_domains' => [
        'localhost',
        '127.0.0.1',
        'dotshub.test',
    ],
];
```

### Authentication Guards

The application uses two authentication guards defined in `config/auth.php`:

```php
'guards' => [
    'admin' => [
        'driver' => 'jwt',
        'provider' => 'admins',
    ],
    'user' => [
        'driver' => 'jwt',
        'provider' => 'users',
    ],
],
```

### Supported Locales

Edit `config/locales.php` to add/remove supported languages:

```php
return [
    'supported_locales' => [
        'en' => 'English',
        'ar' => 'Arabic',
    ],
    'fallback_locale' => 'en',
];
```

## First Steps

### 1. Create an Admin Account

Use the API to create an admin account:

```bash
curl -X POST http://localhost:8000/api/v1/web/admin/auth/sign-up \
  -H "Content-Type: application/json" \
  -H "Accept-Language: en" \
  -d '{
    "name": "Admin User",
    "email": "admin@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

Response:
```json
{
  "status": true,
  "data": {
    "admin": {
      "id": 1,
      "name": "Admin User",
      "email": "admin@example.com"
    },
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
  },
  "message": "Admin registered successfully"
}
```

### 2. Create Your First Tenant (Merchant)

```bash
curl -X POST http://localhost:8000/api/v1/web/merchant/auth/sign-up \
  -H "Content-Type: application/json" \
  -H "Accept-Language: en" \
  -d '{
    "merchant": {
      "name": "My Store",
      "description": "My awesome store"
    },
    "domain": {
      "domain": "mystore.localhost"
    },
    "user": {
      "name": "Store Owner",
      "email": "owner@mystore.com",
      "password": "password123",
      "password_confirmation": "password123"
    }
  }'
```

This will:
1. Create a new merchant record
2. Create a new tenant database (e.g., `tenant_<uuid>`)
3. Run tenant migrations
4. Create the domain mapping
5. Create the first user with `merchant_admin` role
6. Seed default roles (merchant_admin, staff, etc.)

Response:
```json
{
  "status": true,
  "data": {
    "merchant": {
      "id": "9d4e2f3a-7c1b-4e8f-9a6d-2b5c8e7f1a3d",
      "name": "My Store",
      "description": "My awesome store"
    },
    "user": {
      "id": 1,
      "name": "Store Owner",
      "email": "owner@mystore.com"
    },
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
  },
  "message": "Merchant registered successfully"
}
```

### 3. Configure Local Domain

Add the tenant domain to your hosts file:

**Windows:** `C:\Windows\System32\drivers\etc\hosts`
**Linux/Mac:** `/etc/hosts`

```
127.0.0.1 mystore.localhost
```

### 4. Access Tenant API

Now you can access the tenant-specific APIs using the domain:

```bash
curl -X POST http://mystore.localhost:8000/api/v1/web/merchant/auth/sign-in \
  -H "Content-Type: application/json" \
  -d '{
    "email": "owner@mystore.com",
    "password": "password123"
  }'
```

## Development Workflow

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test --filter=AuthTest

# Run with coverage
php artisan test --coverage
```

### Code Formatting

The project uses Laravel Pint for code formatting:

```bash
# Check code style
./vendor/bin/pint --test

# Fix code style
./vendor/bin/pint
```

### Database Operations

#### Create New Migration (Central)

```bash
php artisan make:migration create_table_name --path=database/migrations
```

#### Create New Migration (Tenant)

```bash
php artisan make:migration create_table_name --path=database/migrations/tenant
```

#### Run Tenant Migrations

```bash
# Run for all tenants
php artisan tenants:migrate

# Run for specific tenant
php artisan tenants:migrate --tenants=tenant_id
```

#### Rollback Tenant Migrations

```bash
# Rollback for all tenants
php artisan tenants:rollback

# Rollback for specific tenant
php artisan tenants:rollback --tenants=tenant_id
```

### Custom Artisan Commands

#### Generate Platform Service

```bash
php artisan make:platform-service V1/Web/Admin/Product ProductService
```

This creates:
- Abstract service: `app/Http/Services/V1/Abstracts/Admin/Product/ProductAbstractService.php`
- Concrete service: `app/Http/Services/V1/Web/Admin/Product/ProductService.php`

#### Generate Repository

```bash
php artisan make:repo Product
```

This creates:
- Interface: `app/Repository/Contracts/ProductRepositoryInterface.php`
- Implementation: `app/Repository/Eloquent/ProductRepository.php`

Don't forget to register the repository in `RepositoryServiceProvider`:

```php
$this->app->bind(
    ProductRepositoryInterface::class,
    ProductRepository::class
);
```

### Monitoring Logs

Use Laravel Pail for real-time log monitoring:

```bash
php artisan pail
```

### Cache Management

```bash
# Clear all caches
php artisan cache:clear

# Clear config cache
php artisan config:clear

# Clear route cache
php artisan route:clear

# Clear view cache
php artisan view:clear
```

## Common Tasks

### Adding a New API Endpoint

1. **Create Form Request:**
```bash
php artisan make:request V1/Web/Merchant/Product/StoreProductRequest
```

2. **Create Resource:**
```bash
php artisan make:resource V1/Web/Merchant/ProductResource
```

3. **Generate Service:**
```bash
php artisan make:platform-service V1/Web/Merchant/Product ProductService
```

4. **Generate Repository (if needed):**
```bash
php artisan make:repo Product
```

5. **Create Controller:**
```bash
php artisan make:controller API/V1/Web/Merchant/Product/ProductController --api
```

6. **Define Routes:**
```php
// routes/api/v1/web/merchant/product.php
Route::middleware(['auth:user'])->group(function () {
    Route::get('products', [ProductController::class, 'index']);
    Route::post('products', [ProductController::class, 'store']);
    Route::get('products/{id}', [ProductController::class, 'show']);
    Route::put('products/{id}', [ProductController::class, 'update']);
    Route::delete('products/{id}', [ProductController::class, 'destroy']);
});
```

7. **Register Service in PlatformServiceProvider:**
```php
$this->app->bind(
    ProductAbstractService::class,
    "App\\Http\\Services\\V1\\{$this->platform}\\Merchant\\Product\\ProductService"
);
```

### Adding a New Role

Roles are managed per tenant. Use the Role Management API:

```bash
curl -X POST http://mystore.localhost:8000/api/v1/web/merchant/role \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "warehouse_manager",
    "display_name": "Warehouse Manager",
    "description": "Manages inventory and warehouse operations",
    "permissions": [1, 2, 3],
    "is_private": false,
    "is_editable": true
  }'
```

### Adding Translations

1. Add translation keys to language files:

**English:** `lang/en/messages.php`
```php
return [
    'welcome' => 'Welcome to DotsHub',
    'product_created' => 'Product created successfully',
];
```

**Arabic:** `lang/ar/messages.php`
```php
return [
    'welcome' => 'مرحبا بك في DotsHub',
    'product_created' => 'تم إنشاء المنتج بنجاح',
];
```

2. Use in code:
```php
return Responser::success($product, __('messages.product_created'));
```

3. Client sends `Accept-Language` header:
```bash
curl -H "Accept-Language: ar" http://api.example.com/endpoint
```

## Troubleshooting

### Issue: Tenant database not created

**Solution:**
Check tenant migrations ran successfully:
```bash
php artisan tenants:migrate
```

### Issue: JWT token invalid

**Solutions:**
1. Ensure JWT secret is set: `php artisan jwt:secret`
2. Check token expiration in `config/jwt.php`
3. Verify correct auth guard is used

### Issue: Domain not recognized

**Solutions:**
1. Check domain exists in `domains` table
2. Verify domain is added to hosts file
3. Check `central_domains` in `config/tenancy.php`

### Issue: Permission denied errors

**Solutions:**
1. Ensure user has correct role
2. Check role has required permissions
3. Verify middleware is applied to route

### Issue: Cache not clearing

**Solutions:**
```bash
php artisan cache:clear
php artisan config:clear
redis-cli FLUSHALL  # Clear Redis cache
```

## Next Steps

Now that you have DotsHub running:

1. [Understand the Architecture](./ARCHITECTURE.md)
2. [Learn about Multi-Tenancy](./MULTI_TENANCY.md)
3. [Explore the API](./API.md)
4. [Study Services](./SERVICES.md)
5. [Review Providers](./PROVIDERS.md)

## Additional Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Stancl/Tenancy Documentation](https://tenancyforlaravel.com/docs)
- [JWT-Auth Documentation](https://github.com/tymondesigns/jwt-auth)
- [Laratrust Documentation](https://laratrust.santigarcor.me/)

---

*Last Updated: 2025-11-25*