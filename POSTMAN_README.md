# V6 Gateway API - Postman Collection

This directory contains a comprehensive Postman collection for testing and interacting with the V6 Gateway API.

## Files

- **postman_collection.json** - Main API collection with all endpoints
- **postman_environment.json** - Local development environment configuration

## Features

### Multi-Tenant Architecture
- Domain-based tenant isolation
- Separate endpoints for central admin and tenant-specific operations
- Environment variables for easy tenant switching

### Platform Support
- Web and Mobile platform variants
- Platform-specific resource responses
- Configurable via `X-Platform` header

### Authentication
- JWT-based authentication
- Automatic token management in environment variables
- Pre-configured auth inheritance

### Testing Scripts
- Automatic response validation
- Token extraction and storage
- Response time monitoring
- ID extraction for dependent requests

## Getting Started

### 1. Import Collection

1. Open Postman
2. Click **Import** button
3. Select `postman_collection.json`
4. Click **Import**

### 2. Import Environment

1. Click the **Environments** icon (gear icon)
2. Click **Import**
3. Select `postman_environment.json`
4. Select the imported environment from the dropdown

### 3. Configure Environment

Update these variables in your environment:

| Variable | Description | Example |
|----------|-------------|---------|
| `base_url` | Central app base URL | `http://localhost:8000` |
| `tenant_domain` | Tenant subdomain with port | `ameer.localhost:8000` |
| `api_version` | API version | `v1` |
| `platform` | Platform type (web/mobile) | `web` |
| `access_token` | JWT token (auto-populated) | (auto) |
| `branch_id` | Test branch ID | `1` |
| `role_id` | Test role ID | `1` |

### 4. First Request

1. Navigate to **System API (Tenant) > Authentication > Sign In**
2. Update the request body with valid credentials
3. Click **Send**
4. The access token will be automatically saved to your environment

## Collection Structure

```
V6 Gateway API/
├── System API (Tenant)/          # Tenant-specific endpoints
│   ├── Authentication/           # Sign up, Sign in, Sign out
│   ├── Branches/                 # Branch CRUD operations
│   │   ├── List Branches (Paginated)
│   │   ├── Get All Branches
│   │   ├── Get Branch by ID
│   │   ├── Create Branch
│   │   ├── Update Branch
│   │   └── Delete Branch
│   └── Roles/                    # Role CRUD operations
│       ├── List Roles
│       ├── Create Role
│       ├── Update Role
│       └── Delete Role
└── Admin API (Central)/          # Central admin endpoints
    └── Health Check
```

## Required Headers

All requests include these headers:

| Header | Value | Purpose |
|--------|-------|---------|
| `X-Platform` | `web` or `mobile` | Determines resource variant |
| `Content-Type` | `application/json` | Request body format |
| `Accept` | `application/json` | Response format |
| `Authorization` | `Bearer {token}` | JWT authentication (auto) |

## Authentication Flow

### Sign Up (No Auth Required)
```
POST http://localhost:8000/api/v1/system/auth/sign/up

Body:
{
  "name": "John Doe",
  "email": "john@gmail.com",
  "password": "password123",
  "password_confirmation": "password123",
  "company_name": "Acme Corp",
  "domain": "acme"
}
```

### Sign In (No Auth Required)
```
POST http://ameer.localhost:8000/api/v1/system/auth/sign/in

Body:
{
  "email": "john@gmail.com",
  "password": "password123"
}

Response:
{
  "data": {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "bearer",
    "expires_in": 3600,
    "user": { ... }
  }
}
```

The token is automatically saved to `access_token` environment variable.

### Sign Out (Requires Auth)
```
POST http://ameer.localhost:8000/api/v1/system/auth/sign/out
Authorization: Bearer {access_token}
```

## Testing Different Platforms

To test Mobile vs Web responses:

1. Update `platform` environment variable to `mobile`
2. Re-run your requests
3. Compare responses with `web` platform

The system will automatically return platform-specific resource variations based on the `X-Platform` header.

## Example Workflows

### 1. Complete Authentication Flow
1. Sign Up → Creates tenant and user
2. Sign In → Gets access token
3. Make authenticated requests
4. Sign Out → Invalidates token

### 2. Branch Management
1. Sign In
2. List Branches (Paginated) → View existing branches
3. Create Branch → Add new branch
4. Get Branch by ID → Verify creation
5. Update Branch → Modify branch details
6. Delete Branch → Remove branch

### 3. Role Management
1. Sign In
2. List Roles → View existing roles
3. Create Role → Add new role with permissions
4. Update Role → Modify role permissions
5. Delete Role → Remove role

## Environment Variables Auto-Population

The collection automatically saves these values after successful requests:

- **access_token** - After Sign In
- **user_id** - After Sign In
- **tenant_id** - After Sign Up
- **branch_id** - After Create Branch or Get All Branches
- **role_id** - After Create Role or List Roles

## Troubleshooting

### Token Expired
Run the **Sign In** request again to get a new token.

### Wrong Tenant Domain
Ensure `tenant_domain` in environment matches your tenant's subdomain.

### Platform Errors
Verify `X-Platform` header is set to `web` or `mobile`.

### Invalid Response
Check that you're using the correct base URL:
- Central endpoints: `http://{{base_url}}/...`
- Tenant endpoints: `http://{{tenant_domain}}/...`

## API Response Format

All responses follow this structure:

### Success Response
```json
{
  "success": true,
  "message": "Success message",
  "data": { ... }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "data": {
    "field_name": ["Error details"]
  }
}
```

## Creating Additional Environments

For staging or production:

1. Duplicate `postman_environment.json`
2. Rename to `postman_environment_production.json`
3. Update variables:
   ```json
   {
     "base_url": "https://api.yourdomain.com",
     "tenant_domain": "tenant.yourdomain.com",
     ...
   }
   ```
4. Import the new environment

## Tips

1. **Use Collection Runner** - Run entire folders to test all endpoints sequentially
2. **Monitor Console** - Check Postman Console for auto-saved values
3. **Save Examples** - Save successful responses as examples for documentation
4. **Use Variables** - Leverage `{{variable}}` syntax for dynamic values
5. **Pre-request Scripts** - Add custom logic before requests execute
6. **Tests Tab** - View auto-generated test results after each request

## Support

For issues or questions:
- Check the main project README
- Review API documentation
- Verify environment configuration
- Check Postman Console for detailed logs
