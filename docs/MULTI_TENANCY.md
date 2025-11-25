# Multi-Tenancy Guide

A comprehensive guide to understanding and working with DotsHub's multi-tenant architecture.

## Overview

DotsHub implements a **database-per-tenant** multi-tenancy strategy using the Stancl/Tenancy package. Each merchant (tenant) gets their own isolated database, ensuring maximum security and data separation.

## Multi-Tenancy Architecture

### Database-per-Tenant Model

```
┌─────────────────────────────────────────────────────────┐
│           Central Database (dotshub_central)            │
│  - Admins                                               │
│  - Merchants (Tenants)                                  │
│  - Plans                                                │
│  - Domains                                              │
└─────────────────────────────────────────────────────────┘
                           ↓
        ┌──────────────────┴──────────────────┐
        ↓                                      ↓
┌──────────────────────┐          ┌──────────────────────┐
│  Tenant DB 1         │          │  Tenant DB 2         │
│  (tenant{uuid1})     │          │  (tenant{uuid2})     │
│  - Users             │          │  - Users             │
│  - Roles             │          │  - Roles             │
│  - Permissions       │          │  - Permissions       │
│  - Products          │          │  - Products          │
│  - Orders            │          │  - Orders            │
│  - ...               │          │  - ...               │
└──────────────────────┘          └──────────────────────┘
```

## Key Concepts

### 1. Tenant (Merchant)

A tenant represents a merchant/customer using your platform. Each tenant has:
- Unique UUID identifier
- Own database
- One or more domains
- Multiple users
- Isolated data

**Model:** `App\Models\Merchant`

```php
use App\Models\Merchant;

$merchant = Merchant::create([
    'name' => 'Awesome Store',
    'description' => 'Best products online',
    'status' => 'active',
]);
```

### 2. Domain

Domains identify tenants and route requests to the correct tenant database.

**Model:** `App\Models\Domain`

```php
use App\Models\Domain;

$domain = Domain::create([
    'domain' => 'awesomestore.example.com',
    'tenant_id' => $merchant->id,
]);
```

### 3. Tenant Context

The application operates in two contexts:

**Central Context:**
- Access to central database
- Merchant, Admin, Plan models
- Tenant management operations

**Tenant Context:**
- Access to tenant database
- User, Role, Permission, Product models (tenant-specific)
- Tenant-specific operations

## Tenant Identification

### Domain-Based Identification

Tenants are identified by their domain:

```
Request to: mystore.example.com
           ↓
Middleware: InitializeTenancyByDomain
           ↓
Lookup domain in central database
           ↓
Find matching merchant (tenant)
           ↓
Switch to tenant database
           ↓
Execute request in tenant context
```

### Central Domains

Configured in `config/tenancy.php`:

```php
'central_domains' => [
    'localhost',
    '127.0.0.1',
    'dotshub.test',
],
```

Requests to central domains operate in central context.

## Tenant Lifecycle

### Creating a Tenant

When a merchant signs up, the following happens automatically:

**Step 1: Create Merchant Record**
```php
$merchant = Merchant::create([
    'name' => 'Store Name',
    'description' => 'Store Description',
    'status' => 'pending',
]);
```

**Step 2: Create Domain**
```php
Domain::create([
    'domain' => 'mystore.localhost',
    'tenant_id' => $merchant->id,
]);
```

**Step 3: Create Database** (automatic via TenancyServiceProvider)
```sql
CREATE DATABASE tenant9d4e2f3a7c1b4e8f9a6d2b5c8e7f1a3d;
```

**Step 4: Run Migrations** (automatic)
```php
$merchant->run(function () {
    Artisan::call('migrate', [
        '--path' => 'database/migrations/tenant',
        '--force' => true,
    ]);
});
```

**Step 5: Seed Data** (automatic)
```php
$merchant->run(function () {
    Artisan::call('db:seed', [
        '--class' => 'TenantRoleSeeder',
    ]);
});
```

**Step 6: Create First User**
```php
$merchant->run(function () use ($data, $merchant) {
    $user = User::create([
        'name' => $data['name'],
        'email' => $data['email'],
        'password' => Hash::make($data['password']),
        'merchant_id' => $merchant->id,
        'status' => 'active',
    ]);

    $user->addRole('merchant_admin');

    return $user;
});
```

### Complete Signup Flow

