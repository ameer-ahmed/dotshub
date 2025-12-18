# Platform Architecture Documentation

## Overview
This application uses a platform-aware architecture that dynamically resolves services and requests based on the `X-Platform` header sent with each API request.

## Key Changes

### 1. Platform Detection
**Location:** `app/Providers/PlatformServiceProvider.php:22-66`

- **Header-based:** Platform is detected from the `X-Platform` HTTP header (values: `web`, `mobile`)
- **URL-based version:** API version is detected from URL pattern `api/v1/*`
- **Strict validation:** Throws exceptions for invalid platform/version (no fallbacks)
- **Console mode:** Defaults to first platform when running Artisan commands

### 2. Controller Structure
Controllers are **NOT** separated by platform folders:

```
app/Http/Controllers/V1/
├── Admin/
│   └── Auth/
│       └── AuthController.php
└── Merchant/
    ├── Auth/
    │   └── AuthController.php
    └── Role/
        └── RoleController.php
```

Controllers inject **abstract** services and requests:
```php
public function __construct(
    private readonly AuthAbstractService $authService,
) {}

public function signIn(SignInAbstractRequest $request) {
    return $this->authService->signIn($request);
}
```

### 3. Request Structure
Requests follow the same pattern as services:

**Abstract Requests** (shared validation logic):
```
app/Http/Requests/V1/Abstracts/
├── Admin/
│   └── Auth/
│       ├── SignInAbstractRequest.php
│       └── SignUpAbstractRequest.php
└── Merchant/
    ├── Auth/
    │   ├── SignInAbstractRequest.php
    │   └── SignUpAbstractRequest.php
    └── Role/
        └── RoleAbstractRequest.php
```

**Platform-Specific Requests** (Web/Mobile):
```
app/Http/Requests/V1/
├── Web/
│   ├── Admin/
│   │   └── Auth/
│   │       ├── SignInRequest.php  (extends SignInAbstractRequest)
│   │       └── SignUpRequest.php  (extends SignUpAbstractRequest)
│   └── Merchant/...
└── Mobile/
    └── (future implementations)
```

Each concrete request declares its platform:
```php
class SignInRequest extends SignInAbstractRequest
{
    public static function platform(): Platform
    {
        return Platform::WEB;
    }
}
```

### 4. Route Structure
Routes are **NOT** separated by platform:

```
routes/api/v1/
├── admin.php
└── merchant.php
```

**URL Pattern:** `api/v1/{actor}/{endpoint}`

Examples:
- `api/v1/admin/...`
- `api/v1/merchant/auth/sign/in`
- `api/v1/merchant/roles`

### 5. Request Flow

```
Client Request
├── URL: POST api/v1/merchant/auth/signin
└── Header: X-Platform: web
         ↓
PlatformServiceProvider (constructor)
├── Detects version: 1 (from URL)
└── Detects platform: WEB (from header)
         ↓
Container Resolution (during injection)
├── AuthAbstractService → AuthService (Web)
└── SignInAbstractRequest → SignInRequest (Web)
         ↓
Controller Method Invoked
└── AuthController->signIn(SignInRequest $request)
         ↓
Service Method
└── AuthService->signIn($request)
```

## Making API Requests

### Required Headers
```bash
X-Platform: web  # or 'mobile'
```

### Example with cURL
```bash
curl -X POST http://localhost/api/v1/merchant/auth/signin \
  -H "X-Platform: web" \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password123"}'
```

### Example with Postman
1. Set URL: `POST http://localhost/api/v1/merchant/auth/signin`
2. Add Header: `X-Platform: web`
3. Add JSON body

### Error Cases
- **Missing X-Platform header:** `InvalidArgumentException: X-Platform header is required`
- **Invalid platform:** `InvalidArgumentException: Invalid platform 'xyz'. Supported platforms: web`
- **Invalid version:** `InvalidArgumentException: Invalid API version. Supported versions: 1`

## Artisan Commands

### Generate Complete Service Stack
```bash
php artisan make:platform-service Product \
  --actor=Merchant \
  --domain=Catalog \
  --platform=web,mobile
```

