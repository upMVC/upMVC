# Using .env Configuration in upMVC

## Complete Guide: Migrating from Constants to .env

This guide shows how to use `.env` file instead of hardcoded constants in your upMVC application.

---

## 1. Setup: Your .env File

```env
# Application Settings
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost
APP_PATH=/upMVC

# Legacy support (backward compatibility)
DOMAIN_NAME=http://localhost
SITE_PATH=/upMVC

# Database
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=upmvc
DB_USER=root
DB_PASS=your_password

# Session
SESSION_LIFETIME=7200
SESSION_COOKIE=upmvc_session

# Custom Settings (you can add anything!)
MY_API_KEY=abc123xyz
MY_CUSTOM_VALUE=something
```

---

## 2. How to Access .env Values

### **Method A: Using Environment Class (Recommended)**

```php
<?php
namespace YourModule;

use upMVC\Config\Environment;

class YourController
{
    public function index()
    {
        // Get single value
        $sitePath = Environment::get('SITE_PATH', '/default');
        $domainName = Environment::get('DOMAIN_NAME', 'http://localhost');
        $apiKey = Environment::get('MY_API_KEY');
        
        // Check environment
        if (Environment::isDevelopment()) {
            // Development-only code
        }
        
        // Check if value exists
        if (Environment::has('MY_API_KEY')) {
            $apiKey = Environment::get('MY_API_KEY');
        }
    }
}
```

### **Method B: Using ConfigManager (For Complex Configs)**

```php
<?php
use upMVC\Config\ConfigManager;

// ConfigManager uses Environment internally
$debug = ConfigManager::get('app.debug');        // from .env: APP_DEBUG
$dbHost = ConfigManager::get('database.connections.mysql.host'); // from .env: DB_HOST
$sitePath = ConfigManager::get('app.path');      // from .env: APP_PATH
```

### **Method C: Using Config Class Helper Methods (Backward Compatible)**

```php
<?php
use upMVC\Config;

// New helper methods that check .env first, then constants
$sitePath = Config::getSitePath();       // Checks SITE_PATH in .env, falls back to constant
$domainName = Config::getDomainName();   // Checks DOMAIN_NAME in .env, falls back to constant

// Old way still works (but not recommended)
$sitePath = Config::SITE_PATH;           // Direct constant access
```

---

## 3. Complete Migration Example

### **Before: Using Constants in Config.php**

```php
<?php
// Old Config.php with hardcoded values
class Config
{
    public const SITE_PATH = '/upMVC';
    public const DOMAIN_NAME = 'http://localhost';
    public const DB_HOST = '127.0.0.1';
    public const DB_NAME = 'mydb';
}

// Usage in your code
$sitePath = Config::SITE_PATH;
$baseUrl = Config::DOMAIN_NAME . Config::SITE_PATH;
```

### **After: Using .env Values**

```env
# .env file
SITE_PATH=/upMVC
DOMAIN_NAME=http://localhost
DB_HOST=127.0.0.1
DB_NAME=mydb
```

```php
<?php
// New Config.php with .env support
use upMVC\Config\Environment;

class Config
{
    // Keep constants as fallback
    public const SITE_PATH = '/upMVC';
    public const DOMAIN_NAME = 'http://localhost';
    
    // Add helper methods
    public static function getSitePath(): string
    {
        return Environment::get('SITE_PATH', self::SITE_PATH);
    }
    
    public static function getDomainName(): string
    {
        return Environment::get('DOMAIN_NAME', self::DOMAIN_NAME);
    }
}

// Usage in your code (seamless!)
$sitePath = Config::getSitePath();
$baseUrl = Config::getDomainName() . Config::getSitePath();
```

---

## 4. Real-World Examples

### **Example 1: Module Controller**

```php
<?php
namespace Dashboard;

use upMVC\Config\Environment;
use upMVC\Config\ConfigManager;

class Controller
{
    private $apiKey;
    private $basePath;
    
    public function __construct()
    {
        // Load from .env
        $this->apiKey = Environment::get('MY_API_KEY');
        $this->basePath = Environment::get('SITE_PATH', '/');
    }
    
    public function index()
    {
        // Check environment
        if (Environment::isDevelopment()) {
            echo "Debug Mode: Enabled<br>";
            echo "API Key: " . $this->apiKey . "<br>";
        }
        
        echo "Base Path: " . $this->basePath;
    }
}
```

### **Example 2: Database Connection**

```php
<?php
use upMVC\Config\Environment;

class Database
{
    private $pdo;
    
    public function __construct()
    {
        $host = Environment::get('DB_HOST', '127.0.0.1');
        $port = Environment::get('DB_PORT', '3306');
        $dbname = Environment::get('DB_NAME', 'upmvc');
        $user = Environment::get('DB_USER', 'root');
        $pass = Environment::get('DB_PASS', '');
        
        $dsn = "mysql:host={$host};port={$port};dbname={$dbname}";
        $this->pdo = new PDO($dsn, $user, $pass);
    }
}
```