```php
// app/Http/Services/V1/Web/Merchant/Auth/AuthService.php

public function signUp(array $data)
{
    return DB::transaction(function () use ($data) {
        // 1. Create Merchant (triggers database creation)
        $merchant = $this->merchantRepository->create([
            'name' => $data['merchant']['name'],
            'description' => $data['merchant']['description'] ?? null,
            'status' => 'pending',
        ]);

        // 2. Create Domain
        $this->domainRepository->create([
            'domain' => $data['domain']['domain'],
            'tenant_id' => $merchant->id,
        ]);

        // 3. Create User in tenant context
        $user = $merchant->run(function () use ($data, $merchant) {
            $user = $this->userRepository->create([
                'name' => $data['user']['name'],
                'email' => $data['user']['email'],
                'password' => Hash::make($data['user']['password']),
                'merchant_id' => $merchant->id,
                'status' => 'active',
            ]);

            $user->addRole('merchant_admin');

            return $user;
        });

        // 4. Generate JWT
        $token = auth('user')->login($user);

        return Responser::success([
            'merchant' => $merchant,
            'user' => new UserResource($user),
            'access_token' => $token,
        ], 'Merchant registered successfully', 201);
    });
}
```

### Deleting a Tenant

When a merchant is deleted:

```php
$merchant = Merchant::find($id);
$merchant->delete();

// Automatic via TenancyServiceProvider:
// 1. Delete tenant database
// 2. Delete associated domains
// 3. Cleanup tenant cache
```

## Working with Tenancy

### Running Code in Tenant Context

Use the `run()` method to execute code in tenant context:

```php
$merchant = Merchant::find($merchantId);

// Execute in tenant context
$merchant->run(function () {
    // This runs in tenant database
    $users = User::all();
    $roles = Role::all();

    // Create records in tenant database
    Product::create([
        'name' => 'New Product',
        'price' => 99.99,
    ]);
});

// Back to central context
$admins = Admin::all(); // Queries central database
```

### Switching Between Contexts

```php
use Stancl\Tenancy\Facades\Tenancy;

// Central context
$merchants = Merchant::all();

// Initialize tenancy
$merchant = Merchant::find($id);
tenancy()->initialize($merchant);

// Tenant context
$users = User::all();

// End tenancy (back to central)
tenancy()->end();

// Central context again
$plans = Plan::all();
```

### Getting Current Tenant

```php
use Stancl\Tenancy\Facades\Tenancy;

// Check if in tenant context
if (tenancy()->initialized) {
    // Get current tenant
    $tenant = tenancy()->tenant;

    echo "Current tenant: " . $tenant->name;
    echo "Tenant ID: " . $tenant->id;
}
```

## Middleware

### InitializeTenancyByDomain

Identifies tenant by domain and switches to tenant database.

**Configuration:**
```php
// routes/api/v1/web/merchant/auth.php

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::post('sign-in', [AuthController::class, 'signIn']);
});
```

**Flow:**
1. Extracts domain from request
2. Looks up domain in central database
3. Finds associated merchant (tenant)
4. Switches database connection to tenant DB
5. Proceeds with request

### PreventAccessFromCentralDomains

Prevents tenant routes from being accessed via central domains.

```php
// Blocks this:
http://localhost/api/v1/web/merchant/auth/sign-in

// Allows this:
http://mystore.localhost/api/v1/web/merchant/auth/sign-in
```

## Database Management

### Central Migrations

Located in: `database/migrations/`

Run with:
```bash
php artisan migrate
```

Creates tables in central database:
- admins
- merchants
- plans
- domains

### Tenant Migrations

Located in: `database/migrations/tenant/`

Run for all tenants:
```bash
php artisan tenants:migrate
```

Run for specific tenant:
```bash
php artisan tenants:migrate --tenants=9d4e2f3a-7c1b-4e8f-9a6d-2b5c8e7f1a3d
```

Rollback tenant migrations:
```bash
php artisan tenants:rollback
```

### Creating Migrations

**Central Migration:**
```bash
php artisan make:migration create_plans_table
```

**Tenant Migration:**
```bash
php artisan make:migration create_products_table --path=database/migrations/tenant
```

## Models and Relationships

### Central Models

Located in: `app/Models/`

- `Admin` - Platform administrators
- `Merchant` - Tenants (extends `Stancl\Tenancy\Database\Models\Tenant`)
- `Plan` - Subscription plans
- `Domain` - Tenant domains

**Example:**
```php
use App\Models\Merchant;

// Get merchant
$merchant = Merchant::find($id);

// Relationships
$domains = $merchant->domains;
$users = $merchant->run(fn() => User::all());
```

### Tenant Models

Located in: `app/Models/Tenant/`

