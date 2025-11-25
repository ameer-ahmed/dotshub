# Service Providers

Service providers are the central place of application bootstrapping in DotsHub. They are responsible for binding services, repositories, and other components into the service container.

## Overview

DotsHub uses four main service providers:

1. **AppServiceProvider** - General application bootstrapping
2. **PlatformServiceProvider** - Platform-based service resolution
3. **RepositoryServiceProvider** - Repository pattern bindings
4. **TenancyServiceProvider** - Multi-tenancy lifecycle management

## AppServiceProvider

**Location:** `app/Providers/AppServiceProvider.php`

The standard Laravel service provider for general application configuration.

### Methods

#### register()
```php
public function register(): void
{
    // Register any application services
}
```

Binds services into the container during the registration phase.

#### boot()
```php
public function boot(): void
{
    // Bootstrap any application services
}
```

Performs actions after all services are registered.

### Usage Examples

#### Register a Singleton

```php
public function register(): void
{
    $this->app->singleton(MyService::class, function ($app) {
        return new MyService($app->make(SomeDependency::class));
    });
}
```

#### Configure Model Observers

```php
public function boot(): void
{
    Product::observe(ProductObserver::class);
}
```

#### Register Global Query Scopes

```php
public function boot(): void
{
    User::addGlobalScope('active', function ($builder) {
        $builder->where('status', 'active');
    });
}
```

---

## PlatformServiceProvider

**Location:** `app/Providers/PlatformServiceProvider.php`

This is a custom provider that enables platform-specific service resolution. It allows the same abstract service to have different implementations for Web, Mobile, or other platforms.

### How It Works

The provider:
1. Detects the current platform from the request URL
2. Determines the API version
3. Binds abstract services to their platform-specific implementations

### Source Code Breakdown

```php
namespace App\Providers;

use App\Enums\Platform;
use Illuminate\Support\ServiceProvider;

class PlatformServiceProvider extends ServiceProvider
{
    protected $platform; // Current platform (web, mobile, etc.)
    protected $version;  // API version (v1, v2, etc.)

    /**
     * Register services based on platform detection
     */
    public function register(): void
    {
        $this->detectPlatform();
        $this->registerServices();
    }

    /**
     * Detect platform from route
     */
    protected function detectPlatform(): void
    {
        $segments = request()->segments();

        // URL format: /api/{version}/{platform}/{context}/...
        // Example: /api/v1/web/merchant/auth/sign-in

        if (isset($segments[2])) {
            $this->platform = ucfirst($segments[2]); // "Web"
        }

        if (isset($segments[1])) {
            $this->version = strtoupper($segments[1]); // "V1"
        }
    }

    /**
     * Register platform-specific services
     */
    protected function registerServices(): void
    {
        // Role Service
        $this->app->bind(
            \App\Http\Services\V1\Abstracts\Merchant\Role\RoleAbstractService::class,
            "App\\Http\\Services\\V1\\{$this->platform}\\Merchant\\Role\\RoleService"
        );

        // Admin Auth Service
        $this->app->bind(
            \App\Http\Services\V1\Abstracts\Admin\Auth\AuthAbstractService::class,
            "App\\Http\\Services\\V1\\{$this->platform}\\Admin\\Auth\\AuthService"
        );

        // Merchant Auth Service
        $this->app->bind(
            \App\Http\Services\V1\Abstracts\Merchant\Auth\AuthAbstractService::class,
            "App\\Http\\Services\\V1\\{$this->platform}\\Merchant\\Auth\\AuthService"
        );
    }
}
```

### Platform Detection

The platform is detected from the URL structure:

```
/api/{version}/{platform}/{context}/{resource}
     ↓         ↓           ↓         ↓
     V1        Web         Merchant  Auth
```

Examples:
- `/api/v1/web/merchant/auth/sign-in` → Platform: Web
- `/api/v1/mobile/merchant/auth/sign-in` → Platform: Mobile

### Adding a New Service

To add a new service to the platform provider:

```php
protected function registerServices(): void
{
    // Existing services...

    // Add new service
    $this->app->bind(
        \App\Http\Services\V1\Abstracts\Merchant\Product\ProductAbstractService::class,
        "App\\Http\\Services\\V1\\{$this->platform}\\Merchant\\Product\\ProductService"
    );
}
```

### Benefits

1. **Single Controller** - One controller works for all platforms
2. **Platform-Specific Logic** - Each platform can have unique implementation
3. **Easy Extension** - Adding a new platform is straightforward
4. **Type Safety** - Abstract classes enforce method contracts

### Example Flow