### **Example 3: Defining Constants from .env**

```php
<?php
// In your bootstrap or Start.php
use upMVC\Config\Environment;

// Load environment
Environment::load();

// Define constants from .env
define('BASE_URL', Environment::get('DOMAIN_NAME') . Environment::get('SITE_PATH'));
define('SITEPATH', Environment::get('SITE_PATH'));
define('APP_ROOT', __DIR__);
define('DEBUG_MODE', Environment::get('APP_DEBUG', false));

// Now use them anywhere
echo BASE_URL; // http://localhost/upMVC
```

### **Example 4: Custom Configuration Values**

```env
# .env - Add your own custom values
PAYMENT_GATEWAY_KEY=sk_test_abc123
PAYMENT_GATEWAY_MODE=test
EMAIL_NOTIFICATIONS=true
MAX_UPLOAD_SIZE=10485760
ADMIN_EMAIL=admin@example.com
```

```php
<?php
use upMVC\Config\Environment;

class PaymentService
{
    public function processPayment()
    {
        $apiKey = Environment::get('PAYMENT_GATEWAY_KEY');
        $mode = Environment::get('PAYMENT_GATEWAY_MODE', 'live');
        
        if ($mode === 'test') {
            // Use test endpoint
        }
    }
}

class UploadService
{
    public function validateUpload($fileSize)
    {
        $maxSize = (int) Environment::get('MAX_UPLOAD_SIZE', 5242880);
        
        if ($fileSize > $maxSize) {
            throw new Exception("File too large");
        }
    }
}
```

---

## 5. Best Practices

### ✅ DO:
- Use `.env` for environment-specific values (database, API keys, URLs)
- Use `Environment::get()` with default values: `Environment::get('KEY', 'default')`
- Keep constants as fallbacks for backward compatibility
- Use descriptive names: `PAYMENT_API_KEY` not `PAK`
- Add comments in your `.env.example` file

### ❌ DON'T:
- Don't commit `.env` to git (add to `.gitignore`)
- Don't hardcode sensitive data in PHP files
- Don't use `.env` for values that never change
- Don't access $_ENV directly, use `Environment::get()`

---

## 6. Migration Checklist

- [ ] Create `.env` file in your project root
- [ ] Add all your constants to `.env`
- [ ] Update `Config.php` to add helper methods like `getSitePath()`
- [ ] Replace hardcoded values with `Environment::get()`
- [ ] Test in development environment
- [ ] Create `.env.example` for documentation
- [ ] Add `.env` to `.gitignore`
- [ ] Update production server with production `.env` values

---

## 7. Complete Working Example

### File: `.env`
```env
SITE_PATH=/upMVC
DOMAIN_NAME=http://localhost
APP_NAME=My upMVC App
DB_HOST=localhost
DB_NAME=myapp_db
DB_USER=root
DB_PASS=secret123
```

### File: `Config.php`
```php
<?php
namespace upMVC;

use upMVC\Config\Environment;

class Config
{
    // Fallback constants
    public const SITE_PATH = '/upMVC';
    public const DOMAIN_NAME = 'http://localhost';
    
    // Helper methods
    public static function getSitePath(): string
    {
        if (class_exists('upMVC\Config\Environment')) {
            return Environment::get('SITE_PATH', self::SITE_PATH);
        }
        return self::SITE_PATH;
    }
    
    public static function getDomainName(): string
    {
        if (class_exists('upMVC\Config\Environment')) {
            return Environment::get('DOMAIN_NAME', self::DOMAIN_NAME);
        }
        return self::DOMAIN_NAME;
    }
}
```

### File: `YourModule/Controller.php`
```php
<?php
namespace YourModule;

use upMVC\Config;
use upMVC\Config\Environment;

class Controller
{
    public function index()
    {
        // Method 1: Using helper methods
        $sitePath = Config::getSitePath();
        $domainName = Config::getDomainName();
        
        // Method 2: Direct Environment access
        $appName = Environment::get('APP_NAME', 'Default App');
        $dbHost = Environment::get('DB_HOST', 'localhost');
        
        echo "App: {$appName}<br>";
        echo "Base URL: {$domainName}{$sitePath}<br>";
        echo "DB: {$dbHost}";
    }
}
```

---

## Summary

🎯 **Quick Answer to Your Question:**

**Instead of:**
```php
public const SITE_PATH = '/upMVC';
```

**Use in .env:**
```env
SITE_PATH=/upMVC
```

**Access it:**
```php
use upMVC\Config\Environment;

$sitePath = Environment::get('SITE_PATH', '/default');
// or
$sitePath = Config::getSitePath(); // Using the new helper method
```

That's it! Now all your configuration is in `.env` and can be changed without touching code! 🚀
