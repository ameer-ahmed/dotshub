# Services Documentation

Services in DotsHub contain the business logic of your application. They act as an intermediary layer between controllers and repositories, orchestrating operations and enforcing business rules.

## Overview

The service layer:
- Encapsulates business logic
- Coordinates operations across multiple repositories
- Enforces business rules and validations
- Maintains separation of concerns
- Supports platform-specific implementations

## Architecture

```
Controller → Service (Abstract) → Repository → Model → Database
              ↓
         Platform Implementation
         (Web, Mobile, etc.)
```

## Service Structure

```
app/Http/Services/
└── V1/                                    # API Version
    ├── Abstracts/                         # Abstract Services
    │   ├── PlatformService.php           # Base abstract class
    │   └── [Context]/                    # Admin, Merchant, etc.
    │       └── [Feature]/                # Auth, Role, Product, etc.
    │           └── FeatureAbstractService.php
    └── [Platform]/                        # Web, Mobile, etc.
        └── [Context]/
            └── [Feature]/
                └── FeatureService.php     # Concrete implementation
```

## Base Service Classes

### PlatformService

**Location:** `app/Http/Services/V1/Abstracts/PlatformService.php`

```php
namespace App\Http\Services\V1\Abstracts;

use App\Enums\Platform;

abstract class PlatformService
{
    /**
     * Get the platform this service is for
     *
     * @return Platform
     */
    abstract public function platform(): Platform;
}
```

All services must extend this class and implement the `platform()` method.

## Existing Services

### 1. Admin Auth Service

Handles authentication operations for platform administrators.

#### Abstract Service

**Location:** `app/Http/Services/V1/Abstracts/Admin/Auth/AuthAbstractService.php`

```php
namespace App\Http\Services\V1\Abstracts\Admin\Auth;

use App\Http\Services\V1\Abstracts\PlatformService;

abstract class AuthAbstractService extends PlatformService
{
    /**
     * Register a new admin
     *
     * @param array $data
     * @return mixed
     */
    abstract public function signUp(array $data);

    /**
     * Authenticate an admin
     *
     * @param array $credentials
     * @return mixed
     */
    abstract public function signIn(array $credentials);

    /**
     * Log out an admin
     *
     * @return mixed
     */
    abstract public function signOut();
}
```

#### Web Implementation

**Location:** `app/Http/Services/V1/Web/Admin/Auth/AuthService.php`

```php
namespace App\Http\Services\V1\Web\Admin\Auth;

use App\Enums\Platform;
use App\Http\Helpers\Responser;
use App\Http\Resources\V1\Web\Admin\AdminResource;
use App\Http\Services\V1\Abstracts\Admin\Auth\AuthAbstractService;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AuthService extends AuthAbstractService
{
    public function platform(): Platform
    {
        return Platform::WEB;
    }

    public function signUp(array $data)
    {
        // Create admin account
        $admin = Admin::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Generate JWT token
        $token = auth('admin')->login($admin);

        return Responser::success([
            'admin' => new AdminResource($admin),
            'access_token' => $token,
            'token_type' => 'bearer',
        ], 'Admin registered successfully', 201);
    }

    public function signIn(array $credentials)
    {
        // Attempt authentication
        if (!$token = auth('admin')->attempt($credentials)) {
            return Responser::fail('Invalid credentials', 401);
        }

        $admin = auth('admin')->user();

        return Responser::success([
            'admin' => new AdminResource($admin),
            'access_token' => $token,
            'token_type' => 'bearer',
        ], 'Admin signed in successfully');
    }

    public function signOut()
    {
        auth('admin')->logout();

        return Responser::success(null, 'Admin signed out successfully');
    }
}
```

**Key Points:**
- Uses central database (`Admin` model)
- Uses `admin` auth guard
- Returns standardized responses via `Responser`
- Transforms data with `AdminResource`

### 2. Merchant Auth Service

Handles authentication for merchant users (tenant users).

#### Abstract Service

**Location:** `app/Http/Services/V1/Abstracts/Merchant/Auth/AuthAbstractService.php`

