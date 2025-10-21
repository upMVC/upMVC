# Config::get() and Config::set() Usage Analysis

## 🔍 Search Results

### **`Config::set()` - NOT USED ANYWHERE! ❌**
```
Searched entire codebase: 0 matches
```

### **`Config::get()` - USED 3 TIMES ✅**
Used only in `initConfig()` method (same file):

```php
Line 189: date_default_timezone_set(self::get('timezone', 'UTC'));
Line 192: if (self::get('debug', false)) {
Line 207: $sessionConfig = self::get('session', []);
```

---

## 📊 Detailed Usage

### **Usage #1: Line 189 - Timezone Configuration**
```php
date_default_timezone_set(self::get('timezone', 'UTC'));
//                        ↑
//                        Gets 'UTC' from $config['timezone']
```

**What it does:**
- Reads `$config['timezone']` = `'UTC'`
- Sets PHP timezone to UTC

---

### **Usage #2: Line 192 - Debug Mode**
```php
if (self::get('debug', false)) {
//  ↑
//  Gets true from $config['debug']
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}
```

**What it does:**
- Reads `$config['debug']` = `true`
- If true, enables full error reporting
- If false, disables error display

---

### **Usage #3: Line 207 - Session Configuration**
```php
$sessionConfig = self::get('session', []);
//               ↑
//               Gets entire session array from $config
//
// Returns:
// [
//     'name' => 'UPMVC_SESSION',
//     'lifetime' => 3600,
//     'secure' => false,
//     'httponly' => true
// ]

if (isset($sessionConfig['name'])) {
    session_name($sessionConfig['name']);  // Sets session name
}

session_set_cookie_params([
    'lifetime' => $sessionConfig['lifetime'] ?? 3600,
    'secure' => $sessionConfig['secure'] ?? false,
    'httponly' => $sessionConfig['httponly'] ?? true,
    'samesite' => 'Strict'
]);
```

**What it does:**
- Reads entire `$config['session']` array
- Uses values to configure PHP session
- Sets cookie parameters

---

## 🎯 Summary

### **`get()` Method:**
- ✅ **Used**: 3 times in `initConfig()`
- ✅ **Purpose**: Read values from `$config` array
- ✅ **Scope**: Internal use only (within Config.php)

### **`set()` Method:**
- ❌ **Used**: 0 times (NOWHERE!)
- ❌ **Purpose**: Would allow changing `$config` at runtime
- ❌ **Status**: Dead code - not needed

---

## 💡 Should We Keep or Remove `set()`?

### **Arguments for REMOVING:**
1. ❌ Not used anywhere
2. ❌ $config values shouldn't change at runtime
3. ❌ Configuration should come from .env or static defaults
4. ❌ Adds complexity without benefit
5. ❌ Could lead to unexpected behavior if someone changes config mid-execution

### **Arguments for KEEPING:**
1. ✅ Might be useful for future features
2. ✅ Could be used in unit tests
3. ✅ Provides flexibility for advanced users
4. ✅ Symmetry with `get()` method

---

## 🎨 Visual Flow of `get()` Usage

```
Application Starts
       ↓
new Config()
       ↓
getReqRoute() called
       ↓
initConfig() runs
       ↓
┌──────────────────────────────────────────────┐
│          self::get() USED HERE               │
├──────────────────────────────────────────────┤
│                                              │
│  1. self::get('timezone', 'UTC')            │
│     └─> Returns: 'UTC'                      │
│     └─> Sets PHP timezone                   │
│                                              │
│  2. self::get('debug', false)               │
│     └─> Returns: true                       │
│     └─> Enables error reporting             │
│                                              │
│  3. self::get('session', [])                │
│     └─> Returns: entire session array       │
│     └─> Configures PHP session              │
│                                              │
└──────────────────────────────────────────────┘
       ↓
Session started
       ↓
Application ready
```

---

## 📝 Complete Code Context