```php
// Controller (platform-agnostic)
class AuthController extends Controller
{
    public function __construct(
        protected AuthAbstractService $authService
    ) {}

    public function signIn(Request $request)
    {
        // $authService is automatically resolved to Web or Mobile version
        return $this->authService->signIn($request->all());
    }
}

// Web Implementation
class AuthService extends AuthAbstractService
{
    public function platform(): Platform
    {
        return Platform::WEB;
    }

    public function signIn(array $credentials)
    {
        // Web-specific logic
    }
}

// Mobile Implementation (future)
class AuthService extends AuthAbstractService
{
    public function platform(): Platform
    {
        return Platform::MOBILE;
    }

    public function signIn(array $credentials)
    {
        // Mobile-specific logic
        // Maybe return different response format
        // Maybe use different authentication method
    }
}
```

---

## RepositoryServiceProvider

**Location:** `app/Providers/RepositoryServiceProvider.php`

Binds repository interfaces to their concrete implementations, enabling dependency injection and easier testing.

### Source Code

```php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register repository bindings
     */
    public function register(): void
    {
        // Base Repository
        $this->app->bind(
            \App\Repository\Contracts\RepositoryInterface::class,
            \App\Repository\Eloquent\Repository::class
        );

        // Domain Repository
        $this->app->bind(
            \App\Repository\Contracts\DomainRepositoryInterface::class,
            \App\Repository\Eloquent\DomainRepository::class
        );

        // Role Repository (Tenant)
        $this->app->bind(
            \App\Repository\Contracts\Tenant\RoleRepositoryInterface::class,
            \App\Repository\Eloquent\Tenant\RoleRepository::class
        );

        // User Repository (Tenant)
        $this->app->bind(
            \App\Repository\Contracts\Tenant\UserRepositoryInterface::class,
            \App\Repository\Eloquent\Tenant\UserRepository::class
        );

        // Merchant Repository
        $this->app->bind(
            \App\Repository\Contracts\MerchantRepositoryInterface::class,
            \App\Repository\Eloquent\MerchantRepository::class
        );
    }
}
```

### Adding a New Repository

When you create a new repository, register it here:

```php
public function register(): void
{
    // Existing bindings...

    // New Product Repository
    $this->app->bind(
        \App\Repository\Contracts\ProductRepositoryInterface::class,
        \App\Repository\Eloquent\ProductRepository::class
    );
}
```

### Benefits

1. **Dependency Injection** - Controllers and services receive repositories automatically
2. **Easy Testing** - Mock interfaces instead of concrete classes
3. **Loose Coupling** - Code depends on interfaces, not implementations
4. **Flexibility** - Swap implementations without changing dependent code

### Usage in Services

```php
class ProductService
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository
    ) {}

    public function getAllProducts()
    {
        // Repository is automatically injected
        return $this->productRepository->getAll();
    }
}
```

---

## TenancyServiceProvider

**Location:** `app/Providers/TenancyServiceProvider.php`

Manages the multi-tenancy lifecycle, including tenant creation, database management, and context switching.

### Source Code Breakdown

```php
namespace App\Providers;

use App\Models\Merchant;
use Illuminate\Support\ServiceProvider;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

class TenancyServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap multi-tenancy services
     */
    public function boot(): void
    {
        $this->bootTenantEvents();
        $this->configureTenancyMiddleware();
    }

    /**
     * Register tenant lifecycle events
     */
    protected function bootTenantEvents(): void
    {
        // Event: Tenant Creating
        Event::listen(
            \Stancl\Tenancy\Events\TenantCreated::class,
            function ($event) {
                $tenant = $event->tenant;

                // Create tenant database
                $tenant->createDatabase();

                // Run tenant migrations
                $tenant->run(function () {
                    Artisan::call('migrate', [
                        '--path' => 'database/migrations/tenant',
                        '--force' => true,
                    ]);
                });
            }
        );

        // Event: Seeding tenant data
        Event::listen(
            \Stancl\Tenancy\Events\TenantCreated::class,
            function ($event) {
                $event->tenant->run(function () {
                    // Seed default roles
                    Artisan::call('db:seed', [
                        '--class' => 'TenantRoleSeeder',
                    ]);
                });
            }
        );

        // Event: Tenant Deleting
        Event::listen(
            \Stancl\Tenancy\Events\TenantDeleted::class,
            function ($event) {
                // Delete tenant database
                $event->tenant->deleteDatabase();
            }
        );
    }

    /**
     * Configure tenancy middleware priority
     */
    protected function configureTenancyMiddleware(): void
    {
        $this->app['router']->middlewarePriority = array_merge(
            [InitializeTenancyByDomain::class],
            $this->app['router']->middlewarePriority
        );
    }
}
```

### Tenant Lifecycle Events

#### 1. Tenant Creation

When a merchant signs up:

```php
Event::listen(TenantCreated::class, function ($event) {
    $tenant = $event->tenant;

    // Step 1: Create isolated database
    $tenant->createDatabase();

    // Step 2: Run migrations in tenant database
    $tenant->run(function () {
        Artisan::call('migrate', [
            '--path' => 'database/migrations/tenant',
            '--force' => true,
        ]);
    });

    // Step 3: Seed initial data
    $tenant->run(function () {
        Artisan::call('db:seed', ['--class' => 'TenantRoleSeeder']);
    });
});
```

