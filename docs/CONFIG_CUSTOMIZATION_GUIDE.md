# upMVC Configuration Customization Guide

## 🎯 Quick Start: Where to Change Configuration

All configuration in upMVC can be customized in **TWO PLACES** (in order of priority):

1. **`.env` file** (RECOMMENDED - Primary configuration)
2. **`Config.php` fallbacks** (Backup if .env is missing)

---

## 📝 Method 1: Using .env File (RECOMMENDED)

### Location: `d:\GitHub\upMVC\etc\.env`

This is the **easiest and recommended way** to configure your application.

### Example .env Configuration:

```env
# Application Path & Domain
SITE_PATH=/upMVC
DOMAIN_NAME=http://localhost

# For production:
# SITE_PATH=
# DOMAIN_NAME=https://yourdomain.com

# For subdirectory:
# SITE_PATH=/myapp
# DOMAIN_NAME=https://yourdomain.com

# Application Settings
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost

# Database
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=upmvc
DB_USER=root
DB_PASS=your_password
```

### ✅ Advantages:
- No code changes needed
- Different settings per environment (dev, staging, prod)
- Easy to deploy
- Secure (never commit to git)
- Can be changed without touching PHP files

---

## 🔧 Method 2: Config.php Fallbacks

### Location: `d:\GitHub\upMVC\etc\Config.php`

If `.env` is missing, these fallback values are used.

### Where to Edit: Top of Config.php (Lines 34-38)

```php
/**
 * Fallback configuration values
 * Used only if .env file is missing or values are not set
 * 
 * IMPORTANT: Change these values according to your setup!
 * - site_path: Should be empty '' if in root, or '/folder' if in subdirectory
 * - domain_name: Your domain URL without trailing slash
 */
private static $fallbacks = [
    'site_path' => '/upMVC',              // ← CHANGE THIS
    'domain_name' => 'http://localhost',  // ← CHANGE THIS
];
```

### Common Scenarios:

#### Scenario 1: Root Directory (Domain points to your app root)
```php
private static $fallbacks = [
    'site_path' => '',  // Empty string!
    'domain_name' => 'https://yourdomain.com',
];
```

#### Scenario 2: Subdirectory (App is in a subfolder)
```php
private static $fallbacks = [
    'site_path' => '/myapp',  // Or '/folder/myapp'
    'domain_name' => 'https://yourdomain.com',
];
```

#### Scenario 3: Local Development
```php
private static $fallbacks = [
    'site_path' => '/upMVC',
    'domain_name' => 'http://localhost',
];
```

#### Scenario 4: Production Server
```php
private static $fallbacks = [
    'site_path' => '',
    'domain_name' => 'https://www.yoursite.com',
];
```

---

## 📊 Configuration Priority (Which Takes Precedence?)

```
1. .env file           (HIGHEST PRIORITY - Always checked first)
   ↓
2. Config::$fallbacks  (Used if .env missing or key not found)
   ↓
3. Method defaults     (Used if both above fail)
```

### Example:

```php
// .env file has:
SITE_PATH=/myapp

// Config.php fallback has:
'site_path' => '/upMVC'

// Result:
Config::getSitePath() returns '/myapp'  ← .env wins!
```

---

## 🎨 Complete Customization Example

### Step 1: Edit Config.php Fallbacks

```php
// File: etc/Config.php (lines 34-38)
private static $fallbacks = [
    'site_path' => '',                    // Your path
    'domain_name' => 'https://mysite.com', // Your domain
];
```

### Step 2: Edit .env File (RECOMMENDED)

```env
# File: etc/.env
SITE_PATH=
DOMAIN_NAME=https://mysite.com
APP_ENV=production
APP_DEBUG=false
```

### Step 3: Test Your Configuration

```php
<?php
// Test script
require_once 'vendor/autoload.php';

use upMVC\Config;
use upMVC\Config\Environment;

echo "Site Path: " . Config::getSitePath() . "\n";
echo "Domain: " . Config::getDomainName() . "\n";
echo "Base URL: " . Config::getDomainName() . Config::getSitePath() . "\n";
```

---

## 🛠️ Additional Configuration Options

### General Config Array (Lines 57-77 in Config.php)

```php
private static $config = [
    'debug' => true,              // ← Set false in production
    'timezone' => 'UTC',          // ← Change to your timezone
    'session' => [
        'name' => 'UPMVC_SESSION',
        'lifetime' => 3600,       // ← Session duration (seconds)
        'secure' => false,        // ← Set true for HTTPS only
        'httponly' => true
    ],
    'cache' => [
        'enabled' => false,       // ← Enable for production
        'driver' => 'file',
        'ttl' => 3600            // ← Cache lifetime
    ],
    'security' => [
        'csrf_protection' => true,
        'rate_limit' => 100      // ← Max requests per minute
    ]
];
```

