# 🔄 Configuration Fallbacks System

## Overview

upMVC uses a **three-level fallback system** that allows the framework to work even without complete configuration. This enables quick testing, gradual configuration, and flexible deployment strategies.

**Core Philosophy:** Start simple, configure as you grow.

---

## 🎯 Why Fallbacks?

### Benefits

✅ **Quick Start** - Framework works immediately without full configuration  
✅ **Gradual Configuration** - Add settings only when you need them  
✅ **Flexible Deployment** - Different configs for dev/staging/production  
✅ **No Breaking Errors** - Missing config? Fallback handles it  
✅ **Development Friendly** - Code defaults for rapid development

### Use Cases

- 🚀 Quick testing without .env setup
- 📦 Static sites or API-only projects (no database needed)
- 🔧 Local development with default settings
- 🌐 Production with secure .env configuration

---

## 📊 Fallback Priority Summary

| Setting | Priority 1 (Highest) | Priority 2 (Fallback) | File Location |
|---------|---------------------|----------------------|---------------|
| **Paths & Domain** | `.env` SITE_PATH, DOMAIN_NAME | `Config.php` $fallbacks | `/etc/Config.php` |
| **Protected Routes** | `.env` PROTECTED_ROUTES | `start.php` $defaultProtectedRoutes | `/etc/start.php` |
| **Database Credentials** | `.env` DB_* variables | `ConfigDatabase.php` | `/etc/ConfigDatabase.php` |

---

## 1️⃣ Path & Domain Fallbacks

### Location: `/etc/Config.php`

### Configuration Array

```php
/**
 * Fallback configuration values for path and domain
 * 
 * Used only if .env file is missing or values are not set.
 * In normal operation, .env values are always used.
 */
private static $fallbacks = [
    'site_path' => '/upMVC',              // Installation folder or '' for root
    'domain_name' => 'http://localhost',  // Domain URL without trailing slash
];
```

### Priority Logic

```
1. Check .env for SITE_PATH and DOMAIN_NAME
   ↓ If found: Use .env values ✅
   ↓ If missing: Continue to fallback
   
2. Use Config.php $fallbacks array
   ↓ Returns default values
```

### How It Works

```php
// In Config.php
public static function getSitePath(): string
{
    // Priority 1: .env file
    // Priority 2: $fallbacks array
    return Environment::get('SITE_PATH', self::$fallbacks['site_path']);
}

public static function getDomainName(): string
{
    return Environment::get('DOMAIN_NAME', self::$fallbacks['domain_name']);
}
```

### Used For

- ✅ `BASE_URL` constant construction
- ✅ Routing and URL processing
- ✅ Asset paths (CSS, JS, images)
- ✅ Redirect URLs
- ✅ Link generation in views

### Configuration Examples

**Production (.env):**
```env
SITE_PATH=
DOMAIN_NAME=https://mysite.com
```

**Development (.env):**
```env
SITE_PATH=/upMVC
DOMAIN_NAME=http://localhost
```

**Quick Testing (no .env):**
```
Uses Config.php fallbacks automatically
No configuration needed!
```

### When to Edit

**Edit `.env`:** Production, staging, or when deploying  
**Edit `Config.php` fallbacks:** Default development values for your team

---

## 2️⃣ Protected Routes Fallbacks

### Location: `/etc/start.php`

### Configuration Array

```php
/**
 * Default protected routes requiring authentication
 * 
 * These routes require user authentication before access.
 * Can be overridden via PROTECTED_ROUTES in .env (comma-separated list)
 */
private static $defaultProtectedRoutes = [
    '/dashboardexample/*',  // Dashboard example module
    '/admin/*',             // Admin panel (all routes)
    '/users/*',             // User management (all routes)
    '/moda'                 // Modal example route
];
```

### Priority Logic

```
1. Check .env for PROTECTED_ROUTES
   ↓ If found: Parse comma-separated list ✅
   ↓ If missing: Continue to fallback
   
2. Use start.php $defaultProtectedRoutes array
   ↓ Returns default protected routes
```

### How It Works

```php
// In start.php
private function getProtectedRoutes(): array
{
    // Priority 1: .env file (comma-separated)
    $envRoutes = Environment::get('PROTECTED_ROUTES', '');
    
    if (!empty($envRoutes)) {
        return array_map('trim', explode(',', $envRoutes));
    }
    
    // Priority 2: $defaultProtectedRoutes array
    return self::$defaultProtectedRoutes;
}
```

### Used For

