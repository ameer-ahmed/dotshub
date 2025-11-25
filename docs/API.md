# API Documentation

Complete reference for DotsHub API endpoints, request formats, and response structures.

## Base URL

```
http://yourdomain.com/api/v1/{platform}/{context}
```

**Platforms:** `web`, `mobile`
**Contexts:** `admin`, `merchant`

## API Structure

```
/api/v1/web/admin/         # Admin endpoints (central database)
/api/v1/web/merchant/      # Merchant endpoints (tenant database)
```

## Authentication

DotsHub uses JWT (JSON Web Token) authentication with two separate guards:
- `admin` - For platform administrators
- `user` - For tenant users

### Token Usage

Include the JWT token in the Authorization header:

```
Authorization: Bearer {your-token-here}
```

### Token Expiration

Default TTL: 60 minutes (configurable in `config/jwt.php`)

## Common Headers

All API requests should include:

```
Content-Type: application/json
Accept: application/json
Accept-Language: en  # or 'ar' for Arabic
```

## Response Format

All API responses follow this standard format:

### Success Response

```json
{
  "status": true,
  "data": {
    // Response data here
  },
  "message": "Operation successful"
}
```

### Error Response

```json
{
  "status": false,
  "data": null,
  "message": "Error message here"
}
```

### Validation Error Response

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": [
      "The email field is required."
    ],
    "password": [
      "The password must be at least 8 characters."
    ]
  }
}
```

## HTTP Status Codes

| Code | Meaning |
|------|---------|
| 200 | OK - Request successful |
| 201 | Created - Resource created successfully |
| 400 | Bad Request - Invalid request format |
| 401 | Unauthorized - Authentication required |
| 403 | Forbidden - Insufficient permissions |
| 404 | Not Found - Resource not found |
| 422 | Unprocessable Entity - Validation failed |
| 500 | Internal Server Error - Server error |

---

## Admin API Endpoints

Base: `/api/v1/web/admin`

### Admin Authentication

#### 1. Admin Sign Up

Create a new admin account.

**Endpoint:** `POST /api/v1/web/admin/auth/sign-up`

**Headers:**
```
Content-Type: application/json
Accept-Language: en
```

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "admin@example.com",
  "password": "SecurePass123",
  "password_confirmation": "SecurePass123"
}
```

**Validation Rules:**
- `name`: required, string, max 255 characters
- `email`: required, email, unique in admins table
- `password`: required, min 8 characters, must match confirmation

**Success Response (201):**
```json
{
  "status": true,
  "data": {
    "admin": {
      "id": 1,
      "name": "John Doe",
      "email": "admin@example.com",
      "created_at": "2025-11-25T10:00:00.000000Z"
    },
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "bearer"
  },
  "message": "Admin registered successfully"
}
```

**Error Response (422):**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email has already been taken."]
  }
}
```

**cURL Example:**
```bash
curl -X POST http://localhost:8000/api/v1/web/admin/auth/sign-up \
  -H "Content-Type: application/json" \
  -H "Accept-Language: en" \
  -d '{
    "name": "John Doe",
    "email": "admin@example.com",
    "password": "SecurePass123",
    "password_confirmation": "SecurePass123"
  }'
```

#### 2. Admin Sign In

Authenticate an admin user.

**Endpoint:** `POST /api/v1/web/admin/auth/sign-in`

**Request Body:**
```json
{
  "email": "admin@example.com",
  "password": "SecurePass123"
}
```

**Success Response (200):**
```json
{
  "status": true,
  "data": {
    "admin": {
      "id": 1,
      "name": "John Doe",
      "email": "admin@example.com"
    },
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "bearer"
  },
  "message": "Admin signed in successfully"
}
```

**Error Response (401):**
```json
{
  "status": false,
  "data": null,
  "message": "Invalid credentials"
}
```

**cURL Example:**
```bash
curl -X POST http://localhost:8000/api/v1/web/admin/auth/sign-in \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "SecurePass123"
  }'
```

#### 3. Admin Sign Out

Log out the authenticated admin.

**Endpoint:** `POST /api/v1/web/admin/auth/sign-out`

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "status": true,
  "data": null,
  "message": "Admin signed out successfully"
}
```

