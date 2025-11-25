# System Architecture

## Overview

DotsHub follows a **clean architecture** approach with clear separation of concerns across multiple layers. The system is built on top of Laravel's foundation with custom abstractions for multi-tenancy, platform support, and business logic organization.

## Architecture Layers

```
┌─────────────────────────────────────────────────────┐
│              API Layer (Routes)                      │
│  /api/v1/{platform}/{context}/{resource}            │
└─────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│         Middleware Layer                             │
│  Authentication, Authorization, Localization,        │
│  Tenancy Identification                              │
└─────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│         Controller Layer                             │
│  Request handling, Validation, Response formatting   │
└─────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│         Service Layer (Business Logic)               │
│  Platform-based services, Business rules             │
└─────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│         Repository Layer (Data Access)               │
│  Database operations, Query building                 │
└─────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│         Model Layer (Data Entities)                  │
│  Eloquent Models, Relationships, Scopes              │
└─────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│         Database Layer                               │
│  Central DB (Admins, Merchants, Plans)               │
│  Tenant DBs (Users, Roles, Permissions, Channels)    │
└─────────────────────────────────────────────────────┘
```

## Core Architectural Patterns

### 1. Repository Pattern

The Repository Pattern provides an abstraction layer between the business logic and data access logic.

**Benefits:**
- Decouples business logic from data access
- Makes code more testable
- Centralizes data access logic
- Easier to swap data sources

**Structure:**
```
Repository/
├── Contracts/
│   ├── RepositoryInterface.php          # Base interface
│   ├── MerchantRepositoryInterface.php  # Merchant-specific
│   └── ...
└── Eloquent/
    ├── Repository.php                    # Base implementation
    ├── MerchantRepository.php            # Merchant implementation
    └── ...
```

**Example:**
```php
// Interface
interface UserRepositoryInterface extends RepositoryInterface
{
    public function findByEmail(string $email): ?User;
}

// Implementation
class UserRepository extends Repository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }
}

// Service Provider Binding
$this->app->bind(UserRepositoryInterface::class, UserRepository::class);
```

### 2. Service Layer Pattern

Services encapsulate business logic and orchestrate operations across multiple repositories.

**Benefits:**
- Separates business logic from controllers
- Reusable across different contexts
- Easier to maintain and test
- Single responsibility principle

**Structure:**
```
Http/Services/
└── V1/
    ├── Abstracts/
    │   ├── PlatformService.php           # Base abstract class
    │   └── [Context]/[Feature]/
    │       └── ServiceAbstractService.php
    └── [Platform]/
        └── [Context]/[Feature]/
            └── Service.php
```

**Example:**
```php
// Abstract Service
abstract class AuthAbstractService extends PlatformService
{
    abstract public function signUp(array $data);
    abstract public function signIn(array $credentials);
    abstract public function signOut();
}

// Concrete Implementation
class AuthService extends AuthAbstractService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {}

    public function platform(): Platform
    {
        return Platform::WEB;
    }

    public function signUp(array $data)
    {
        // Business logic here
        return $this->userRepository->create($data);
    }
}
```

### 3. Platform Abstraction

Platform abstraction allows the same API to serve different client types (Web, Mobile) with different implementations.

**How it Works:**

1. **Route Definition:**
```php
// routes/api/v1/web/admin/auth.php
Route::post('sign-up', [AuthController::class, 'signUp']);
```

2. **Platform Detection:**
The `PlatformServiceProvider` detects the platform from the URL (`/api/v1/web/...` or `/api/v1/mobile/...`)

3. **Service Binding:**
```php
// PlatformServiceProvider.php
$this->app->bind(
    AuthAbstractService::class,
    "App\\Http\\Services\\V1\\{$this->platform}\\Admin\\Auth\\AuthService"
);
```

4. **Controller Usage:**
```php
class AuthController extends Controller
{
    public function __construct(
        protected AuthAbstractService $authService  // Automatically resolved
    ) {}

    public function signUp(SignUpRequest $request)
    {
        return $this->authService->signUp($request->validated());
    }
}
```

**Benefits:**
- Same controller works for all platforms
- Platform-specific logic isolated
- Easy to add new platforms
- Clean separation of concerns

### 4. Multi-Tenancy Architecture

DotsHub uses **database-per-tenant** multi-tenancy for maximum isolation and security.

**Components:**

1. **Tenant Identification:**
   - Via domain/subdomain
   - Middleware: `InitializeTenancyByDomain`

