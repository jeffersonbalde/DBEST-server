# DigitalOcean Database Connection Fix

## Problem
When running `php artisan migrate` or `php artisan migrate:fresh --seed` on DigitalOcean, you get:
```
SQLSTATE[HY000] [2002] php_network_getaddresses: getaddrinfo for 
db-mysql-nyc3-10742-do-user-23126168-0.m.db.ondigitalocean.com failed: 
No such host is known.
```

## Root Causes

1. **DNS Resolution Issue**: The server cannot resolve the database hostname
2. **Network Configuration**: App and database might be in different networks
3. **SSL Configuration**: DigitalOcean databases require proper SSL settings
4. **Connection Timeout**: Default timeout might be too short

## Solutions

### Solution 1: Use Private Hostname (Recommended for Same VPC)

If your app and database are in the same VPC/region, use the **private hostname** instead:

1. Go to DigitalOcean Dashboard → Databases → Your Database
2. Click on "Connection Details"
3. Look for **"Private Network Hostname"** (not the public one)
4. Update your `.env` file:
   ```env
   DB_HOST=private-db-mysql-nyc3-10742-do-user-23126168-0.db.ondigitalocean.com
   DB_PORT=25060
   ```

### Solution 2: Verify Database is Accessible

1. **Check Trusted Sources**:
   - Go to DigitalOcean → Databases → Your Database → Settings
   - Under "Trusted Sources", ensure your app's IP or "0.0.0.0/0" (for testing) is added
   - For production, use specific IPs or VPC ranges

2. **Test Connection from Server**:
   ```bash
   # SSH into your DigitalOcean app server
   # Test DNS resolution
   nslookup db-mysql-nyc3-10742-do-user-23126168-0.m.db.ondigitalocean.com
   
   # Test connection (if mysql client is installed)
   mysql -h db-mysql-nyc3-10742-do-user-23126168-0.m.db.ondigitalocean.com \
         -P 25060 \
         -u doadmin \
         -p
   ```

### Solution 3: Update Environment Variables

Ensure your `.env` file on DigitalOcean has these settings:

```env
DB_CONNECTION=mysql
DB_HOST=db-mysql-nyc3-10742-do-user-23126168-0.m.db.ondigitalocean.com
DB_PORT=25060
DB_DATABASE=DBEST
DB_USERNAME=doadmin
DB_PASSWORD=your-password-here

# SSL Configuration (optional but recommended)
MYSQL_ATTR_SSL_VERIFY_SERVER_CERT=false
DB_TIMEOUT=30
```

### Solution 4: Use Database URL (Alternative)

Instead of separate variables, you can use `DB_URL`:

```env
DB_URL=mysql://doadmin:your-password@db-mysql-nyc3-10742-do-user-23126168-0.m.db.ondigitalocean.com:25060/DBEST?sslmode=REQUIRED
```

### Solution 5: Check App Platform Database Connection

If using DigitalOcean App Platform:

1. Go to your App → Settings → Components
2. Check if database is linked as a component
3. If linked, the connection string should be automatically injected
4. Verify the environment variables are set correctly

### Solution 6: DNS Resolution Fix

If DNS is not resolving, try:

1. **Flush DNS on the server** (if you have SSH access):
   ```bash
   # For Ubuntu/Debian
   sudo systemd-resolve --flush-caches
   
   # Or restart networking
   sudo systemctl restart systemd-resolved
   ```

2. **Use IP Address** (temporary workaround):
   - Get the IP address: `nslookup db-mysql-nyc3-10742-do-user-23126168-0.m.db.ondigitalocean.com`
   - Use IP in `.env`: `DB_HOST=<ip-address>`
   - **Note**: IPs can change, so this is not recommended for production

## Verification Steps

After applying fixes, test the connection:

```bash
# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();
# Should return PDO object without errors

# Or run a simple query
>>> DB::select('SELECT 1');
# Should return [{"1": 1}]
```

## Updated Configuration

The `config/database.php` has been updated with:
- Better SSL handling for DigitalOcean
- Connection timeout settings
- Error handling improvements
- Support for SSL verification options

## Common Issues

### Issue: "Connection refused"
- **Cause**: Database not accessible from app's network
- **Fix**: Add app's IP to database trusted sources

### Issue: "SSL connection error"
- **Cause**: SSL configuration mismatch
- **Fix**: Set `MYSQL_ATTR_SSL_VERIFY_SERVER_CERT=false` in `.env`

### Issue: "Connection timeout"
- **Cause**: Network latency or firewall
- **Fix**: Increase `DB_TIMEOUT=30` in `.env`

### Issue: "Access denied"
- **Cause**: Wrong username/password or user doesn't have access
- **Fix**: Verify credentials in DigitalOcean dashboard

## Still Having Issues?

1. Check DigitalOcean database logs
2. Verify database is running and accessible
3. Check firewall rules and network settings
4. Contact DigitalOcean support if database is not accessible

