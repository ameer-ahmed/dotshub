# Custom Artisan Commands

DotsHub provides custom Artisan commands to accelerate development by automatically generating boilerplate code with proper structure and conventions.

## Overview

Custom commands available:
1. `make:platform-service` - Generate complete feature scaffolding
2. `make:repo` - Generate repository pattern implementation

These commands automate the creation of:
- Models
- Repositories (Interface + Implementation)
- Services (Abstract + Platform-specific)
- Controllers
- Form Requests
- API Resources
- Service Provider registrations

---

## make:platform-service

Generates a complete feature scaffold following the platform abstraction pattern, including model, repository, abstract service, platform-specific implementations, controller, request, and resource.

### Signature

```bash
php artisan make:platform-service {name} [options]
```

### Arguments

| Argument | Description | Example |
|----------|-------------|---------|
| `name` | Base name for the feature (StudlyCase) | `Product`, `Order`, `Customer` |

### Options

| Option | Default | Description |
|--------|---------|-------------|
| `--actor` | `Merchant` | Actor/context folder (User, Admin, Merchant) |
| `--domain` | Same as name | Domain folder under actor |
| `--api-version` | `1` | API version number |
| `--platform` | `web` | Comma-separated platforms (web,mobile) |
| `--no-controller` | `false` | Skip generating controllers |
| `--no-request` | `false` | Skip generating form request |
| `--no-resource` | `false` | Skip generating API resource |
| `--no-model` | `false` | Skip generating model |
| `--no-repo` | `false` | Skip generating repository |

### What It Creates

#### 1. Model (if not skipped)
**Location:** `app/Models/{Name}.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $guarded = [];
}
```

#### 2. Repository Interface
**Location:** `app/Repository/{Name}RepositoryInterface.php`

```php
<?php

namespace App\Repository;

interface ProductRepositoryInterface extends \App\Repository\Contracts\RepositoryInterface
{
}
```

#### 3. Repository Implementation
**Location:** `app/Repository/Eloquent/{Name}Repository.php`

```php
<?php

namespace App\Repository\Eloquent;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use App\Repository\Eloquent\Repository;
use App\Repository\ProductRepositoryInterface;

class ProductRepository extends Repository implements ProductRepositoryInterface
{
    protected Model $model;

    public function __construct(Product $model)
    {
        parent::__construct($model);
    }
}
```

#### 4. Abstract Service
**Location:** `app/Http/Services/V{version}/Abstracts/{Actor}/{Domain}/{Name}AbstractService.php`

```php
<?php

namespace App\Http\Services\V1\Abstracts\Merchant\Product;

use App\Http\Services\PlatformService;

abstract class ProductAbstractService extends PlatformService
{
    // Empty abstract service
}
```

#### 5. Platform-Specific Service(s)
**Location:** `app/Http/Services/V{version}/{Platform}/{Actor}/{Domain}/{Name}Service.php`

```php
<?php

namespace App\Http\Services\V1\Web\Merchant\Product;

use App\Enums\Platform;
use App\Http\Services\V1\Abstracts\Merchant\Product\ProductAbstractService;

class ProductService extends ProductAbstractService
{
    public static function platform(): Platform
    {
        return Platform::WEB;
    }
}
```

#### 6. Controller (if not skipped)
**Location:** `app/Http/Controllers/V{version}/{Platform}/{Actor}/{Domain}/{Name}Controller.php`

```php
<?php

namespace App\Http\Controllers\V1\Web\Merchant\Product;

use App\Http\Controllers\Controller;
use App\Http\Services\V1\Web\Merchant\Product\ProductService;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService,
    ) {}
}
```

#### 7. Form Request (if not skipped)
**Location:** `app/Http/Requests/V{version}/{Platform}/{Actor}/{Domain}/{Name}Request.php`

```php
<?php

namespace App\Http\Requests\V1\Web\Merchant\Product;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // TODO: rules
        ];
    }
}
```

#### 8. API Resource (if not skipped)
**Location:** `app/Http/Resources/V{version}/{Platform}/{Actor}/{Domain}/{Name}Resource.php`

```php
<?php

namespace App\Http\Resources\V1\Web\Merchant\Product;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            // TODO: map fields
        ];
    }
}
```

#### 9. Automatic Service Provider Registration

The command automatically updates:

**PlatformServiceProvider.php:**
- Adds binding for abstract service to platform implementations
- Updates `bindServices()` method
- Updates `getConcreteImplementations()` match statement

**RepositoryServiceProvider.php:**
- Adds binding for repository interface to implementation
- Adds use statements
- Updates `register()` method

### Usage Examples

#### Basic Usage

Generate a complete Product feature for Merchant:

```bash
php artisan make:platform-service Product --actor=Merchant --domain=Product
```

**Creates:**
```
app/Models/Product.php
app/Repository/ProductRepositoryInterface.php
app/Repository/Eloquent/ProductRepository.php
app/Http/Services/V1/Abstracts/Merchant/Product/ProductAbstractService.php
app/Http/Services/V1/Web/Merchant/Product/ProductService.php
app/Http/Controllers/V1/Web/Merchant/Product/ProductController.php
app/Http/Requests/V1/Web/Merchant/Product/ProductRequest.php
app/Http/Resources/V1/Web/Merchant/Product/ProductResource.php
```

#### Multiple Platforms

Generate for both Web and Mobile:

```bash
php artisan make:platform-service Order \
  --actor=Merchant \
  --domain=Order \
  --platform=web,mobile
```

**Creates services and controllers for both platforms:**
```
app/Http/Services/V1/Web/Merchant/Order/OrderService.php
app/Http/Services/V1/Mobile/Merchant/Order/OrderService.php
app/Http/Controllers/V1/Web/Merchant/Order/OrderController.php
app/Http/Controllers/V1/Mobile/Merchant/Order/OrderController.php
```

#### Admin Context

Generate an admin feature:

```bash
php artisan make:platform-service User \
  --actor=Admin \
  --domain=User
```

**Creates:**
```
app/Http/Services/V1/Abstracts/Admin/User/UserAbstractService.php
app/Http/Services/V1/Web/Admin/User/UserService.php
app/Http/Controllers/V1/Web/Admin/User/UserController.php
```

#### Skip Components

Generate only service layer (no controller, request, resource):

```bash
php artisan make:platform-service Category \
  --actor=Merchant \
  --domain=Category \
  --no-controller \
  --no-request \
  --no-resource
```

#### Use Existing Model

Generate without creating a new model:

```bash
php artisan make:platform-service Product \
  --actor=Merchant \
  --domain=Product \
  --no-model
```

#### API Version 2

Generate for API v2:

```bash
php artisan make:platform-service Payment \
  --actor=Merchant \
  --domain=Payment \
  --api-version=2
```

**Creates:**
```
app/Http/Services/V2/Abstracts/Merchant/Payment/PaymentAbstractService.php
app/Http/Services/V2/Web/Merchant/Payment/PaymentService.php
app/Http/Controllers/V2/Web/Merchant/Payment/PaymentController.php
```

### Real-World Example

Let's create a complete Inventory feature:

```bash
php artisan make:platform-service Inventory \
  --actor=Merchant \
  --domain=Inventory \
  --platform=web,mobile
```

**Step-by-step what happens:**

1. **Creates Model:**
```php
// app/Models/Inventory.php
class Inventory extends Model
{
    use HasFactory;
    protected $guarded = [];
}
```

2. **Creates Repository:**
```php
// Interface
interface InventoryRepositoryInterface extends RepositoryInterface {}

// Implementation
class InventoryRepository extends Repository implements InventoryRepositoryInterface
{
    public function __construct(Inventory $model)
    {
        parent::__construct($model);
    }
}
```

3. **Creates Abstract Service:**
```php
// app/Http/Services/V1/Abstracts/Merchant/Inventory/InventoryAbstractService.php
abstract class InventoryAbstractService extends PlatformService
{
    // Define your methods here
}
```

4. **Creates Platform Services:**
```php
// Web
class InventoryService extends InventoryAbstractService
{
    public static function platform(): Platform
    {
        return Platform::WEB;
    }
}

// Mobile
class InventoryService extends InventoryAbstractService
{
    public static function platform(): Platform
    {
        return Platform::MOBILE;
    }
}
```

5. **Registers Everything:**
Updates `PlatformServiceProvider` and `RepositoryServiceProvider` automatically.

### After Generation

After running the command, you typically need to:

1. **Add methods to Abstract Service:**
```php
abstract class InventoryAbstractService extends PlatformService
{
    abstract public function index();
    abstract public function store(array $data);
    abstract public function update(int $id, array $data);
    abstract public function delete(int $id);
}
```