```php
namespace App\Http\Services\V1\Abstracts\Merchant\Auth;

use App\Http\Services\V1\Abstracts\PlatformService;

abstract class AuthAbstractService extends PlatformService
{
    abstract public function signUp(array $data);
    abstract public function signIn(array $credentials);
    abstract public function signOut();
}
```

#### Web Implementation

**Location:** `app/Http/Services/V1/Web/Merchant/Auth/AuthService.php`

```php
namespace App\Http\Services\V1\Web\Merchant\Auth;

use App\Enums\Platform;
use App\Http\Helpers\Responser;
use App\Http\Resources\V1\Web\Merchant\UserResource;
use App\Http\Services\V1\Abstracts\Merchant\Auth\AuthAbstractService;
use App\Models\Merchant;
use App\Models\Tenant\User;
use App\Repository\Contracts\DomainRepositoryInterface;
use App\Repository\Contracts\MerchantRepositoryInterface;
use App\Repository\Contracts\Tenant\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthService extends AuthAbstractService
{
    public function __construct(
        protected MerchantRepositoryInterface $merchantRepository,
        protected DomainRepositoryInterface $domainRepository,
        protected UserRepositoryInterface $userRepository
    ) {}

    public function platform(): Platform
    {
        return Platform::WEB;
    }

    public function signUp(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Step 1: Create Merchant (Tenant)
            $merchant = $this->merchantRepository->create([
                'name' => $data['merchant']['name'],
                'description' => $data['merchant']['description'] ?? null,
                'status' => 'pending',
            ]);

            // Step 2: Create Domain
            $this->domainRepository->create([
                'domain' => $data['domain']['domain'],
                'tenant_id' => $merchant->id,
            ]);

            // Step 3: Create First User (in tenant context)
            $user = $merchant->run(function () use ($data, $merchant) {
                $user = $this->userRepository->create([
                    'name' => $data['user']['name'],
                    'email' => $data['user']['email'],
                    'password' => Hash::make($data['user']['password']),
                    'merchant_id' => $merchant->id,
                    'status' => 'active',
                ]);

                // Assign merchant_admin role
                $user->addRole('merchant_admin');

                return $user;
            });

            // Generate JWT token
            $token = auth('user')->login($user);

            return Responser::success([
                'merchant' => $merchant,
                'user' => new UserResource($user),
                'access_token' => $token,
                'token_type' => 'bearer',
            ], 'Merchant registered successfully', 201);
        });
    }

    public function signIn(array $credentials)
    {
        // Attempt authentication (in tenant context)
        if (!$token = auth('user')->attempt($credentials)) {
            return Responser::fail('Invalid credentials', 401);
        }

        $user = auth('user')->user();

        return Responser::success([
            'user' => new UserResource($user),
            'access_token' => $token,
            'token_type' => 'bearer',
        ], 'User signed in successfully');
    }

    public function signOut()
    {
        auth('user')->logout();

        return Responser::success(null, 'User signed out successfully');
    }
}
```

**Key Points:**
- Creates merchant, domain, and user in a single transaction
- Uses tenant context for user operations (`$merchant->run()`)
- Uses `user` auth guard
- Assigns default role to first user
- Works across multiple repositories

### 3. Role Service

Manages roles and permissions within tenant context.

#### Abstract Service

**Location:** `app/Http/Services/V1/Abstracts/Merchant/Role/RoleAbstractService.php`

```php
namespace App\Http\Services\V1\Abstracts\Merchant\Role;

use App\Http\Services\V1\Abstracts\PlatformService;

abstract class RoleAbstractService extends PlatformService
{
    abstract public function index();
    abstract public function store(array $data);
    abstract public function update(int $id, array $data);
    abstract public function delete(int $id);
}
```

#### Web Implementation

**Location:** `app/Http/Services/V1/Web/Merchant/Role/RoleService.php`