2. **Database Isolation:**
   - Each tenant gets separate database
   - Naming convention: `tenant{tenant_id}`
   - Automatic creation and migration

3. **Context Switching:**
   - Central context: Admin operations
   - Tenant context: Merchant operations
   - Automatic switching via middleware

**Flow:**
```
Request → Domain Detection → Tenant Lookup → Database Switch → Query Execution
```

See [Multi-Tenancy Guide](./MULTI_TENANCY.md) for detailed information.

## Directory Structure Explained

### app/Console/Commands/
Custom Artisan commands for development productivity.

**Commands:**
- `MakePlatformService` - Generates platform-based service structure
- `MakeRepo` - Generates repository interface and implementation

**Usage:**
```bash
php artisan make:platform-service V1/Web/Admin/Product ProductService
php artisan make:repo User
```

### app/Enums/
Type-safe enumeration classes.

**Available Enums:**
- `Platform` - WEB, MOBILE
- `UserStatus` - User account states
- `MerchantStatus` - PENDING, ACTIVE, INACTIVE, etc.

**Example:**
```php
use App\Enums\Platform;

if ($platform === Platform::WEB) {
    // Web-specific logic
}
```

### app/Http/Controllers/
API controllers organized by version and platform.

**Structure:**
```
Controllers/
└── API/
    └── V1/
        └── [Platform]/
            └── [Context]/
                └── [Feature]/
                    └── FeatureController.php
```

**Example:** `API/V1/Web/Admin/Auth/AuthController.php`

### app/Http/Helpers/
Utility classes for common operations.

**Responser Helper:**
```php
use App\Http\Helpers\Responser;

// Success response
return Responser::success($data, 'Operation successful', 201);

// Error response
return Responser::fail('Error message', 400);

// Custom response
return Responser::custom(true, $data, 'Custom message', 200);
```

### app/Http/Middleware/
Custom middleware for request processing.

**Localize Middleware:**
- Detects language from `Accept-Language` header
- Sets application locale automatically
- Falls back to default locale

**Usage:**
```php
// In routes
Route::middleware(['localize'])->group(function () {
    // Routes here
});
```

### app/Http/Requests/
Form Request classes for validation.

**Example:**
```php
class SignUpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins',
            'password' => 'required|min:8|confirmed',
        ];
    }
}
```

### app/Http/Resources/
API Resource classes for response transformation.

**Example:**
```php
class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
```

### app/Models/
Eloquent model classes.

**Central Models:** (app/Models/)
- Admin
- Merchant
- Plan

**Tenant Models:** (app/Models/Tenant/)
- User
- Role
- Permission
- Channel

### app/Providers/
Service providers for application bootstrapping.

See [Providers Documentation](./PROVIDERS.md) for detailed information.

### app/Repository/
Repository pattern implementation.

See [Repository Documentation](./REPOSITORIES.md) for detailed information.

### app/Traits/
Reusable trait classes for shared functionality.

## Authentication & Authorization Flow

### Admin Authentication Flow

```
1. Admin sends credentials to /api/v1/web/admin/auth/sign-in
2. Middleware: Localize
3. Controller validates request
4. AuthService verifies credentials against central DB
5. JWT token generated
6. Token returned to admin
7. Subsequent requests include token in Authorization header
8. auth:admin middleware validates token
```

### Merchant Authentication Flow

```
1. User accesses tenant domain (e.g., tenant1.example.com)
2. Tenancy middleware identifies tenant from domain
3. Database switches to tenant database
4. User sends credentials to /api/v1/web/merchant/auth/sign-in
5. AuthService verifies credentials against tenant DB
6. JWT token generated with tenant context
7. Token returned to user
8. Subsequent requests maintain tenant context
```

## Request/Response Lifecycle

### Typical Request Flow

```
┌─────────────────────────────────────────────────────┐
│ 1. HTTP Request                                      │
│    POST /api/v1/web/merchant/auth/sign-in           │
└─────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│ 2. Middleware Stack                                  │
│    - InitializeTenancyByDomain (detect tenant)      │
│    - Localize (set language)                        │
│    - PreventAccessFromCentralDomains                │
└─────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│ 3. Route Matching                                    │
│    Find controller and method                        │
└─────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│ 4. Form Request Validation                           │
│    Validate input data                               │
└─────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│ 5. Controller Method                                 │
│    Inject dependencies (services)                    │
└─────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│ 6. Service Layer                                     │
│    Execute business logic                            │
└─────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│ 7. Repository Layer                                  │
│    Perform database operations                       │
└─────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│ 8. Response Formatting                               │
│    API Resource transformation                       │
│    Responser helper                                  │
└─────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│ 9. HTTP Response                                     │
│    JSON response with status code                    │
└─────────────────────────────────────────────────────┘
```

