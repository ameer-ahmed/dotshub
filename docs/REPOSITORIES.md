# Repository Pattern Documentation

The Repository Pattern provides an abstraction layer between the business logic and data access, making the codebase more maintainable, testable, and flexible.

## Overview

Repositories in DotsHub:
- Abstract database operations
- Provide a consistent interface for data access
- Enable easy testing through mocking
- Centralize query logic
- Support the service layer

## Architecture

```
Service Layer
     ↓
Repository Interface (Contract)
     ↓
Repository Implementation (Eloquent)
     ↓
Eloquent Model
     ↓
Database
```

## Repository Structure

```
app/Repository/
├── Contracts/                          # Interfaces
│   ├── RepositoryInterface.php        # Base interface
│   ├── MerchantRepositoryInterface.php
│   ├── DomainRepositoryInterface.php
│   └── Tenant/                        # Tenant-specific interfaces
│       ├── UserRepositoryInterface.php
│       └── RoleRepositoryInterface.php
└── Eloquent/                           # Implementations
    ├── Repository.php                  # Base implementation
    ├── MerchantRepository.php
    ├── DomainRepository.php
    └── Tenant/                         # Tenant-specific implementations
        ├── UserRepository.php
        └── RoleRepository.php
```

## Base Repository

### RepositoryInterface

**Location:** `app/Repository/Contracts/RepositoryInterface.php`

Defines the contract that all repositories must implement.

```php
namespace App\Repository\Contracts;

interface RepositoryInterface
{
    // Read Operations
    public function getAll();
    public function getActive();
    public function getById($id);
    public function get(array $conditions = [], array $relations = [], array $orderBy = []);
    public function first(array $conditions = [], array $relations = []);
    public function getFirst(array $conditions = [], array $relations = [], array $orderBy = []);

    // Write Operations
    public function create(array $data);
    public function insert(array $data);
    public function createMany(array $data);
    public function update($model, array $data);
    public function delete($model);
    public function forceDelete($model);

    // Pagination
    public function paginate(int $perPage = 15);
    public function paginateWithQuery(array $conditions = [], int $perPage = 15, array $relations = [], array $orderBy = []);

    // Relationships
    public function whereHasMorph(string $relation, $relatedTypes, $callback = null);
}
```

### Repository Base Class

**Location:** `app/Repository/Eloquent/Repository.php`

Provides the base implementation for all repositories.

