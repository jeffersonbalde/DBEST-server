# CSRF Token Mismatch Fix

## Issue
Getting "CSRF token mismatch" error when trying to log in from the Digital Ocean client.

## Root Cause
The `EnsureFrontendRequestsAreStateful` middleware was forcing stateful authentication (cookie-based) for all API routes, which requires CSRF tokens. However, the application uses Bearer token authentication, not stateful authentication.

## Solution Applied

### Updated `bootstrap/app.php`
- Removed the `EnsureFrontendRequestsAreStateful` middleware from API routes
- Added CSRF token validation exception for all API routes (`api/*`)

This allows:
- API routes to use Bearer token authentication without CSRF validation
- Web routes to still use CSRF protection (if needed)

## How It Works Now

1. **API Routes** (`/api/*`):
   - No CSRF validation required
   - Uses Bearer token authentication
   - Headers: `Authorization: Bearer {token}`

2. **Web Routes** (if any):
   - Still protected by CSRF (if CSRF middleware is applied)

## Testing
After deploying, test the login:
```bash
curl -X POST https://dbest-server-eopaw.ondigitalocean.app/api/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"username":"test","password":"test"}'
```

Should return a token without CSRF errors.

## Notes
- This configuration is correct for API-only applications using Bearer tokens
- If you need stateful authentication (cookies) in the future, you'll need to:
  1. Re-add `EnsureFrontendRequestsAreStateful` middleware
  2. Fetch CSRF cookie from `/sanctum/csrf-cookie` before login
  3. Include CSRF token in requests