- `User` - Tenant users
- `Role` - Tenant roles
- `Permission` - Tenant permissions
- `Product` - Products (example)

**Example:**
```php
use App\Models\Tenant\User;

// Must be in tenant context
$users = User::where('status', 'active')->get();

// Relationships
$user = User::find(1);
$roles = $user->roles;
$permissions = $user->allPermissions();
```

## Tenant Isolation

### Data Isolation

Each tenant's data is completely isolated:

```php
// Tenant 1 context
$merchant1 = Merchant::find($tenant1Id);
$merchant1->run(function () {
    User::create(['name' => 'User A']);
});

// Tenant 2 context
$merchant2 = Merchant::find($tenant2Id);
$merchant2->run(function () {
    $users = User::all(); // Only sees Tenant 2 users, not Tenant 1
});
```

### Cache Isolation

Tenant caches are automatically prefixed:

```php
// In tenant context
cache()->put('key', 'value'); // Stored as "tenant{id}_key"

// Different tenant can't access it
// Each tenant has isolated cache namespace
```

### Storage Isolation

File uploads are isolated per tenant:

```php
// In tenant context
Storage::disk('public')->put('file.pdf', $content);
// Stored in: storage/app/public/tenant{id}/file.pdf
```

## Security Considerations

### 1. Always Verify Tenant Ownership

```php
public function show(int $productId)
{
    $user = auth('user')->user();
    $product = Product::find($productId);

    // Verify product belongs to user's merchant
    if ($product->merchant_id !== $user->merchant_id) {
        return Responser::fail('Unauthorized', 403);
    }

    return Responser::success($product);
}
```

### 2. Never Trust Domain from Request

Always use middleware for tenant identification:

```php
// Bad - don't do this
$domain = $request->header('X-Tenant-Domain');
$merchant = Merchant::whereDomain($domain)->first();

// Good - use middleware
Route::middleware([InitializeTenancyByDomain::class])->group(function () {
    // Tenant already identified by middleware
});
```

### 3. Prevent Cross-Tenant Data Leaks

```php
// Add merchant_id to all tenant queries
class Product extends Model
{
    protected static function booted()
    {
        static::addGlobalScope('merchant', function ($builder) {
            if (tenancy()->initialized) {
                $builder->where('merchant_id', tenancy()->tenant->id);
            }
        });
    }
}
```

### 4. Validate Domain Ownership

```php
public function addDomain(array $data)
{
    $user = auth('user')->user();

    // Ensure user can only add domains to their merchant
    Domain::create([
        'domain' => $data['domain'],
        'tenant_id' => $user->merchant_id, // Use authenticated user's merchant
    ]);
}
```

## Advanced Usage

### Tenant Seeding

Create a tenant seeder:

```php
// database/seeders/TenantRoleSeeder.php

namespace Database\Seeders;

use App\Models\Tenant\Role;
use Illuminate\Database\Seeder;

class TenantRoleSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            [
                'name' => 'merchant_admin',
                'display_name' => 'Merchant Administrator',
                'description' => 'Full access to all features',
                'is_private' => true,
                'is_editable' => false,
            ],
            [
                'name' => 'staff',
                'display_name' => 'Staff Member',
                'description' => 'Basic staff access',
                'is_private' => false,
                'is_editable' => true,
            ],
        ];

        foreach ($roles as $roleData) {
            Role::create($roleData);
        }
    }
}
```

Run for all tenants:
```bash
php artisan tenants:seed --class=TenantRoleSeeder
```

### Queuing Jobs with Tenancy

Jobs maintain tenant context:

```php
use App\Jobs\ProcessOrder;
use App\Models\Tenant\Order;

// In tenant context
$order = Order::find(1);
ProcessOrder::dispatch($order); // Job runs in same tenant context
```

**Job Class:**
```php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Middleware\IdentificationMiddleware;

class ProcessOrder implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Order $order
    ) {}

    public function handle()
    {
        // Automatically runs in correct tenant context
        $this->order->process();
    }
}
```

### Custom Tenant Columns

Add custom fields to merchants:

```php
// Migration
Schema::table('merchants', function (Blueprint $table) {
    $table->string('phone')->nullable();
    $table->json('settings')->nullable();
    $table->string('logo_url')->nullable();
});

// Model
class Merchant extends Tenant
{
    protected $casts = [
        'settings' => 'array',
    ];

    public function getSetting(string $key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }
}
```

### Multiple Domains per Tenant

```php
$merchant = Merchant::find($id);

// Add multiple domains
$merchant->domains()->createMany([
    ['domain' => 'store.example.com'],
    ['domain' => 'shop.example.com'],
    ['domain' => 'www.example.com'],
]);

// Access from any domain
// All route to same tenant database
```