```php
namespace App\Http\Services\V1\Web\Merchant\Role;

use App\Enums\Platform;
use App\Http\Helpers\Responser;
use App\Http\Resources\V1\Web\Merchant\RoleResource;
use App\Http\Services\V1\Abstracts\Merchant\Role\RoleAbstractService;
use App\Repository\Contracts\Tenant\RoleRepositoryInterface;
use Illuminate\Support\Facades\DB;

class RoleService extends RoleAbstractService
{
    public function __construct(
        protected RoleRepositoryInterface $roleRepository
    ) {}

    public function platform(): Platform
    {
        return Platform::WEB;
    }

    public function index()
    {
        $user = auth('user')->user();
        $merchantId = $user->merchant_id;

        // Get roles for this merchant
        $roles = $this->roleRepository->get([
            ['merchant_id', '=', $merchantId]
        ]);

        return Responser::success(
            RoleResource::collection($roles),
            'Roles retrieved successfully'
        );
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $user = auth('user')->user();

            // Create role
            $role = $this->roleRepository->create([
                'name' => $data['name'],
                'display_name' => $data['display_name'],
                'description' => $data['description'] ?? null,
                'merchant_id' => $user->merchant_id,
                'created_by' => $user->id,
                'is_private' => $data['is_private'] ?? false,
                'is_editable' => $data['is_editable'] ?? true,
            ]);

            // Sync permissions
            if (isset($data['permissions'])) {
                $role->syncPermissions($data['permissions']);
            }

            return Responser::success(
                new RoleResource($role),
                'Role created successfully',
                201
            );
        });
    }

    public function update(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $user = auth('user')->user();

            // Find role
            $role = $this->roleRepository->getById($id);

            if (!$role) {
                return Responser::fail('Role not found', 404);
            }

            // Check ownership
            if ($role->merchant_id !== $user->merchant_id) {
                return Responser::fail('Unauthorized', 403);
            }

            // Check if editable
            if (!$role->is_editable) {
                return Responser::fail('Role is not editable', 403);
            }

            // Update role
            $this->roleRepository->update($role, [
                'display_name' => $data['display_name'] ?? $role->display_name,
                'description' => $data['description'] ?? $role->description,
            ]);

            // Sync permissions
            if (isset($data['permissions'])) {
                $role->syncPermissions($data['permissions']);
            }

            return Responser::success(
                new RoleResource($role->fresh()),
                'Role updated successfully'
            );
        });
    }

    public function delete(int $id)
    {
        $user = auth('user')->user();

        // Find role
        $role = $this->roleRepository->getById($id);

        if (!$role) {
            return Responser::fail('Role not found', 404);
        }

        // Check ownership
        if ($role->merchant_id !== $user->merchant_id) {
            return Responser::fail('Unauthorized', 403);
        }

        // Check if editable
        if (!$role->is_editable) {
            return Responser::fail('Role is not deletable', 403);
        }

        // Delete role
        $this->roleRepository->delete($role);

        return Responser::success(null, 'Role deleted successfully');
    }
}
```

**Key Points:**
- Enforces tenant isolation (checks `merchant_id`)
- Enforces ownership rules
- Uses transactions for data integrity
- Validates editability before modifications
- Syncs relationships (permissions)

## Creating a New Service

### Step 1: Use Artisan Command

```bash
php artisan make:platform-service V1/Web/Merchant/Product ProductService
```

This creates:
- `app/Http/Services/V1/Abstracts/Merchant/Product/ProductAbstractService.php`
- `app/Http/Services/V1/Web/Merchant/Product/ProductService.php`

### Step 2: Define Abstract Methods

Edit the abstract service to define the contract:

```php
namespace App\Http\Services\V1\Abstracts\Merchant\Product;

use App\Http\Services\V1\Abstracts\PlatformService;

abstract class ProductAbstractService extends PlatformService
{
    /**
     * Get all products
     */
    abstract public function index(array $filters = []);

    /**
     * Get single product
     */
    abstract public function show(int $id);

    /**
     * Create new product
     */
    abstract public function store(array $data);

    /**
     * Update existing product
     */
    abstract public function update(int $id, array $data);

    /**
     * Delete product
     */
    abstract public function delete(int $id);
}
```

### Step 3: Implement Concrete Service

Implement the methods in the concrete service:

```php
namespace App\Http\Services\V1\Web\Merchant\Product;

use App\Enums\Platform;
use App\Http\Helpers\Responser;
use App\Http\Resources\V1\Web\Merchant\ProductResource;
use App\Http\Services\V1\Abstracts\Merchant\Product\ProductAbstractService;
use App\Repository\Contracts\Tenant\ProductRepositoryInterface;
use Illuminate\Support\Facades\DB;

class ProductService extends ProductAbstractService
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository
    ) {}

    public function platform(): Platform
    {
        return Platform::WEB;
    }

    public function index(array $filters = [])
    {
        $user = auth('user')->user();

        // Build query conditions
        $conditions = [
            ['merchant_id', '=', $user->merchant_id]
        ];

        if (isset($filters['category_id'])) {
            $conditions[] = ['category_id', '=', $filters['category_id']];
        }

        if (isset($filters['status'])) {
            $conditions[] = ['status', '=', $filters['status']];
        }

        // Get products with pagination
        $products = $this->productRepository->paginateWithQuery($conditions);

        return Responser::success(
            ProductResource::collection($products),
            'Products retrieved successfully'
        );
    }

    public function show(int $id)
    {
        $user = auth('user')->user();

        $product = $this->productRepository->getById($id);

        if (!$product) {
            return Responser::fail('Product not found', 404);
        }

        // Check ownership
        if ($product->merchant_id !== $user->merchant_id) {
            return Responser::fail('Unauthorized', 403);
        }

        return Responser::success(
            new ProductResource($product),
            'Product retrieved successfully'
        );
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $user = auth('user')->user();

            // Business logic: Check if SKU is unique
            $exists = $this->productRepository->first([
                ['sku', '=', $data['sku']],
                ['merchant_id', '=', $user->merchant_id]
            ]);

            if ($exists) {
                return Responser::fail('SKU already exists', 422);
            }

            // Create product
            $product = $this->productRepository->create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'sku' => $data['sku'],
                'price' => $data['price'],
                'cost' => $data['cost'] ?? 0,
                'quantity' => $data['quantity'] ?? 0,
                'merchant_id' => $user->merchant_id,
                'created_by' => $user->id,
                'status' => $data['status'] ?? 'active',
            ]);

            // Business logic: Log product creation
            activity()
                ->causedBy($user)
                ->performedOn($product)
                ->log('Product created');

            return Responser::success(
                new ProductResource($product),
                'Product created successfully',
                201
            );
        });
    }

    public function update(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $user = auth('user')->user();

            $product = $this->productRepository->getById($id);

            if (!$product) {
                return Responser::fail('Product not found', 404);
            }

            // Check ownership
            if ($product->merchant_id !== $user->merchant_id) {
                return Responser::fail('Unauthorized', 403);
            }

            // Business logic: Check SKU uniqueness if changing
            if (isset($data['sku']) && $data['sku'] !== $product->sku) {
                $exists = $this->productRepository->first([
                    ['sku', '=', $data['sku']],
                    ['merchant_id', '=', $user->merchant_id],
                    ['id', '!=', $id]
                ]);

                if ($exists) {
                    return Responser::fail('SKU already exists', 422);
                }
            }

            // Update product
            $this->productRepository->update($product, $data);

            return Responser::success(
                new ProductResource($product->fresh()),
                'Product updated successfully'
            );
        });
    }

    public function delete(int $id)
    {
        $user = auth('user')->user();

        $product = $this->productRepository->getById($id);

        if (!$product) {
            return Responser::fail('Product not found', 404);
        }

        // Check ownership
        if ($product->merchant_id !== $user->merchant_id) {
            return Responser::fail('Unauthorized', 403);
        }

        // Business logic: Check if product has orders
        if ($product->orders()->exists()) {
            return Responser::fail('Cannot delete product with existing orders', 422);
        }

        // Soft delete
        $this->productRepository->delete($product);

        return Responser::success(null, 'Product deleted successfully');
    }
}
```

### Step 4: Register in PlatformServiceProvider

```php
// app/Providers/PlatformServiceProvider.php

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

### Step 5: Use in Controller

```php
namespace App\Http\Controllers\API\V1\Web\Merchant\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Web\Merchant\Product\StoreProductRequest;
use App\Http\Requests\V1\Web\Merchant\Product\UpdateProductRequest;
use App\Http\Services\V1\Abstracts\Merchant\Product\ProductAbstractService;