2. **Implement methods in Platform Services:**
```php
class InventoryService extends InventoryAbstractService
{
    public function __construct(
        protected InventoryRepositoryInterface $inventoryRepository
    ) {}

    public function index()
    {
        $items = $this->inventoryRepository->getAll();
        return Responser::success($items);
    }

    // ... implement other methods
}
```

3. **Add controller methods:**
```php
class InventoryController extends Controller
{
    public function index()
    {
        return $this->inventoryService->index();
    }

    public function store(InventoryRequest $request)
    {
        return $this->inventoryService->store($request->validated());
    }
}
```

4. **Define validation rules:**
```php
class InventoryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:0',
            'location' => 'required|string|max:255',
        ];
    }
}
```

5. **Map resource fields:**
```php
class InventoryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product' => new ProductResource($this->whenLoaded('product')),
            'quantity' => $this->quantity,
            'location' => $this->location,
            'created_at' => $this->created_at,
        ];
    }
}
```

6. **Create migration:**
```bash
php artisan make:migration create_inventories_table --path=database/migrations/tenant
```

7. **Define routes:**
```php
// routes/api/v1/web/merchant/inventory.php
Route::middleware(['auth:user'])->group(function () {
    Route::get('inventory', [InventoryController::class, 'index']);
    Route::post('inventory', [InventoryController::class, 'store']);
    Route::put('inventory/{id}', [InventoryController::class, 'update']);
    Route::delete('inventory/{id}', [InventoryController::class, 'destroy']);
});
```

---

## make:repo

Generates a repository pattern implementation (interface + Eloquent class) and automatically registers it in `RepositoryServiceProvider`.

### Signature

```bash
php artisan make:repo {name} [options]
```

### Arguments

| Argument | Description | Example |
|----------|-------------|---------|
| `name` | Repository base name (StudlyCase) | `Product`, `Category`, `Order` |

### Options

| Option | Description |
|--------|-------------|
| `--model={FQCN}` | Specify custom model (FQCN or short name) |
| `--tenant` | Generate for tenant context (Models\Tenant\*) |
| `--force` | Overwrite existing files |

### What It Creates

#### 1. Repository Interface
**Central:** `app/Repository/Contracts/{Name}RepositoryInterface.php`
**Tenant:** `app/Repository/Contracts/Tenant/{Name}RepositoryInterface.php`

```php
<?php

namespace App\Repository\Contracts;

interface ProductRepositoryInterface extends \App\Repository\Contracts\RepositoryInterface
{
}
```

#### 2. Repository Implementation
**Central:** `app/Repository/Eloquent/{Name}Repository.php`
**Tenant:** `app/Repository/Eloquent/Tenant/{Name}Repository.php`

```php
<?php

namespace App\Repository\Eloquent;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use App\Repository\Eloquent\Repository;
use App\Repository\Contracts\ProductRepositoryInterface;

class ProductRepository extends Repository implements ProductRepositoryInterface
{
    protected Model $model;

    public function __construct(Product $model)
    {
        parent::__construct($model);
    }
}
```

#### 3. Automatic Provider Registration

Updates `RepositoryServiceProvider`:
- Adds `use` statements for interface and implementation
- Adds singleton binding in `register()` method

```php
$this->app->singleton(ProductRepositoryInterface::class, ProductRepository::class);
```

### Usage Examples

#### Basic Repository (Central Database)

```bash
php artisan make:repo Product
```

**Creates:**
```
app/Repository/Contracts/ProductRepositoryInterface.php
app/Repository/Eloquent/ProductRepository.php
```

**Assumes model:** `App\Models\Product`

#### Tenant Repository

```bash
php artisan make:repo User --tenant
```

**Creates:**
```
app/Repository/Contracts/Tenant/UserRepositoryInterface.php
app/Repository/Eloquent/Tenant/UserRepository.php
```

**Assumes model:** `App\Models\Tenant\User`

#### Custom Model Path

```bash
php artisan make:repo Order --model=App\Models\Tenant\Order
```

#### Short Model Name

```bash
php artisan make:repo Category --model=Category --tenant
```

Resolves to: `App\Models\Tenant\Category`

#### Overwrite Existing

```bash
php artisan make:repo Product --force
```

### After Generation