### Tenant Impersonation (for Support)

```php
// Admin impersonates tenant for support
public function impersonate(string $merchantId)
{
    $admin = auth('admin')->user();

    // Log the impersonation
    Log::info("Admin {$admin->id} impersonating merchant {$merchantId}");

    $merchant = Merchant::find($merchantId);

    return $merchant->run(function () {
        $user = User::first(); // Get any user in tenant

        $token = auth('user')->login($user);

        return Responser::success([
            'user' => $user,
            'access_token' => $token,
        ]);
    });
}
```

## Troubleshooting

### Issue: "Tenant could not be identified"

**Cause:** Domain not found in database or not configured correctly.

**Solution:**
1. Check domain exists in `domains` table
2. Verify domain matches exactly (no http://, trailing slashes, etc.)
3. Add to hosts file if testing locally

### Issue: "Table 'central_db.users' doesn't exist"

**Cause:** Trying to access tenant model from central context.

**Solution:** Ensure you're in tenant context:
```php
$merchant->run(function () {
    $users = User::all(); // Now in tenant context
});
```

### Issue: Migrations not running for tenant

**Cause:** Using wrong migration path.

**Solution:**
```bash
# Wrong
php artisan migrate --path=database/migrations/tenant

# Right (for tenants)
php artisan tenants:migrate
```

### Issue: "Access denied for user to database 'tenant...'"

**Cause:** Database user doesn't have permissions to create databases.

**Solution:** Grant permissions:
```sql
GRANT ALL PRIVILEGES ON `tenant%`.* TO 'your_user'@'localhost';
FLUSH PRIVILEGES;
```

### Issue: Cache conflicts between tenants

**Cause:** Not using tenant-aware cache.

**Solution:** Ensure `CacheTenancyBootstrapper` is enabled in `config/tenancy.php`.

## Configuration

### config/tenancy.php

Key configuration options:

```php
return [
    // Tenant model
    'tenant_model' => \App\Models\Merchant::class,

    // ID generator
    'id_generator' => \Stancl\Tenancy\UUIDGenerator::class,

    // Database configuration
    'database' => [
        'prefix' => env('TENANCY_DATABASE_PREFIX', 'tenant'),
        'suffix' => env('TENANCY_DATABASE_SUFFIX', ''),
    ],

    // Central domains
    'central_domains' => [
        'localhost',
        '127.0.0.1',
        'dotshub.test',
    ],

    // Bootstrappers
    'bootstrappers' => [
        \Stancl\Tenancy\Bootstrappers\DatabaseTenancyBootstrapper::class,
        \Stancl\Tenancy\Bootstrappers\CacheTenancyBootstrapper::class,
        \Stancl\Tenancy\Bootstrappers\FilesystemTenancyBootstrapper::class,
        \Stancl\Tenancy\Bootstrappers\QueueTenancyBootstrapper::class,
    ],

    // Features
    'features' => [
        \Stancl\Tenancy\Features\UserImpersonation::class,
    ],
];
```

## Best Practices

### 1. Always Use Middleware for Tenant Routes

```php
// Good
Route::middleware([InitializeTenancyByDomain::class])->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
});

// Bad - manually initializing tenancy
Route::get('/products', function () {
    $merchant = Merchant::find($id);
    tenancy()->initialize($merchant);
    return Product::all();
});
```

### 2. Store Tenant ID in Tenant Models

```php
// Migration
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->uuid('merchant_id');
    // other fields
});

// Model
class Product extends Model
{
    protected $fillable = ['merchant_id', /* other fields */];

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }
}
```

### 3. Test Tenant Isolation

```php
public function test_tenant_data_is_isolated()
{
    $merchant1 = Merchant::factory()->create();
    $merchant2 = Merchant::factory()->create();

    $merchant1->run(function () {
        User::factory()->create(['name' => 'User 1']);
    });

    $merchant2->run(function () {
        $users = User::all();
        $this->assertCount(0, $users); // Should not see merchant1's users
    });
}
```

### 4. Document Tenant-Specific Features

Clearly document which features are tenant-specific vs platform-wide.

---

## Related Documentation

- [Architecture Overview](./ARCHITECTURE.md) - System design
- [Providers](./PROVIDERS.md) - TenancyServiceProvider details
- [Getting Started](./GETTING_STARTED.md) - Setup guide
- [API Documentation](./API.md) - API endpoints

---

*Last Updated: 2025-11-25*