### Override via .env:

```env
APP_DEBUG=false
APP_TIMEZONE=America/New_York
SESSION_LIFETIME=7200
CACHE_ENABLED=true
CACHE_TTL=86400
```

---

## 📋 Quick Reference Cheat Sheet

| What You Want | Where to Change | Example Value |
|--------------|-----------------|---------------|
| **Site Path** | `.env` → `SITE_PATH` | `/myapp` or `` (empty) |
| **Domain** | `.env` → `DOMAIN_NAME` | `https://yourdomain.com` |
| **Debug Mode** | `.env` → `APP_DEBUG` | `true` or `false` |
| **Database** | `.env` → `DB_*` | See .env file |
| **Timezone** | `.env` → `APP_TIMEZONE` | `America/New_York` |
| **Fallbacks** | `Config.php` → `$fallbacks` | Edit array |

---

## ⚠️ Important Notes

### 1. Site Path Rules:
- **Root directory:** Use empty string `''` or leave blank in .env
- **Subdirectory:** Use `/folder` (with leading slash, no trailing slash)
- **Nested:** Use `/parent/child` (with leading slash, no trailing slash)

### 2. Domain Name Rules:
- Include protocol: `http://` or `https://`
- No trailing slash: `https://domain.com` ✅ not `https://domain.com/` ❌
- No path: `https://domain.com` ✅ not `https://domain.com/app` ❌

### 3. Security:
- Never commit `.env` to git
- Add `.env` to `.gitignore`
- Create `.env.example` with dummy values for team members
- Use different `.env` for dev, staging, production

---

## 🚀 Deployment Checklist

### Development → Production:

- [ ] Update `.env` with production values
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Change `DOMAIN_NAME` to production domain
- [ ] Set `SITE_PATH` appropriately
- [ ] Update database credentials
- [ ] Enable cache (`CACHE_ENABLED=true`)
- [ ] Set secure session (`SESSION_SECURE=true` for HTTPS)

### Example Production .env:

```env
SITE_PATH=
DOMAIN_NAME=https://www.yoursite.com
APP_ENV=production
APP_DEBUG=false
APP_URL=https://www.yoursite.com

DB_HOST=localhost
DB_NAME=production_db
DB_USER=prod_user
DB_PASS=strong_password_here

SESSION_SECURE=true
SESSION_LIFETIME=7200
CACHE_ENABLED=true
CACHE_TTL=86400
```

---

## 💡 Pro Tips

### 1. Use .env for Everything
Instead of editing PHP files, put everything in `.env`:

```env
PAYMENT_API_KEY=sk_live_abc123
EMAIL_FROM=noreply@yoursite.com
UPLOAD_MAX_SIZE=10485760
ADMIN_EMAIL=admin@yoursite.com
```

Access in code:
```php
$apiKey = Environment::get('PAYMENT_API_KEY');
```

### 2. Create Environment-Specific Files
```
.env              ← Never commit
.env.example      ← Commit this (with dummy values)
.env.development  ← Your local settings
.env.staging      ← Staging server settings
.env.production   ← Production server settings
```

### 3. Use Config Helper Methods
```php
// Instead of hardcoding
$baseUrl = 'http://localhost/upMVC';

// Use this
$baseUrl = Config::getDomainName() . Config::getSitePath();
```

---

## 🎓 Summary

### Quick Setup (3 Steps):

1. **Edit `.env` file** with your settings
2. **Edit `Config.php` fallbacks** (lines 34-38) as backup
3. **Test your application**

### The Golden Rule:
> **Always use .env for configuration. Edit Config.php fallbacks only as emergency backup values!**

---

## 🆘 Troubleshooting

### Problem: My changes don't work!

**Solution:** Check this order:
1. Is `.env` file loaded? (Check `bootstrapApplication()` is called)
2. Is the key correct in `.env`? (Case-sensitive!)
3. Did you restart your server/clear cache?
4. Is `.env` in the correct location? (`etc/.env`)

### Problem: Site path is wrong!

**Check:**
```php
echo Config::getSitePath();  // What does this show?
echo Environment::get('SITE_PATH');  // What does this show?
```

### Problem: Can't find where to edit!

**Quick reference:**
- `.env` file: `d:\GitHub\upMVC\etc\.env`
- Fallbacks: `d:\GitHub\upMVC\etc\Config.php` (lines 34-38)
- Config array: `d:\GitHub\upMVC\etc\Config.php` (lines 57-77)

---

**That's it! Now you can customize your upMVC application easily!** 🎉