```php
namespace App\Repository\Eloquent;

use App\Repository\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class Repository implements RepositoryInterface
{
    protected Model $model;

    /**
     * Constructor - Inject the model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Get all records
     */
    public function getAll()
    {
        return $this->model->all();
    }

    /**
     * Get active records
     */
    public function getActive()
    {
        return $this->model->where('status', 'active')->get();
    }

    /**
     * Get record by ID
     */
    public function getById($id)
    {
        return $this->model->find($id);
    }

    /**
     * Get records with conditions
     *
     * @param array $conditions [['column', 'operator', 'value']]
     * @param array $relations  ['relation1', 'relation2']
     * @param array $orderBy    [['column', 'direction']]
     */
    public function get(array $conditions = [], array $relations = [], array $orderBy = [])
    {
        $query = $this->model->query();

        // Apply conditions
        foreach ($conditions as $condition) {
            if (count($condition) === 3) {
                [$column, $operator, $value] = $condition;
                $query->where($column, $operator, $value);
            }
        }

        // Eager load relationships
        if (!empty($relations)) {
            $query->with($relations);
        }

        // Apply ordering
        foreach ($orderBy as $order) {
            [$column, $direction] = $order;
            $query->orderBy($column, $direction);
        }

        return $query->get();
    }

    /**
     * Get first record matching conditions
     */
    public function first(array $conditions = [], array $relations = [])
    {
        $query = $this->model->query();

        foreach ($conditions as $condition) {
            if (count($condition) === 3) {
                [$column, $operator, $value] = $condition;
                $query->where($column, $operator, $value);
            }
        }

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->first();
    }

    /**
     * Get first record with ordering
     */
    public function getFirst(array $conditions = [], array $relations = [], array $orderBy = [])
    {
        $query = $this->model->query();

        foreach ($conditions as $condition) {
            if (count($condition) === 3) {
                [$column, $operator, $value] = $condition;
                $query->where($column, $operator, $value);
            }
        }

        if (!empty($relations)) {
            $query->with($relations);
        }

        foreach ($orderBy as $order) {
            [$column, $direction] = $order;
            $query->orderBy($column, $direction);
        }

        return $query->first();
    }

    /**
     * Create a new record
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Insert record without timestamps
     */
    public function insert(array $data)
    {
        return $this->model->insert($data);
    }

    /**
     * Create multiple records
     */
    public function createMany(array $data)
    {
        return $this->model->createMany($data);
    }

    /**
     * Update a record
     */
    public function update($model, array $data)
    {
        return $model->update($data);
    }

    /**
     * Soft delete a record
     */
    public function delete($model)
    {
        return $model->delete();
    }

    /**
     * Permanently delete a record
     */
    public function forceDelete($model)
    {
        return $model->forceDelete();
    }

    /**
     * Paginate all records
     */
    public function paginate(int $perPage = 15)
    {
        return $this->model->paginate($perPage);
    }

    /**
     * Paginate with query conditions
     */
    public function paginateWithQuery(
        array $conditions = [],
        int $perPage = 15,
        array $relations = [],
        array $orderBy = []
    ) {
        $query = $this->model->query();

        foreach ($conditions as $condition) {
            if (count($condition) === 3) {
                [$column, $operator, $value] = $condition;
                $query->where($column, $operator, $value);
            }
        }

        if (!empty($relations)) {
            $query->with($relations);
        }

        foreach ($orderBy as $order) {
            [$column, $direction] = $order;
            $query->orderBy($column, $direction);
        }

        return $query->paginate($perPage);
    }

    /**
     * Query polymorphic relationships
     */
    public function whereHasMorph(string $relation, $relatedTypes, $callback = null)
    {
        return $this->model->whereHasMorph($relation, $relatedTypes, $callback)->get();
    }
}
```

## Existing Repositories

### 1. MerchantRepository

**Interface:** `app/Repository/Contracts/MerchantRepositoryInterface.php`

```php
namespace App\Repository\Contracts;

interface MerchantRepositoryInterface extends RepositoryInterface
{
    // Add merchant-specific methods here if needed
}
```

**Implementation:** `app/Repository/Eloquent/MerchantRepository.php`

```php
namespace App\Repository\Eloquent;

use App\Models\Merchant;
use App\Repository\Contracts\MerchantRepositoryInterface;

class MerchantRepository extends Repository implements MerchantRepositoryInterface
{
    public function __construct(Merchant $model)
    {
        parent::__construct($model);
    }

    // Add custom methods if needed
}
```

### 2. DomainRepository

**Interface:** `app/Repository/Contracts/DomainRepositoryInterface.php`

```php
namespace App\Repository\Contracts;

interface DomainRepositoryInterface extends RepositoryInterface
{
    //
}
```

**Implementation:** `app/Repository/Eloquent/DomainRepository.php`

```php
namespace App\Repository\Eloquent;

use App\Models\Domain;
use App\Repository\Contracts\DomainRepositoryInterface;

class DomainRepository extends Repository implements DomainRepositoryInterface
{
    public function __construct(Domain $model)
    {
        parent::__construct($model);
    }
}
```

### 3. User Repository (Tenant)

**Interface:** `app/Repository/Contracts/Tenant/UserRepositoryInterface.php`

```php
namespace App\Repository\Contracts\Tenant;

use App\Repository\Contracts\RepositoryInterface;

interface UserRepositoryInterface extends RepositoryInterface
{
    public function findByEmail(string $email);
}
```

**Implementation:** `app/Repository/Eloquent/Tenant/UserRepository.php`