### The `$config` Array (What `get()` Reads From):
```php
private static $config = [
    'debug' => true,              // ← Used by get('debug')
    'timezone' => 'UTC',          // ← Used by get('timezone')
    'session' => [                // ← Used by get('session')
        'name' => 'UPMVC_SESSION',
        'lifetime' => 3600,
        'secure' => false,
        'httponly' => true
    ],
    'cache' => [                  // ❌ NOT used by get()
        'enabled' => false,
        'driver' => 'file',
        'ttl' => 3600
    ],
    'security' => [               // ❌ NOT used by get()
        'csrf_protection' => true,
        'rate_limit' => 100
    ]
];
```

### What's Actually Used:
```
✅ debug       → Used to control error reporting
✅ timezone    → Used to set PHP timezone
✅ session     → Used to configure PHP session
❌ cache       → NOT used (might be for future use)
❌ security    → NOT used (might be for future use)
```

---

## 🔧 The `get()` Method - How It Works

```php
public static function get(string $key, $default = null)
{
    // Split 'session.lifetime' into ['session', 'lifetime']
    $parts = explode('.', $key);
    $config = self::$config;
    
    // Navigate through nested arrays
    foreach ($parts as $part) {
        if (isset($config[$part])) {
            $config = $config[$part];  // Go deeper
        } else {
            return $default;           // Not found, use default
        }
    }
    
    return $config;  // Found, return value
}
```

### Example Executions:

```php
// Example 1: Simple key
get('debug', false)
  ↓ explode('.', 'debug') = ['debug']
  ↓ $config = $config['debug']
  ↓ return true ✅

// Example 2: Nested key  
get('session.lifetime', 0)
  ↓ explode('.', 'session.lifetime') = ['session', 'lifetime']
  ↓ $config = $config['session']      → ['name' => ..., 'lifetime' => 3600]
  ↓ $config = $config['lifetime']     → 3600
  ↓ return 3600 ✅

// Example 3: Entire array
get('session', [])
  ↓ explode('.', 'session') = ['session']
  ↓ $config = $config['session']
  ↓ return ['name' => 'UPMVC_SESSION', 'lifetime' => 3600, ...] ✅

// Example 4: Non-existent key
get('nonexistent', 'default')
  ↓ explode('.', 'nonexistent') = ['nonexistent']
  ↓ !isset($config['nonexistent'])
  ↓ return 'default' ✅
```

---

## 🔧 The `set()` Method - NOT USED

```php
public static function set(string $key, $value): void
{
    $parts = explode('.', $key);
    $config = &self::$config;  // Reference - can modify
    
    foreach ($parts as $part) {
        if (!isset($config[$part])) {
            $config[$part] = [];
        }
        $config = &$config[$part];
    }
    
    $config = $value;  // Set the value
}
```

**This method is never called anywhere in your codebase!**

---

## 💭 My Recommendation

### **KEEP `get()` - It's Essential ✅**
Used for application initialization. Critical for:
- Setting timezone
- Configuring error reporting
- Setting up sessions

### **REMOVE `set()` - It's Dead Code ❌**
Reasons:
1. Not used anywhere
2. Config values shouldn't change at runtime
3. Makes code cleaner
4. Reduces confusion

---

## 🎯 The Real Picture

```
┌─────────────────────────────────────────────────────┐
│            Config Class Methods                     │
├─────────────────────────────────────────────────────┤
│                                                     │
│  get()              → ✅ USED (3 times in initConfig) │
│  set()              → ❌ NOT USED (dead code)        │
│  getSitePath()      → ✅ USED (in getReqRoute)       │
│  getDomainName()    → ✅ USED (in initConfig)        │
│  getReqRoute()      → ✅ USED (in Start.php)         │
│  initConfig()       → ✅ USED (by getReqRoute)       │
│  cleanUrl...()      → ✅ USED (by getReqRoute)       │
│                                                     │
└─────────────────────────────────────────────────────┘
```

---

## ✅ Conclusion

### **`get()` Method:**
- **Status**: ACTIVE and ESSENTIAL
- **Usage**: 3 times in `initConfig()`
- **Purpose**: Read configuration from `$config` array
- **Keep?**: YES! ✅

### **`set()` Method:**
- **Status**: UNUSED (Dead code)
- **Usage**: 0 times
- **Purpose**: Would change config at runtime (not needed)
- **Keep?**: NO! Remove it ❌

### **Want me to remove `set()` to make code cleaner?** 🧹