Creates:
- Model: `app/Models/Product.php`
- Repository Interface: `app/Repository/Contracts/ProductRepositoryInterface.php`
- Repository Eloquent: `app/Repository/Eloquent/ProductRepository.php`
- Abstract Service: `app/Http/Services/V1/Abstracts/Merchant/Catalog/ProductAbstractService.php`
- Concrete Services: `app/Http/Services/V1/Web/Merchant/Catalog/ProductService.php`
- Controllers: `app/Http/Controllers/V1/Merchant/Catalog/ProductController.php`
- Abstract Request: `app/Http/Requests/V1/Abstracts/Merchant/Catalog/ProductAbstractRequest.php`
- Concrete Requests: `app/Http/Requests/V1/Web/Merchant/Catalog/ProductRequest.php`
- Resource: `app/Http/Resources/V1/Web/Merchant/Catalog/ProductResource.php`

Options:
- `--tenant` : Generate for tenant models
- `--no-controller` : Skip controller generation
- `--no-request` : Skip request generation
- `--no-resource` : Skip resource generation
- `--no-model` : Skip model generation
- `--no-repo` : Skip repository generation

### Generate Standalone Request
```bash
php artisan make:platform-request UpdateProfile \
  --actor=Merchant \
  --domain=User \
  --platform=web,mobile
```

Creates:
- Abstract: `app/Http/Requests/V1/Abstracts/Merchant/User/UpdateProfileAbstractRequest.php`
- Concrete: `app/Http/Requests/V1/Web/Merchant/User/UpdateProfileRequest.php`

## Adding New Platforms

### 1. Add Platform to Enum
`app/Enums/Platform.php`
```php
enum Platform: string
{
    case WEB = 'web';
    case MOBILE = 'mobile';  // Add new platforms here
}
```

### 2. Generate Platform-Specific Implementations
```bash
php artisan make:platform-service YourService \
  --platform=web,mobile
```

### 3. Test with Header
```bash
curl -H "X-Platform: mobile" http://localhost/api/v1/...
```

## Benefits

1. **Single Controller Set:** No duplication across platforms
2. **Shared Validation Logic:** Abstract requests contain common rules
3. **Platform-Specific Behavior:** Easy to override in concrete classes
4. **Type Safety:** Controllers type-hint abstracts, container resolves concretes
5. **Automatic Resolution:** Platform detection and binding happens transparently
6. **Easy Testing:** Can mock abstracts or test concrete implementations
7. **Scalable:** Add new platforms without touching existing code

## Migration Guide (from old structure)

### Controllers
- **Before:** `app/Http/Controllers/V1/Web/Merchant/Auth/AuthController.php`
- **After:** `app/Http/Controllers/V1/Merchant/Auth/AuthController.php`
- Change: Inject `AuthAbstractService` instead of `AuthService`
- Change: Inject `SignInAbstractRequest` instead of `SignInRequest`

### Routes
- **Before:** `routes/api/v1/web/merchant.php`
- **After:** `routes/api/v1/merchant.php`
- Change: Remove `/web/` prefix from URLs
- Change: Update use statements to point to new controller locations

### API Clients
- **Before:** `POST /api/v1/web/merchant/auth/signin`
- **After:** `POST /api/v1/merchant/auth/signin` + Header: `X-Platform: web`

## Troubleshooting

### "X-Platform header is required"
- Ensure all API requests include the `X-Platform` header
- Valid values: `web`, `mobile`

### "No implementation found for..."
- Check that concrete service/request is registered in `PlatformServiceProvider`
- Verify the `platform()` method returns correct Platform enum case

### "Invalid platform"
- Check platform value matches enum in `app/Enums/Platform.php`
- Platform header is case-insensitive but must match enum values

### Routes not working
- Verify route files exist: `routes/api/v1/admin.php`, `routes/api/v1/merchant.php`
- Check `bootstrap/app.php` routing configuration
- Run `php artisan route:list` to see registered routes

## Testing

### Unit Tests
```php
use App\Enums\Platform;
use App\Http\Requests\V1\Web\Merchant\Auth\SignInRequest;

test('SignInRequest declares web platform', function () {
    expect(SignInRequest::platform())->toBe(Platform::WEB);
});
```

### Feature Tests
```php
test('authentication requires X-Platform header', function () {
    $response = $this->postJson('/api/v1/merchant/auth/signin', [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $response->assertStatus(500); // Missing X-Platform header
});

test('web platform authentication works', function () {
    $response = $this->postJson('/api/v1/merchant/auth/signin', [
        'email' => 'test@example.com',
        'password' => 'password',
    ], [
        'X-Platform' => 'web',
    ]);

    $response->assertOk();
});
```