```php
namespace App\Repository\Eloquent\Tenant;

use App\Models\Tenant\User;
use App\Repository\Contracts\Tenant\UserRepositoryInterface;
use App\Repository\Eloquent\Repository;

class UserRepository extends Repository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email)
    {
        return $this->model->where('email', $email)->first();
    }
}
```

### 4. Role Repository (Tenant)

**Interface:** `app/Repository/Contracts/Tenant/RoleRepositoryInterface.php`

```php
namespace App\Repository\Contracts\Tenant;

use App\Repository\Contracts\RepositoryInterface;

interface RoleRepositoryInterface extends RepositoryInterface
{
    public function findByName(string $name);
    public function getByMerchant(string $merchantId);
}
```

**Implementation:** `app/Repository/Eloquent/Tenant/RoleRepository.php`

```php
namespace App\Repository\Eloquent\Tenant;

use App\Models\Tenant\Role;
use App\Repository\Contracts\Tenant\RoleRepositoryInterface;
use App\Repository\Eloquent\Repository;

class RoleRepository extends Repository implements RoleRepositoryInterface
{
    public function __construct(Role $model)
    {
        parent::__construct($model);
    }

    /**
     * Find role by name
     */
    public function findByName(string $name)
    {
        return $this->model->where('name', $name)->first();
    }

    /**
     * Get all roles for a merchant
     */
    public function getByMerchant(string $merchantId)
    {
        return $this->get([
            ['merchant_id', '=', $merchantId]
        ]);
    }
}
```

## Creating a New Repository

### Step 1: Use Artisan Command

```bash
php artisan make:repo Product
```

This generates:
- `app/Repository/Contracts/ProductRepositoryInterface.php`
- `app/Repository/Eloquent/ProductRepository.php`

### Step 2: Define Interface Methods

Edit `app/Repository/Contracts/ProductRepositoryInterface.php`:

```php
namespace App\Repository\Contracts;

interface ProductRepositoryInterface extends RepositoryInterface
{
    /**
     * Find product by SKU
     */
    public function findBySku(string $sku);

    /**
     * Get products by category
     */
    public function getByCategory(int $categoryId);

    /**
     * Get low stock products
     */
    public function getLowStock(int $threshold = 10);

    /**
     * Search products by name
     */
    public function search(string $query);
}
```

### Step 3: Implement Repository

Edit `app/Repository/Eloquent/ProductRepository.php`:

```php
namespace App\Repository\Eloquent;

use App\Models\Tenant\Product;
use App\Repository\Contracts\ProductRepositoryInterface;

class ProductRepository extends Repository implements ProductRepositoryInterface
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    /**
     * Find product by SKU
     */
    public function findBySku(string $sku)
    {
        return $this->model->where('sku', $sku)->first();
    }

    /**
     * Get products by category
     */
    public function getByCategory(int $categoryId)
    {
        return $this->get([
            ['category_id', '=', $categoryId],
            ['status', '=', 'active']
        ], ['category'], [['created_at', 'desc']]);
    }

    /**
     * Get low stock products
     */
    public function getLowStock(int $threshold = 10)
    {
        return $this->model
            ->where('quantity', '<=', $threshold)
            ->where('status', 'active')
            ->orderBy('quantity', 'asc')
            ->get();
    }

    /**
     * Search products by name
     */
    public function search(string $query)
    {
        return $this->model
            ->where('name', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->orWhere('sku', 'like', "%{$query}%")
            ->get();
    }
}
```

### Step 4: Register in RepositoryServiceProvider

Edit `app/Providers/RepositoryServiceProvider.php`:

```php
public function register(): void
{
    // Existing bindings...

    // Register Product Repository
    $this->app->bind(
        \App\Repository\Contracts\ProductRepositoryInterface::class,
        \App\Repository\Eloquent\ProductRepository::class
    );
}
```

### Step 5: Use in Service

```php
namespace App\Http\Services\V1\Web\Merchant\Product;

use App\Repository\Contracts\ProductRepositoryInterface;

class ProductService extends ProductAbstractService
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository
    ) {}

    public function searchProducts(string $query)
    {
        $products = $this->productRepository->search($query);

        return Responser::success($products, 'Products found');
    }

    public function getLowStockAlert()
    {
        $products = $this->productRepository->getLowStock(5);

        return Responser::success($products, 'Low stock products');
    }
}
```

