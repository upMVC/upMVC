# QUICK VIEW: Where to Change Configuration in Config.php

```php
<?php
namespace upMVC;

use upMVC\Config\Environment;

class Config
{
    // ⭐⭐⭐ EDIT THIS SECTION - LINE 34-38 ⭐⭐⭐
    // =============================================
    /**
     * Fallback configuration values
     * 
     * CHANGE THESE VALUES ACCORDING TO YOUR SETUP:
     */
    private static $fallbacks = [
        'site_path' => '/upMVC',              // ← YOUR PATH HERE
        'domain_name' => 'http://localhost',  // ← YOUR DOMAIN HERE
    ];
    // =============================================
    
    
    // ⭐⭐⭐ OPTIONAL: EDIT THIS SECTION - LINE 57-77 ⭐⭐⭐
    // =============================================
    /**
     * Static configuration array
     */
    private static $config = [
        'debug' => true,              // ← Set false in production
        'timezone' => 'UTC',          // ← Your timezone
        'session' => [
            'name' => 'UPMVC_SESSION',
            'lifetime' => 3600,       // ← Session time in seconds
            'secure' => false,        // ← true for HTTPS
            'httponly' => true
        ],
        'cache' => [
            'enabled' => false,       // ← Enable in production
            'driver' => 'file',
            'ttl' => 3600
        ],
        'security' => [
            'csrf_protection' => true,
            'rate_limit' => 100       // ← Requests per minute
        ]
    ];
    // =============================================
    
    // ... rest of the class (don't edit below unless you know what you're doing)
}
```

---

## 🎯 THE MOST IMPORTANT SECTION TO EDIT:

### Lines 34-38: The $fallbacks Array

```php
private static $fallbacks = [
    'site_path' => '/upMVC',              // ← CHANGE THIS!
    'domain_name' => 'http://localhost',  // ← CHANGE THIS!
];
```

---

## 📝 Common Configurations:

### Local Development:
```php
private static $fallbacks = [
    'site_path' => '/upMVC',
    'domain_name' => 'http://localhost',
];
```

### Production (Root Directory):
```php
private static $fallbacks = [
    'site_path' => '',  // Empty!
    'domain_name' => 'https://yourdomain.com',
];
```

### Production (Subdirectory):
```php
private static $fallbacks = [
    'site_path' => '/myapp',
    'domain_name' => 'https://yourdomain.com',
];
```

---

## ⚡ Pro Tip: Use .env Instead!

Instead of editing Config.php, create/edit `.env` file:

```env
SITE_PATH=/upMVC
DOMAIN_NAME=http://localhost
```

**Why?** Because:
- ✅ No code changes
- ✅ Easy to deploy
- ✅ Different per environment
- ✅ More secure

**The $fallbacks array is just a backup if .env is missing!**

---

## 🔍 How It Works:

```
User calls: Config::getSitePath()
    ↓
Checks .env first: SITE_PATH=/upMVC
    ↓ (if found)
Returns: /upMVC ✅
    ↓ (if NOT found in .env)
Uses fallback: $fallbacks['site_path']
    ↓
Returns: /upMVC ✅
```

---

## 📍 File Locations:

| File | Location | Purpose |
|------|----------|---------|
| `Config.php` | `d:\GitHub\upMVC\etc\Config.php` | Fallback values |
| `.env` | `d:\GitHub\upMVC\etc\.env` | Primary config (RECOMMENDED) |

---

## ✅ That's It!

**Just edit lines 34-38 in Config.php, or better yet, use the .env file!** 🚀