**Database Naming:**
```
Central DB: dotshub_central
Tenant DB:  tenant9d4e2f3a7c1b4e8f9a6d2b5c8e7f1a3d
            ↑
            prefix from config/tenancy.php
```

#### 2. Tenant Deletion

When a merchant is deleted:

```php
Event::listen(TenantDeleted::class, function ($event) {
    // Automatically drop tenant database
    $event->tenant->deleteDatabase();
});
```

### Tenancy Bootstrappers

Bootstrappers are executed when switching tenant context:

1. **DatabaseTenancyBootstrapper**
   - Switches database connection to tenant database

2. **CacheTenancyBootstrapper**
   - Isolates cache by tenant (prefixes cache keys)

3. **FilesystemTenancyBootstrapper**
   - Isolates file storage by tenant

4. **QueueTenancyBootstrapper**
   - Maintains tenant context in queued jobs

### Middleware Configuration

```php
protected function configureTenancyMiddleware(): void
{
    // Ensure tenancy middleware runs first
    $this->app['router']->middlewarePriority = array_merge(
        [InitializeTenancyByDomain::class],
        $this->app['router']->middlewarePriority
    );
}
```

This ensures that tenant identification happens before other middleware runs.

### Tenant Context Switching

```php
// Switch to tenant context
$merchant = Merchant::find($id);
$merchant->run(function () {
    // Code here runs in tenant context
    $users = User::all(); // Queries tenant database
});

// Back to central context
$admins = Admin::all(); // Queries central database
```

### Customizing Tenant Creation

To customize what happens when a tenant is created:

```php
protected function bootTenantEvents(): void
{
    Event::listen(TenantCreated::class, function ($event) {
        $event->tenant->run(function () use ($event) {
            // Create default channel
            Channel::create([
                'name' => 'Default',
                'merchant_id' => $event->tenant->id,
            ]);

            // Create default settings
            Setting::create([
                'key' => 'currency',
                'value' => 'USD',
            ]);

            // Send welcome email
            Mail::to($event->tenant->email)->send(new WelcomeEmail());
        });
    });
}
```

---

## Best Practices

### 1. Keep Providers Focused

Each provider should have a single responsibility:
- `AppServiceProvider` - General app configuration
- `PlatformServiceProvider` - Platform resolution only
- `RepositoryServiceProvider` - Repository bindings only
- `TenancyServiceProvider` - Tenancy lifecycle only

### 2. Use Deferred Providers for Performance

For services not needed on every request:

```php
class HeavyServiceProvider extends ServiceProvider
{
    protected $defer = true;

    public function register()
    {
        $this->app->singleton(HeavyService::class);
    }

    public function provides()
    {
        return [HeavyService::class];
    }
}
```

### 3. Register Bindings in register(), Bootstrap in boot()

```php
public function register(): void
{
    // Bind services, register singletons
    $this->app->bind(ServiceInterface::class, Service::class);
}

public function boot(): void
{
    // Perform actions after all services registered
    // Publish configs, register observers, etc.
}
```

### 4. Type-hint Dependencies

```php
public function register(): void
{
    $this->app->singleton(MyService::class, function ($app) {
        return new MyService(
            $app->make(DependencyOne::class),
            $app->make(DependencyTwo::class)
        );
    });
}
```

---

## Registering Custom Providers

Add new providers to `config/app.php`:

```php
'providers' => [
    // Laravel Service Providers...

    // Application Service Providers
    App\Providers\AppServiceProvider::class,
    App\Providers\PlatformServiceProvider::class,
    App\Providers\RepositoryServiceProvider::class,
    App\Providers\TenancyServiceProvider::class,

    // Your Custom Provider
    App\Providers\CustomServiceProvider::class,
],
```

---

## Testing Providers

### Testing Service Bindings

```php
class PlatformServiceProviderTest extends TestCase
{
    public function test_auth_service_is_bound()
    {
        $this->get('/api/v1/web/merchant/auth/sign-in');

        $service = $this->app->make(AuthAbstractService::class);

        $this->assertInstanceOf(
            AuthService::class,
            $service
        );
    }

    public function test_correct_platform_is_detected()
    {
        $this->get('/api/v1/web/merchant/auth/sign-in');

        $service = $this->app->make(AuthAbstractService::class);

        $this->assertEquals(Platform::WEB, $service->platform());
    }
}
```

---

## Related Documentation

- [Services Documentation](./SERVICES.md) - Learn about service layer
- [Repository Documentation](./REPOSITORIES.md) - Learn about data access
- [Multi-Tenancy Guide](./MULTI_TENANCY.md) - Deep dive into tenancy
- [Architecture Overview](./ARCHITECTURE.md) - System design patterns

---

*Last Updated: 2025-11-25*