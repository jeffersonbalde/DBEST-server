# CORS Configuration for Digital Ocean Deployment

## Issue
CORS errors when accessing the API from the client application deployed on Digital Ocean.

## Solution Applied

### 1. Updated `config/cors.php`
- Added the Digital Ocean client URL to `allowed_origins`
- Added pattern matching for Digital Ocean client domains
- Increased `max_age` to 86400 (24 hours) for better caching
- Added exposed headers for proper authentication

### 2. Updated `config/sanctum.php`
- Added the Digital Ocean client domain to `stateful` domains
- This ensures Sanctum recognizes the client as a trusted SPA

### 3. Environment Variables (Required in Digital Ocean)
Make sure your `.env` file on Digital Ocean includes:

```env
SANCTUM_STATEFUL_DOMAINS=https://dbest-client-cobe7.ondigitalocean.app,localhost,localhost:3000,localhost:5173
FRONTEND_URL=https://dbest-client-cobe7.ondigitalocean.app
APP_URL=https://dbest-server-eopaw.ondigitalocean.app
```

### 4. After Deployment
1. Clear the config cache:
   ```bash
   php artisan config:clear
   php artisan config:cache
   ```

2. Restart the application on Digital Ocean

### 5. Verify CORS Headers
You can verify CORS is working by checking the response headers:
- `Access-Control-Allow-Origin` should include your client URL
- `Access-Control-Allow-Methods` should include the methods you're using
- `Access-Control-Allow-Headers` should include `Authorization` and `Content-Type`

## Testing
Test the CORS configuration by making a preflight request:
```bash
curl -X OPTIONS https://dbest-server-eopaw.ondigitalocean.app/api/auth/login \
  -H "Origin: https://dbest-client-cobe7.ondigitalocean.app" \
  -H "Access-Control-Request-Method: POST" \
  -H "Access-Control-Request-Headers: Content-Type,Authorization" \
  -v
```

You should see `Access-Control-Allow-Origin` in the response headers.