class ProductController extends Controller
{
    public function __construct(
        protected ProductAbstractService $productService
    ) {}

    public function index(Request $request)
    {
        return $this->productService->index($request->all());
    }

    public function show(int $id)
    {
        return $this->productService->show($id);
    }

    public function store(StoreProductRequest $request)
    {
        return $this->productService->store($request->validated());
    }

    public function update(int $id, UpdateProductRequest $request)
    {
        return $this->productService->update($id, $request->validated());
    }

    public function destroy(int $id)
    {
        return $this->productService->delete($id);
    }
}
```

## Service Best Practices

### 1. Use Transactions for Multi-Step Operations

```php
use Illuminate\Support\Facades\DB;

public function complexOperation(array $data)
{
    return DB::transaction(function () use ($data) {
        // Step 1
        $record1 = $this->repository1->create($data);

        // Step 2
        $record2 = $this->repository2->create([
            'related_id' => $record1->id,
            // ...
        ]);

        // Step 3
        $this->repository3->update($something, $data);

        return Responser::success($record1);
    });
}
```

### 2. Enforce Business Rules in Services

```php
public function updatePrice(int $productId, float $newPrice)
{
    $product = $this->productRepository->getById($productId);

    // Business rule: Price cannot be lower than cost
    if ($newPrice < $product->cost) {
        return Responser::fail('Price cannot be lower than cost', 422);
    }

    // Business rule: Price change requires approval for active products
    if ($product->status === 'active' && !$this->hasApproval()) {
        return Responser::fail('Price change requires approval', 403);
    }

    $this->productRepository->update($product, ['price' => $newPrice]);

    return Responser::success($product);
}
```

### 3. Always Check Ownership in Multi-Tenant

```php
public function show(int $id)
{
    $user = auth('user')->user();
    $resource = $this->repository->getById($id);

    if (!$resource) {
        return Responser::fail('Resource not found', 404);
    }

    // Enforce tenant isolation
    if ($resource->merchant_id !== $user->merchant_id) {
        return Responser::fail('Unauthorized', 403);
    }

    return Responser::success($resource);
}
```

### 4. Use Type Hints and Return Types

```php
public function calculateTotal(int $orderId): float
{
    $order = $this->orderRepository->getById($orderId);

    return $order->items->sum(fn($item) => $item->price * $item->quantity);
}
```

### 5. Keep Services Focused

One service per feature/resource:
- ✅ `ProductService` - handles products
- ✅ `OrderService` - handles orders
- ❌ `ProductAndOrderService` - handles both (bad)

### 6. Inject Dependencies

```php
public function __construct(
    protected ProductRepositoryInterface $productRepository,
    protected CategoryRepositoryInterface $categoryRepository,
    protected InventoryService $inventoryService
) {}
```

### 7. Return Consistent Responses

Always use the `Responser` helper:

```php
// Success
return Responser::success($data, 'Message', 200);

// Error
return Responser::fail('Error message', 400);

// Custom
return Responser::custom(true, $data, 'Message', 201);
```

## Testing Services

```php
namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Http\Services\V1\Web\Merchant\Product\ProductService;
use App\Repository\Contracts\Tenant\ProductRepositoryInterface;

class ProductServiceTest extends TestCase
{
    protected ProductService $service;
    protected $productRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productRepository = $this->mock(ProductRepositoryInterface::class);
        $this->service = new ProductService($this->productRepository);
    }

    public function test_it_creates_product()
    {
        $data = [
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'price' => 99.99,
        ];

        $this->productRepository
            ->shouldReceive('first')
            ->once()
            ->andReturn(null); // SKU doesn't exist

        $this->productRepository
            ->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn(new Product($data));

        $response = $this->service->store($data);

        $this->assertTrue($response->getData()->status);
    }
}
```

## Related Documentation

- [Providers Documentation](./PROVIDERS.md) - Service registration
- [Repository Documentation](./REPOSITORIES.md) - Data access
- [API Documentation](./API.md) - Using services in controllers
- [Architecture Overview](./ARCHITECTURE.md) - System design

---

*Last Updated: 2025-11-25*