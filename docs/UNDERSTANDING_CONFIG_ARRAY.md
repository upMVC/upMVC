# Understanding $config Array in Config.php

## 🎯 What is $config?

The `$config` array is a **static property** that stores application-wide configuration settings in memory. Think of it as a **local storage box** for settings.

```php
private static $config = [
    'debug' => true,
    'timezone' => 'UTC',
    'session' => [
        'name' => 'UPMVC_SESSION',
        'lifetime' => 3600,
        // ...
    ],
    // ...
];
```

---

## 🤔 Your Question: "Is it Overwriting Something?"

**Short Answer:** NO, it's NOT overwriting. It's a **SEPARATE storage system**.

**Long Answer:** Let me show you how it all works together...

---

## 🏗️ Three Storage Systems in Your upMVC:

### 1. **`$fallbacks` Array** (Lines 44-47)
```php
private static $fallbacks = [
    'site_path' => '/upMVC',
    'domain_name' => 'http://localhost',
];
```
**Purpose:** Backup values for `getSitePath()` and `getDomainName()`  
**Used by:** `Config::getSitePath()`, `Config::getDomainName()`  
**Priority:** Last resort (only if .env missing)

---

### 2. **`$config` Array** (Lines 57-77)
```php
private static $config = [
    'debug' => true,
    'timezone' => 'UTC',
    'session' => [...],
    'cache' => [...],
    'security' => [...]
];
```
**Purpose:** General application settings (debug, timezone, session, cache, security)  
**Used by:** `Config::get()`, `Config::set()`, `initConfig()`  
**Priority:** Hardcoded defaults (can be changed at runtime with `Config::set()`)

---

### 3. **`.env` File + `Environment` Class**
```env
SITE_PATH=/upMVC
DOMAIN_NAME=http://localhost
APP_DEBUG=true
DB_HOST=localhost
```
**Purpose:** Environment-specific configuration (loaded from file)  
**Used by:** `Environment::get()`, `ConfigManager::get()`  
**Priority:** HIGHEST (always checked first)

---

## 📊 How They Work Together (Flow Chart)

```
┌─────────────────────────────────────────────────────────────┐
│                    USER CALLS METHOD                        │
└─────────────────────────────────────────────────────────────┘
                              ↓
                    ┌─────────┴─────────┐
                    │                   │
         ┌──────────▼──────────┐   ┌───▼────────────┐
         │ Config::get()       │   │ Config::       │
         │ (general settings)  │   │ getSitePath()  │
         └──────────┬──────────┘   └───┬────────────┘
                    │                  │
         ┌──────────▼──────────┐   ┌───▼────────────┐
         │ Checks $config[]    │   │ Checks .env    │
         │ Returns value       │   │ via Environment│
         └─────────────────────┘   └───┬────────────┘
                                       │
                              ┌────────▼────────┐
                              │ If not in .env: │
                              │ Use $fallbacks[]│
                              └─────────────────┘
```

---

## 🔍 Real Examples - How They're Used

### Example 1: Timezone (Uses `$config`)

```php
// In initConfig() - Line 213
date_default_timezone_set(self::get('timezone', 'UTC'));
//                        ↑
//                        Calls Config::get()
//                        Looks in $config array
//                        Finds: 'timezone' => 'UTC'
```

**Flow:**
1. Call: `self::get('timezone', 'UTC')`
2. Looks in: `$config['timezone']`
3. Finds: `'UTC'`
4. Returns: `'UTC'`
5. Sets timezone to UTC ✅

---

### Example 2: Debug Mode (Uses `$config`)

```php
// In initConfig() - Line 216
if (self::get('debug', false)) {
//  ↑
//  Calls Config::get()
//  Looks in $config['debug']
//  Finds: true
    error_reporting(E_ALL);  // ← This runs because debug is true
}
```

**Flow:**
1. Call: `self::get('debug', false)`
2. Looks in: `$config['debug']`
3. Finds: `true`
4. Returns: `true`
5. Enables full error reporting ✅

---

### Example 3: Site Path (Uses `$fallbacks`)

```php
// In getSitePath() - Line 107
return Environment::get('SITE_PATH', self::$fallbacks['site_path']);
//     ↑                            ↑
//     Checks .env first            Uses $fallbacks as backup
```

**Flow:**
1. Call: `Config::getSitePath()`
2. First checks: `.env` file for `SITE_PATH`
3. If found in .env: Returns that value ✅
4. If NOT in .env: Uses `$fallbacks['site_path']` = '/upMVC' ✅

---

### Example 4: Session Config (Uses `$config`)

```php
// In initConfig() - Line 231
$sessionConfig = self::get('session', []);
//               ↑
//               Gets the entire 'session' array from $config
//
// Returns:
// [
//     'name' => 'UPMVC_SESSION',
//     'lifetime' => 3600,
//     'secure' => false,
//     'httponly' => true
// ]
```

**Then later:**
```php
if (isset($sessionConfig['name'])) {
    session_name($sessionConfig['name']);  // Sets session name to 'UPMVC_SESSION'
}
```

---

## 🎨 Visual Comparison