**cURL Example:**
```bash
curl -X POST http://localhost:8000/api/v1/web/admin/auth/sign-out \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

## Merchant API Endpoints

Base: `/api/v1/web/merchant`

**Note:** Merchant endpoints require tenant identification via domain.

### Merchant Authentication

#### 1. Merchant Sign Up (Tenant Creation)

Register a new merchant with tenant database.

**Endpoint:** `POST /api/v1/web/merchant/auth/sign-up`

**Domain:** Use central domain (e.g., `localhost`, `dotshub.test`)

**Request Body:**
```json
{
  "merchant": {
    "name": "My Awesome Store",
    "description": "Best products online"
  },
  "domain": {
    "domain": "mystore.localhost"
  },
  "user": {
    "name": "Store Owner",
    "email": "owner@mystore.com",
    "password": "SecurePass123",
    "password_confirmation": "SecurePass123"
  }
}
```

**Validation Rules:**

**Merchant:**
- `merchant.name`: required, string, max 255
- `merchant.description`: optional, string

**Domain:**
- `domain.domain`: required, unique, valid domain format

**User:**
- `user.name`: required, string, max 255
- `user.email`: required, email
- `user.password`: required, min 8, confirmed

**Success Response (201):**
```json
{
  "status": true,
  "data": {
    "merchant": {
      "id": "9d4e2f3a-7c1b-4e8f-9a6d-2b5c8e7f1a3d",
      "name": "My Awesome Store",
      "description": "Best products online",
      "status": "pending",
      "created_at": "2025-11-25T10:00:00.000000Z"
    },
    "user": {
      "id": 1,
      "name": "Store Owner",
      "email": "owner@mystore.com",
      "merchant_id": "9d4e2f3a-7c1b-4e8f-9a6d-2b5c8e7f1a3d",
      "status": "active"
    },
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "bearer"
  },
  "message": "Merchant registered successfully"
}
```

**What Happens:**
1. Creates merchant record in central database
2. Creates isolated tenant database (`tenant{merchant_id}`)
3. Runs tenant migrations
4. Creates domain mapping
5. Creates first user in tenant database
6. Assigns `merchant_admin` role
7. Seeds default roles and permissions

**cURL Example:**
```bash
curl -X POST http://localhost:8000/api/v1/web/merchant/auth/sign-up \
  -H "Content-Type: application/json" \
  -d '{
    "merchant": {
      "name": "My Awesome Store",
      "description": "Best products online"
    },
    "domain": {
      "domain": "mystore.localhost"
    },
    "user": {
      "name": "Store Owner",
      "email": "owner@mystore.com",
      "password": "SecurePass123",
      "password_confirmation": "SecurePass123"
    }
  }'
```

#### 2. Merchant Sign In

Authenticate a tenant user.

**Endpoint:** `POST /api/v1/web/merchant/auth/sign-in`

**Domain:** Use tenant domain (e.g., `mystore.localhost`)

**Request Body:**
```json
{
  "email": "owner@mystore.com",
  "password": "SecurePass123"
}
```

**Success Response (200):**
```json
{
  "status": true,
  "data": {
    "user": {
      "id": 1,
      "name": "Store Owner",
      "email": "owner@mystore.com",
      "merchant_id": "9d4e2f3a-7c1b-4e8f-9a6d-2b5c8e7f1a3d",
      "status": "active",
      "roles": [
        {
          "id": 1,
          "name": "merchant_admin",
          "display_name": "Merchant Administrator"
        }
      ]
    },
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "bearer"
  },
  "message": "User signed in successfully"
}
```

**cURL Example:**
```bash
curl -X POST http://mystore.localhost:8000/api/v1/web/merchant/auth/sign-in \
  -H "Content-Type: application/json" \
  -d '{
    "email": "owner@mystore.com",
    "password": "SecurePass123"
  }'
```

#### 3. Merchant Sign Out

Log out the authenticated tenant user.

**Endpoint:** `POST /api/v1/web/merchant/auth/sign-out`

**Domain:** Tenant domain

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "status": true,
  "data": null,
  "message": "User signed out successfully"
}
```

---

### Role Management

Manage roles and permissions within the tenant.

#### 1. List Roles

Get all roles for the merchant.

**Endpoint:** `GET /api/v1/web/merchant/role`

**Domain:** Tenant domain

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "status": true,
  "data": [
    {
      "id": 1,
      "name": "merchant_admin",
      "display_name": "Merchant Administrator",
      "description": "Full access to all features",
      "is_private": true,
      "is_editable": false,
      "permissions_count": 50,
      "created_at": "2025-11-25T10:00:00.000000Z"
    },
    {
      "id": 2,
      "name": "staff",
      "display_name": "Staff Member",
      "description": "Basic access for staff",
      "is_private": false,
      "is_editable": true,
      "permissions_count": 10,
      "created_at": "2025-11-25T10:00:00.000000Z"
    }
  ],
  "message": "Roles retrieved successfully"
}
```

**cURL Example:**
```bash
curl -X GET http://mystore.localhost:8000/api/v1/web/merchant/role \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### 2. Create Role