## Repository Usage Examples

### Basic CRUD Operations

```php
// Get all records
$products = $this->productRepository->getAll();

// Get by ID
$product = $this->productRepository->getById(1);

// Create
$product = $this->productRepository->create([
    'name' => 'New Product',
    'sku' => 'PRD-001',
    'price' => 99.99,
]);

// Update
$this->productRepository->update($product, [
    'price' => 89.99,
]);

// Delete (soft delete)
$this->productRepository->delete($product);

// Force delete
$this->productRepository->forceDelete($product);
```

### Query with Conditions

```php
// Single condition
$products = $this->productRepository->get([
    ['status', '=', 'active']
]);

// Multiple conditions
$products = $this->productRepository->get([
    ['category_id', '=', 5],
    ['price', '>', 50],
    ['quantity', '>', 0]
]);

// With relationships
$products = $this->productRepository->get(
    [['status', '=', 'active']],
    ['category', 'images']
);

// With ordering
$products = $this->productRepository->get(
    [['status', '=', 'active']],
    ['category'],
    [['created_at', 'desc']]
);
```

### First Record Queries

```php
// First matching record
$product = $this->productRepository->first([
    ['sku', '=', 'PRD-001']
]);

// First with relationships
$product = $this->productRepository->first(
    [['sku', '=', 'PRD-001']],
    ['category', 'images']
);

// First with ordering
$latestProduct = $this->productRepository->getFirst(
    [['status', '=', 'active']],
    ['category'],
    [['created_at', 'desc']]
);
```

### Pagination

```php
// Simple pagination
$products = $this->productRepository->paginate(20);

// Pagination with conditions
$products = $this->productRepository->paginateWithQuery(
    [['category_id', '=', 5]],
    20,
    ['category'],
    [['name', 'asc']]
);
```

### Bulk Operations

```php
// Create many
$products = $this->productRepository->createMany([
    ['name' => 'Product 1', 'sku' => 'PRD-001'],
    ['name' => 'Product 2', 'sku' => 'PRD-002'],
]);

// Insert (no timestamps)
$this->productRepository->insert([
    ['name' => 'Product 1', 'sku' => 'PRD-001'],
    ['name' => 'Product 2', 'sku' => 'PRD-002'],
]);
```

## Advanced Repository Methods

### Complex Queries

Add custom methods for complex queries:

```php
public function getProductsWithLowStockInCategory(int $categoryId, int $threshold = 10)
{
    return $this->model
        ->where('category_id', $categoryId)
        ->where('quantity', '<=', $threshold)
        ->where('status', 'active')
        ->with(['category'])
        ->orderBy('quantity', 'asc')
        ->get();
}

public function getTopSellingProducts(int $limit = 10)
{
    return $this->model
        ->withCount('orderItems')
        ->orderBy('order_items_count', 'desc')
        ->limit($limit)
        ->get();
}

public function getPriceRange()
{
    return [
        'min' => $this->model->min('price'),
        'max' => $this->model->max('price'),
        'avg' => $this->model->avg('price'),
    ];
}
```

### Aggregation Methods

```php
public function getTotalInventoryValue()
{
    return $this->model
        ->selectRaw('SUM(price * quantity) as total_value')
        ->value('total_value');
}

public function getProductCountByCategory()
{
    return $this->model
        ->selectRaw('category_id, COUNT(*) as count')
        ->groupBy('category_id')
        ->get();
}
```

### Relationship Queries

```php
public function getProductsWithImages()
{
    return $this->model
        ->has('images')
        ->with('images')
        ->get();
}

public function getProductsByTag(string $tag)
{
    return $this->model
        ->whereHas('tags', function ($query) use ($tag) {
            $query->where('name', $tag);
        })
        ->with('tags')
        ->get();
}
```

## Repository Best Practices