1. **Add custom methods to interface:**
```php
interface ProductRepositoryInterface extends RepositoryInterface
{
    public function findBySku(string $sku);
    public function getByCategory(int $categoryId);
    public function getLowStock(int $threshold = 10);
}
```

2. **Implement methods in repository:**
```php
class ProductRepository extends Repository implements ProductRepositoryInterface
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function findBySku(string $sku)
    {
        return $this->model->where('sku', $sku)->first();
    }

    public function getByCategory(int $categoryId)
    {
        return $this->get([
            ['category_id', '=', $categoryId],
            ['status', '=', 'active']
        ]);
    }

    public function getLowStock(int $threshold = 10)
    {
        return $this->model
            ->where('quantity', '<=', $threshold)
            ->where('status', 'active')
            ->get();
    }
}
```

3. **Use in service:**
```php
class ProductService extends ProductAbstractService
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository
    ) {}

    public function findBySku(string $sku)
    {
        $product = $this->productRepository->findBySku($sku);

        if (!$product) {
            return Responser::fail('Product not found', 404);
        }

        return Responser::success(new ProductResource($product));
    }
}
```

---

## Comparison: When to Use Which Command

### Use `make:platform-service` when:
- Creating a complete new feature
- Need controller, service, repository, request, and resource
- Want platform abstraction (Web/Mobile)
- Building CRUD operations
- Starting from scratch

**Example:**
```bash
php artisan make:platform-service Product --actor=Merchant --domain=Product
```

### Use `make:repo` when:
- Only need a repository
- Service/controller already exists
- Working with existing models
- Adding data access layer to legacy code
- Creating utility repositories

**Example:**
```bash
php artisan make:repo Product --tenant
```

---

## Best Practices

### 1. Consistent Naming

Use StudlyCase for names:
```bash
# Good
php artisan make:platform-service ProductCategory
php artisan make:repo OrderItem

# Bad
php artisan make:platform-service product_category
php artisan make:repo order-item
```

### 2. Organize by Domain

Use meaningful actor and domain names:
```bash
# Good - clear organization
php artisan make:platform-service Product --actor=Merchant --domain=Catalog
php artisan make:platform-service Order --actor=Merchant --domain=Sales

# Avoid - too generic
php artisan make:platform-service Product --actor=Merchant --domain=Product
```

### 3. Tenant vs Central

Always specify `--tenant` for tenant-specific features:
```bash
# Tenant feature
php artisan make:repo User --tenant

# Central feature
php artisan make:repo Admin
```

### 4. Use Multiple Platforms Sparingly

Only generate for multiple platforms if implementations differ:
```bash
# Good - different mobile/web implementations
php artisan make:platform-service Payment --platform=web,mobile

# Overkill - if implementations are identical
php artisan make:platform-service Setting --platform=web,mobile
```

### 5. Skip Unnecessary Components

Don't generate what you don't need:
```bash
# Only need service layer
php artisan make:platform-service Report \
  --no-model \
  --no-controller \
  --no-request \
  --no-resource
```

---

## Troubleshooting

### Issue: "PlatformServiceProvider.php not found"

**Cause:** Provider doesn't exist or wrong path

**Solution:** Ensure `app/Providers/PlatformServiceProvider.php` exists

### Issue: "RepositoryServiceProvider.php not found"

**Cause:** Provider doesn't exist

**Solution:** Ensure `app/Providers/RepositoryServiceProvider.php` exists

### Issue: Model not found after generation

**Cause:** Model namespace doesn't match expectation

**Solution:** Use `--model` option to specify correct namespace:
```bash
php artisan make:platform-service Order --model=App\Models\Tenant\Order
```

### Issue: Files already exist

**Cause:** Running command twice

**Solution:** Use `--force` to overwrite:
```bash
php artisan make:repo Product --force
```

### Issue: Provider registration not working

**Cause:** Syntax error in provider file

**Solution:**
1. Check `app/Providers/RepositoryServiceProvider.php` for syntax errors
2. Ensure `register()` method exists
3. Manually add binding if needed

---

## Related Documentation

- [Services Documentation](./SERVICES.md) - How to use generated services
- [Repository Documentation](./REPOSITORIES.md) - Repository pattern details
- [Architecture](./ARCHITECTURE.md) - Understanding the structure
- [Getting Started](./GETTING_STARTED.md) - Development workflow

---

*Last Updated: 2025-11-25*