Create a new role with permissions.

**Endpoint:** `POST /api/v1/web/merchant/role`

**Domain:** Tenant domain

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "name": "warehouse_manager",
  "display_name": "Warehouse Manager",
  "description": "Manages inventory and warehouse operations",
  "permissions": [1, 2, 3, 5, 8],
  "is_private": false,
  "is_editable": true
}
```

**Validation Rules:**
- `name`: required, string, unique, lowercase, snake_case
- `display_name`: required, string, max 255
- `description`: optional, string
- `permissions`: optional, array of permission IDs
- `is_private`: optional, boolean (default: false)
- `is_editable`: optional, boolean (default: true)

**Success Response (201):**
```json
{
  "status": true,
  "data": {
    "id": 3,
    "name": "warehouse_manager",
    "display_name": "Warehouse Manager",
    "description": "Manages inventory and warehouse operations",
    "merchant_id": "9d4e2f3a-7c1b-4e8f-9a6d-2b5c8e7f1a3d",
    "created_by": 1,
    "is_private": false,
    "is_editable": true,
    "permissions": [
      {
        "id": 1,
        "name": "products.view",
        "display_name": "View Products"
      },
      {
        "id": 2,
        "name": "products.create",
        "display_name": "Create Products"
      }
    ],
    "created_at": "2025-11-25T11:00:00.000000Z"
  },
  "message": "Role created successfully"
}
```

**cURL Example:**
```bash
curl -X POST http://mystore.localhost:8000/api/v1/web/merchant/role \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "warehouse_manager",
    "display_name": "Warehouse Manager",
    "description": "Manages inventory and warehouse operations",
    "permissions": [1, 2, 3, 5, 8],
    "is_private": false,
    "is_editable": true
  }'
```

#### 3. Update Role

Update an existing role.

**Endpoint:** `PUT /api/v1/web/merchant/role/{id}`

**Domain:** Tenant domain

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "display_name": "Senior Warehouse Manager",
  "description": "Senior level warehouse management",
  "permissions": [1, 2, 3, 5, 8, 13, 21]
}
```

**Validation Rules:**
- `display_name`: optional, string, max 255
- `description`: optional, string
- `permissions`: optional, array of permission IDs

**Success Response (200):**
```json
{
  "status": true,
  "data": {
    "id": 3,
    "name": "warehouse_manager",
    "display_name": "Senior Warehouse Manager",
    "description": "Senior level warehouse management",
    "permissions": [
      // Updated permissions array
    ],
    "updated_at": "2025-11-25T12:00:00.000000Z"
  },
  "message": "Role updated successfully"
}
```

**Error Responses:**

**404 Not Found:**
```json
{
  "status": false,
  "data": null,
  "message": "Role not found"
}
```

**403 Forbidden (Not Editable):**
```json
{
  "status": false,
  "data": null,
  "message": "Role is not editable"
}
```

**403 Forbidden (Wrong Tenant):**
```json
{
  "status": false,
  "data": null,
  "message": "Unauthorized"
}
```

**cURL Example:**
```bash
curl -X PUT http://mystore.localhost:8000/api/v1/web/merchant/role/3 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "display_name": "Senior Warehouse Manager",
    "description": "Senior level warehouse management",
    "permissions": [1, 2, 3, 5, 8, 13, 21]
  }'
```

#### 4. Delete Role

Delete a role.

**Endpoint:** `DELETE /api/v1/web/merchant/role/{id}`

**Domain:** Tenant domain

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "status": true,
  "data": null,
  "message": "Role deleted successfully"
}
```

**Error Responses:**

**404 Not Found:**
```json
{
  "status": false,
  "data": null,
  "message": "Role not found"
}
```

**403 Forbidden (Not Deletable):**
```json
{
  "status": false,
  "data": null,
  "message": "Role is not deletable"
}
```

**cURL Example:**
```bash
curl -X DELETE http://mystore.localhost:8000/api/v1/web/merchant/role/3 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## Pagination

Paginated endpoints return data in this format:

```json
{
  "status": true,
  "data": {
    "current_page": 1,
    "data": [
      // Array of items
    ],
    "first_page_url": "http://api.example.com/endpoint?page=1",
    "from": 1,
    "last_page": 5,
    "last_page_url": "http://api.example.com/endpoint?page=5",
    "next_page_url": "http://api.example.com/endpoint?page=2",
    "path": "http://api.example.com/endpoint",
    "per_page": 15,
    "prev_page_url": null,
    "to": 15,
    "total": 75
  },
  "message": "Data retrieved successfully"
}
```

**Query Parameters:**
- `page`: Page number (default: 1)
- `per_page`: Items per page (default: 15)

**Example:**
```bash
curl "http://mystore.localhost:8000/api/v1/web/merchant/role?page=2&per_page=20" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## Filtering and Sorting

Many endpoints support filtering and sorting via query parameters.

**Example:**
```
GET /api/v1/web/merchant/products?status=active&category_id=5&sort_by=price&sort_order=desc
```

**Common Filter Parameters:**
- `status`: Filter by status
- `category_id`: Filter by category
- `search`: Search by name/description
- `min_price`: Minimum price filter
- `max_price`: Maximum price filter

**Sorting Parameters:**
- `sort_by`: Field to sort by (e.g., `created_at`, `price`, `name`)
- `sort_order`: `asc` or `desc` (default: `desc`)

---

## Internationalization

The API supports multiple languages via the `Accept-Language` header.

**Supported Languages:**
- `en` - English (default)
- `ar` - Arabic

**Example:**
```bash
curl -X GET http://mystore.localhost:8000/api/v1/web/merchant/role \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept-Language: ar"
```

**Response in Arabic:**
```json
{
  "status": true,
  "data": [...],
  "message": "تم استرجاع الأدوار بنجاح"
}
```

---

## Error Handling

### Common Error Scenarios

#### 1. Unauthenticated

**Status:** 401 Unauthorized

```json
{
  "message": "Unauthenticated."
}
```

**Solution:** Include valid JWT token in Authorization header

#### 2. Forbidden

**Status:** 403 Forbidden

```json
{
  "status": false,
  "data": null,
  "message": "Unauthorized"
}
```

**Solution:** User doesn't have required permissions or accessing wrong tenant's data

#### 3. Validation Failed

**Status:** 422 Unprocessable Entity

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field_name": [
      "Error message 1",
      "Error message 2"
    ]
  }
}
```

**Solution:** Fix validation errors and resubmit

#### 4. Resource Not Found

**Status:** 404 Not Found

```json
{
  "status": false,
  "data": null,
  "message": "Resource not found"
}
```

**Solution:** Check resource ID exists

#### 5. Server Error

**Status:** 500 Internal Server Error

```json
{
  "status": false,
  "data": null,
  "message": "An error occurred"
}
```

**Solution:** Check server logs for details

---

## Rate Limiting

API requests are rate-limited to prevent abuse.

**Default Limits:**
- 60 requests per minute per IP

**Headers:**
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1637856000
```

**Rate Limit Exceeded Response (429):**
```json
{
  "message": "Too Many Attempts."
}
```

---

## Postman Collection

For easier API testing, import the Postman collection:

1. Download: [DotsHub API Collection](./postman/dotshub-collection.json)
2. Import into Postman
3. Configure environment variables:
   - `base_url`: Your API base URL
   - `admin_token`: Admin JWT token
   - `user_token`: User JWT token
   - `tenant_domain`: Your tenant domain

---

## API Versioning

The API uses URL-based versioning: `/api/v1/...`

When breaking changes are introduced, a new version will be created: `/api/v2/...`

Old versions remain supported for a deprecation period.

---

## Best Practices

### 1. Always Include Headers

```
Content-Type: application/json
Accept: application/json
Accept-Language: en
Authorization: Bearer {token}
```

### 2. Handle Token Expiration

Implement token refresh logic when receiving 401 responses.

### 3. Use HTTPS in Production

Never send tokens over unencrypted connections.

### 4. Validate Input Client-Side

Reduce unnecessary API calls by validating input before submission.

### 5. Handle Errors Gracefully

Always check response status and handle errors appropriately.

### 6. Respect Rate Limits

Implement backoff strategies when approaching rate limits.

---

## Related Documentation

- [Getting Started](./GETTING_STARTED.md) - Setup and configuration
- [Services](./SERVICES.md) - Business logic layer
- [Multi-Tenancy](./MULTI_TENANCY.md) - Tenant system explained
- [Architecture](./ARCHITECTURE.md) - System design

---

*Last Updated: 2025-11-25*