### 1. Keep Repositories Focused

One repository per model:
- ✅ `ProductRepository` - handles Product model
- ✅ `CategoryRepository` - handles Category model
- ❌ `ProductCategoryRepository` - handles both (bad)

### 2. Use Type Hints

```php
public function findBySku(string $sku): ?Product
{
    return $this->model->where('sku', $sku)->first();
}

public function getPriceRange(): array
{
    return [
        'min' => $this->model->min('price'),
        'max' => $this->model->max('price'),
    ];
}
```

### 3. Return Consistent Types

```php
// Returns Model or null
public function getById(int $id): ?Model
{
    return $this->model->find($id);
}

// Returns Collection
public function getAll(): Collection
{
    return $this->model->all();
}

// Returns LengthAwarePaginator
public function paginate(int $perPage = 15): LengthAwarePaginator
{
    return $this->model->paginate($perPage);
}
```

### 4. Avoid Business Logic

Repositories should only handle data access, not business rules:

**Bad:**
```php
public function createProduct(array $data)
{
    // Business logic doesn't belong here
    if ($data['price'] < $data['cost']) {
        throw new Exception('Price cannot be lower than cost');
    }

    return $this->model->create($data);
}
```

**Good:**
```php
// Repository - just data access
public function create(array $data)
{
    return $this->model->create($data);
}

// Service - business logic
public function createProduct(array $data)
{
    // Business rule in service
    if ($data['price'] < $data['cost']) {
        return Responser::fail('Price cannot be lower than cost');
    }

    return $this->productRepository->create($data);
}
```

### 5. Use Query Builder for Complex Queries

```php
public function getProductReport(array $filters)
{
    $query = $this->model->query();

    if (isset($filters['category_id'])) {
        $query->where('category_id', $filters['category_id']);
    }

    if (isset($filters['min_price'])) {
        $query->where('price', '>=', $filters['min_price']);
    }

    if (isset($filters['max_price'])) {
        $query->where('price', '<=', $filters['max_price']);
    }

    if (isset($filters['search'])) {
        $query->where(function ($q) use ($filters) {
            $q->where('name', 'like', "%{$filters['search']}%")
              ->orWhere('sku', 'like', "%{$filters['search']}%");
        });
    }

    return $query->get();
}
```

### 6. Eager Load Relationships

Prevent N+1 queries by eager loading:

```php
// Bad - N+1 queries
public function getAllProducts()
{
    return $this->model->all();
    // Later: $product->category causes new query for each product
}

// Good - eager loading
public function getAllProducts()
{
    return $this->model->with(['category', 'images'])->get();
}
```

## Testing Repositories

```php
namespace Tests\Unit\Repositories;

use Tests\TestCase;
use App\Models\Tenant\Product;
use App\Repository\Eloquent\ProductRepository;

class ProductRepositoryTest extends TestCase
{
    protected ProductRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ProductRepository(new Product());
    }

    public function test_it_creates_product()
    {
        $data = [
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'price' => 99.99,
        ];

        $product = $this->repository->create($data);

        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals('Test Product', $product->name);
    }

    public function test_it_finds_by_sku()
    {
        Product::factory()->create(['sku' => 'TEST-SKU']);

        $product = $this->repository->findBySku('TEST-SKU');

        $this->assertNotNull($product);
        $this->assertEquals('TEST-SKU', $product->sku);
    }

    public function test_it_gets_low_stock_products()
    {
        Product::factory()->create(['quantity' => 5]);
        Product::factory()->create(['quantity' => 50]);

        $products = $this->repository->getLowStock(10);

        $this->assertCount(1, $products);
        $this->assertEquals(5, $products->first()->quantity);
    }
}
```

## Related Documentation

- [Services Documentation](./SERVICES.md) - Using repositories in services
- [Providers Documentation](./PROVIDERS.md) - Registering repositories
- [Architecture Overview](./ARCHITECTURE.md) - System design
- [Models Documentation](./MODELS.md) - Eloquent models

---

*Last Updated: 2025-11-25*