- ✅ `AuthMiddleware` route protection
- ✅ Session-based authentication checks
- ✅ Redirect to login for unauthorized access
- ✅ Wildcard pattern matching (`/admin/*`)

### Configuration Examples

**Production (.env):**
```env
PROTECTED_ROUTES=/admin/*,/users/*,/dashboard/*,/api/secure/*
```

**Development (.env):**
```env
PROTECTED_ROUTES=/admin/*,/users/*
```

**Quick Testing (no .env):**
```
Uses start.php defaults:
- /dashboardexample/*
- /admin/*
- /users/*
- /moda
```

### Wildcard Patterns

```
/admin/*        → Matches /admin, /admin/users, /admin/settings/edit
/users/*        → Matches /users, /users/profile, /users/123
/exact-route    → Matches only /exact-route (no children)
```

### When to Edit

**Edit `.env`:** Production security policies, specific project needs  
**Edit `start.php` defaults:** Default security for your project template

---

## 3️⃣ Database Credentials Fallbacks

### Location: `/etc/Database.php`

### Priority Logic

```
1. Check .env for DB_* variables
   ↓ If DB_HOST exists: Use all .env DB_* vars ✅
   ↓ If DB_HOST missing: Continue to fallback
   
2. Use ConfigDatabase.php values
   ↓ Returns fallback credentials
```

### How It Works

```php
// In Database.php
private function loadConfig()
{
    // Priority 1: .env file (production recommended)
    if (isset($_ENV['DB_HOST']) && !empty($_ENV['DB_HOST'])) {
        $this->host = $_ENV['DB_HOST'];
        $this->databaseName = $_ENV['DB_NAME'] ?? '';
        $this->username = $_ENV['DB_USER'] ?? '';
        $this->password = $_ENV['DB_PASS'] ?? '';
        $this->port = $_ENV['DB_PORT'] ?? 3306;
        $this->charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';
    } else {
        // Priority 2: ConfigDatabase.php (fallback)
        $this->host = ConfigDatabase::get('db.host');
        $this->databaseName = ConfigDatabase::get('db.name');
        $this->username = ConfigDatabase::get('db.user');
        $this->password = ConfigDatabase::get('db.pass');
        $this->port = ConfigDatabase::get('db.port', 3306);
        $this->charset = ConfigDatabase::get('db.charset', 'utf8mb4');
    }
}
```

### Environment Variables

```env
# .env database configuration (production)
DB_HOST=prod-server.com
DB_NAME=production_db
DB_USER=prod_user
DB_PASS=secure_password_here
DB_PORT=3306
DB_CHARSET=utf8mb4
```

### Used For

- ✅ PDO database connections
- ✅ Model database operations
- ✅ Migration scripts
- ✅ Seed data scripts

### Configuration Examples

**Production (.env):**
```env
DB_HOST=mysql.production.com
DB_NAME=prod_database
DB_USER=prod_user
DB_PASS=strong_password_123
DB_PORT=3306
DB_CHARSET=utf8mb4
```

**Development (.env):**
```env
DB_HOST=localhost
DB_NAME=dev_database
DB_USER=root
DB_PASS=
DB_PORT=3306
```

**Quick Testing (ConfigDatabase.php fallback):**
```php
// /etc/ConfigDatabase.php
return [
    'db.host' => 'localhost',
    'db.name' => 'test_db',
    'db.user' => 'root',
    'db.pass' => '',
    'db.port' => 3306,
    'db.charset' => 'utf8mb4'
];
```

### Security Best Practices

🔐 **Production:**
- ✅ Use `.env` file (not committed to Git)
- ✅ Strong passwords
- ✅ Limited user privileges
- ✅ Encrypted connections if possible

🔧 **Development:**
- ✅ Use `ConfigDatabase.php` with dummy data
- ✅ Never commit real credentials
- ✅ Use different credentials than production

### When to Edit

**Edit `.env`:** Production, staging, any real database  
**Edit `ConfigDatabase.php`:** Development defaults, local testing

---

## 🔍 Troubleshooting Configuration Conflicts

### Problem: Settings not being applied

**Check in this order:**

1. **`.env` file** (highest priority)
   - Location: `/etc/.env`
   - Is the file present?
   - Are variables correctly named?
   - Are there typos in variable names?

2. **`Config.php` $fallbacks** (path/domain)
   - Location: `/etc/Config.php`
   - Lines: ~30-33
   - Check `site_path` and `domain_name` values

3. **`start.php` $defaultProtectedRoutes** (auth routes)
   - Location: `/etc/start.php`
   - Lines: ~40-46
   - Check route patterns match your needs