```php
┌─────────────────────────────────────────────────────────────┐
│                    $fallbacks Array                         │
│                 (Lines 44-47)                               │
├─────────────────────────────────────────────────────────────┤
│ 'site_path' => '/upMVC'                                     │
│ 'domain_name' => 'http://localhost'                         │
├─────────────────────────────────────────────────────────────┤
│ Used by: getSitePath(), getDomainName()                     │
│ Purpose: Backup if .env missing                             │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│                     $config Array                           │
│                   (Lines 57-77)                             │
├─────────────────────────────────────────────────────────────┤
│ 'debug' => true                                             │
│ 'timezone' => 'UTC'                                         │
│ 'session' => [name, lifetime, secure, httponly]            │
│ 'cache' => [enabled, driver, ttl]                          │
│ 'security' => [csrf_protection, rate_limit]                │
├─────────────────────────────────────────────────────────────┤
│ Used by: get(), set(), initConfig()                         │
│ Purpose: General app settings                               │
└─────────────────────────────────────────────────────────────┘
```

---

## 💡 Understanding "Overwriting"

### **Does $config overwrite $fallbacks?**
**NO!** They're completely separate:

```php
// These are DIFFERENT storage boxes
private static $fallbacks = [...];  // Box 1
private static $config = [...];     // Box 2

// They never touch each other!
```

### **Does $config overwrite .env?**
**NO!** They work in PRIORITY order:

```php
// For site_path:
1. Check .env first         → HIGHEST PRIORITY
2. If not found, use $fallbacks → FALLBACK

// For general settings (debug, timezone, etc):
1. Use $config values       → ONLY SOURCE
```

---

## 🔧 Can You Change $config at Runtime?

**YES!** Using `Config::set()`:

```php
// Original value in $config
$config['debug'] = true;

// Change it at runtime
Config::set('debug', false);

// Now Config::get('debug') returns false
```

**Example:**
```php
// Before
echo Config::get('timezone');  // UTC

// Change it
Config::set('timezone', 'America/New_York');

// After
echo Config::get('timezone');  // America/New_York
```

---

## 📝 Complete Method Breakdown

### **`Config::get()` Method**

```php
public static function get(string $key, $default = null)
{
    $parts = explode('.', $key);     // Split 'session.lifetime' into ['session', 'lifetime']
    $config = self::$config;         // Start with $config array
    
    foreach ($parts as $part) {
        if (isset($config[$part])) {
            $config = $config[$part]; // Drill down into nested arrays
        } else {
            return $default;          // Not found, return default
        }
    }
    
    return $config;                   // Found, return value
}
```

**Usage Examples:**
```php
Config::get('debug');                    // Returns: true
Config::get('timezone');                 // Returns: 'UTC'
Config::get('session.lifetime');         // Returns: 3600
Config::get('session.name');             // Returns: 'UPMVC_SESSION'
Config::get('cache.enabled');            // Returns: false
Config::get('security.csrf_protection'); // Returns: true
Config::get('nonexistent', 'default');   // Returns: 'default'
```

---

### **`Config::set()` Method**

```php
public static function set(string $key, $value): void
{
    $parts = explode('.', $key);     // Split key into parts
    $config = &self::$config;        // Reference to $config (can modify)
    
    foreach ($parts as $part) {
        if (!isset($config[$part])) {
            $config[$part] = [];     // Create nested array if needed
        }
        $config = &$config[$part];   // Move deeper
    }
    
    $config = $value;                // Set the value
}
```

**Usage Examples:**
```php
Config::set('debug', false);              // Changes debug mode
Config::set('timezone', 'Europe/London'); // Changes timezone
Config::set('session.lifetime', 7200);    // Changes session lifetime to 2 hours
Config::set('cache.enabled', true);       // Enables cache
Config::set('new_setting', 'value');      // Creates new setting
```

---

## 🎯 Summary

### **What is $config doing?**

1. **Storing general application settings** (debug, timezone, session, cache, security)
2. **Providing dot-notation access** via `Config::get()` and `Config::set()`
3. **Being used by `initConfig()`** to configure the application on startup
4. **NOT overwriting anything** - it's a separate storage system

### **The Three Storage Systems:**

```
$fallbacks      → For site_path and domain_name (backup)
$config         → For general settings (debug, timezone, session, etc)
.env + Environment → For environment-specific values (PRIMARY)
```

### **They Work Together Like This:**

```php
// For paths/domain:
getSitePath() → Checks .env → Falls back to $fallbacks

// For general settings:
initConfig() → Uses $config directly

// For environment values:
Environment::get() → Reads from .env file
```

---

## 🚀 Practical Example - Full Application Startup

```php
// 1. Application starts
new Start();

// 2. Constructor runs
public function __construct() {
    $this->bootstrapApplication();  // Loads .env
    $this->initializeRequest();
}

// 3. bootstrapApplication() loads .env
ConfigManager::load();
Environment::load();  // ← .env values now in memory

// 4. initializeRequest() uses Config
$config = new Config();
$this->reqRoute = $config->getReqRoute($this->reqURI);

// 5. getReqRoute() calls initConfig()
private function initConfig() {
    date_default_timezone_set(self::get('timezone', 'UTC'));
    //                        ↑ Gets from $config['timezone']
    
    if (self::get('debug', false)) {
    //  ↑ Gets from $config['debug']
        error_reporting(E_ALL);
    }
    
    define('BASE_URL', self::getDomainName() . self::getSitePath());
    //                 ↑                       ↑
    //                 Gets from .env          Gets from .env
    //                 (or $fallbacks)         (or $fallbacks)
}
```

---

## ✅ Key Takeaways

1. ✅ `$config` is a **separate storage** for general settings
2. ✅ `$fallbacks` is **separate storage** for path/domain defaults
3. ✅ `.env` has **highest priority** for path/domain
4. ✅ Nothing is **overwriting** anything - they serve different purposes
5. ✅ You can **change $config at runtime** with `Config::set()`
6. ✅ `$config` is used **internally** by `initConfig()` to setup the app

**They're all working together harmoniously!** 🎵