## Database Architecture

### Central Database

Stores platform-wide data:
- Admins (platform administrators)
- Merchants (tenant information)
- Plans (subscription plans)
- Domains (tenant domain mappings)

### Tenant Databases

Each tenant has an isolated database containing:
- Users (tenant-specific users)
- Roles (tenant-specific roles)
- Permissions (tenant-specific permissions)
- Channels (tenant-specific channels)
- Other tenant-specific data

### Database Naming Convention

```
Central DB: configured in .env (DB_DATABASE)
Tenant DBs: tenant{merchant_id}
```

### Migrations

```
database/migrations/           # Central database migrations
database/migrations/tenant/    # Tenant database migrations (run for each tenant)
```

## Caching Strategy

### Redis-based Caching

The application uses Redis for caching with tenant isolation:

```php
// Tenant-specific cache
cache()->store('tenant')->put('key', 'value', 3600);

// Central cache
cache()->put('key', 'value', 3600);
```

### Cache Keys

Tenant cache keys are automatically prefixed with tenant ID to prevent collisions.

## Configuration Management

### Environment-based Configuration

```
.env                 # Main environment configuration
config/*.php         # Configuration files
```

### Key Configuration Files

- `config/tenancy.php` - Multi-tenancy settings
- `config/auth.php` - Authentication guards and providers
- `config/jwt.php` - JWT token configuration
- `config/laratrust.php` - Roles and permissions
- `config/locales.php` - Supported languages

## Error Handling

### Standardized Error Responses

All errors are formatted consistently using the Responser helper:

```php
try {
    // Operation
} catch (\Exception $e) {
    return Responser::fail($e->getMessage(), 500);
}
```

### Automatic Error Logging

The Responser helper automatically logs errors:
```php
Log::error($message, [
    'status' => $status,
    'trace' => debug_backtrace()
]);
```

## Security Considerations

### JWT Token Security
- Tokens expire after configured time
- Refresh token mechanism available
- Secure token storage recommended

### Database Isolation
- Each tenant has separate database
- No cross-tenant data leakage possible
- Tenant context enforced by middleware

### Input Validation
- All inputs validated via Form Requests
- Type-safe enums prevent invalid values
- SQL injection prevention via Eloquent ORM

### Authorization
- Role-based access control
- Permission checks before operations
- Ownership verification for sensitive operations

## Performance Optimization

### Query Optimization
- Eager loading to prevent N+1 queries
- Repository layer for query reusability
- Database indexing on foreign keys

### Caching Strategy
- Redis for high-performance caching
- Tenant-specific cache isolation
- Cache invalidation on data updates

### Asset Optimization
- Vite for frontend asset bundling
- CSS purging with Tailwind
- Lazy loading where appropriate

## Extensibility

### Adding New Platforms

1. Create directory structure:
```
app/Http/Services/V1/Mobile/
app/Http/Controllers/API/V1/Mobile/
routes/api/v1/mobile/
```

2. Add platform to enum:
```php
enum Platform: string
{
    case WEB = 'web';
    case MOBILE = 'mobile';
}
```

3. Create platform-specific implementations of abstract services

### Adding New Features

1. Create repository (if needed)
2. Create service (business logic)
3. Create controller
4. Create form requests (validation)
5. Create API resources (response formatting)
6. Define routes
7. Write tests

## Best Practices

1. **Always use type hints** for better IDE support and type safety
2. **Use dependency injection** instead of facades where possible
3. **Keep controllers thin** - business logic belongs in services
4. **Use form requests** for validation, not controller validation
5. **Follow PSR standards** for code formatting
6. **Write tests** for all business logic
7. **Use Eloquent ORM** features instead of raw queries
8. **Leverage caching** for expensive operations
9. **Document complex logic** with comments
10. **Use enums** instead of string/integer constants

## Next Steps

- [Learn about Providers](./PROVIDERS.md)
- [Understand Services](./SERVICES.md)
- [Explore Repositories](./REPOSITORIES.md)
- [Review API Documentation](./API.md)
- [Deep dive into Multi-Tenancy](./MULTI_TENANCY.md)

---

*Last Updated: 2025-11-25*