4. **`ConfigDatabase.php`** (database)
   - Location: `/etc/ConfigDatabase.php`
   - Check all `db.*` keys

### Common Issues

#### Issue 1: Wrong BASE_URL

**Symptom:** Links broken, assets not loading, routing fails

**Solution:**
```
1. Check .env: SITE_PATH and DOMAIN_NAME
2. If .env missing: Check Config.php $fallbacks
3. Verify no trailing slash in DOMAIN_NAME
4. Verify SITE_PATH starts with / or is empty
```

#### Issue 2: Routes not protected

**Symptom:** Can access admin without login

**Solution:**
```
1. Check .env: PROTECTED_ROUTES (comma-separated)
2. If .env missing: Check start.php $defaultProtectedRoutes
3. Verify wildcard patterns: /admin/* not /admin*
4. Check AuthMiddleware is registered
```

#### Issue 3: Database connection fails

**Symptom:** "Database could not be connected" error

**Solution:**
```
1. Check .env: All DB_* variables present
2. If .env missing: Check ConfigDatabase.php values
3. Verify database server is running
4. Test credentials with MySQL client
5. Check firewall/port access
```

### Debug Mode

Enable debug mode to see configuration values:

```php
// In etc/.env
APP_ENV=development
DEBUG=true
```

Then check which config source is being used:

```php
// Temporary debugging code
echo "Site Path Source: " . (isset($_ENV['SITE_PATH']) ? '.env' : 'Config.php fallback');
echo "DB Source: " . (isset($_ENV['DB_HOST']) ? '.env' : 'ConfigDatabase.php fallback');
```

---

## 📝 Configuration Strategy

### Development Workflow

**Day 1 - Quick Start:**
```
1. Install upMVC
2. Don't create .env yet
3. Framework uses all fallbacks
4. Start coding immediately ✅
```

**Day 2 - Add Database:**
```
1. Create .env file
2. Add only DB_* variables
3. Rest uses fallbacks
4. Database now connected ✅
```

**Day 3 - Custom Config:**
```
1. Add SITE_PATH to .env
2. Add PROTECTED_ROUTES to .env
3. All custom, no fallbacks ✅
```

### Deployment Strategy

**Development:**
- Use `ConfigDatabase.php` with local DB
- Use `Config.php` fallbacks for paths
- Use `start.php` default routes

**Staging:**
- Create `.env` with staging DB credentials
- Set SITE_PATH if in subfolder
- Set PROTECTED_ROUTES for testing

**Production:**
- Complete `.env` with all settings
- Secure DB credentials
- Custom protected routes
- Never commit `.env` to Git!

---

## 🎯 Best Practices

### ✅ DO:

1. **Use .env for production** - Secure, not in Git
2. **Keep fallbacks as sensible defaults** - For quick dev setup
3. **Document your .env variables** - Team knows what to set
4. **Use different credentials per environment** - Dev ≠ Production
5. **Version control fallback files** - Team shares defaults

### ❌ DON'T:

1. **Commit .env to Git** - Security risk!
2. **Use production credentials in fallbacks** - Security risk!
3. **Rely on fallbacks in production** - Use .env instead
4. **Mix configuration sources** - Confusing to debug
5. **Hardcode paths in code** - Use Config methods

---

## 📚 Related Documentation

- [First Steps Guide](FIRST-STEPS-GUIDE.md) - Getting started
- [Configuration Management](../etc/Config.php) - Config.php source code
- [Database Setup](../etc/Database.php) - Database.php source code
- [Application Bootstrap](../etc/start.php) - start.php source code

---

## 🔗 Quick Reference

### Check Current Configuration

```php
// In your code, check what's being used:

// Paths
echo Config::getSitePath();      // From .env or Config.php
echo Config::getDomainName();    // From .env or Config.php

// Protected Routes
$start = new Start();
$routes = $start->getProtectedRoutes();  // From .env or start.php
print_r($routes);

// Database
$db = new Database();
// Check connection success
$conn = $db->getConnection();
if ($conn) {
    echo "DB connected!";  // From .env or ConfigDatabase.php
}
```

### .env Template

```env
# Application
SITE_PATH=/upMVC
DOMAIN_NAME=http://localhost
APP_ENV=development

# Database
DB_HOST=localhost
DB_NAME=your_database
DB_USER=your_username
DB_PASS=your_password
DB_PORT=3306
DB_CHARSET=utf8mb4

# Security
PROTECTED_ROUTES=/admin/*,/users/*,/dashboard/*
```

---

**Last Updated:** October 2025  
**upMVC Version:** 1.4.x+
