# URL Handling in upMVC - Complete Explanation

## Question: Is `intended_url` a fully qualified URL or just a route?

**Answer:** It's a **full path from domain root** (not fully qualified with domain, but includes BASE_URL subdirectory).

## The Data Flow

### 1. Configuration (Config.php)
```php
public const DOMAIN_NAME = 'http://localhost';
public const SITE_PATH = '/upMVC';

// Combined:
define('BASE_URL', 'http://localhost/upMVC');
```

### 2. Browser Request
User visits: `http://localhost/upMVC/moda`

### 3. PHP Receives (Start.php)
```php
$_SERVER['REQUEST_URI'] = '/upMVC/moda'  // ← Full path from domain root
//                         ^^^^^^ SITE_PATH included!
//                               ^^^^^ Route
```

**Important:** `$_SERVER['REQUEST_URI']` contains:
- ✅ SITE_PATH (`/upMVC`)
- ✅ Route (`/moda`)
- ✅ Query params if any (`?id=123`)
- ❌ Domain name (`http://localhost`)

### 4. Route Extraction (Config.php)
```php
// getReqRoute() strips SITE_PATH to get clean route
$reqRoute = '/moda'  // Clean route for matching
$reqURI = '/upMVC/moda'  // Full URI preserved
```

### 5. Middleware Receives (AuthMiddleware.php)
```php
$request = [
    'route' => '/moda',              // Clean route for matching
    'uri' => '/upMVC/moda',          // Full URI from REQUEST_URI
    'method' => 'GET'
];
```

### 6. Session Storage (AuthMiddleware.php)
```php
// What's stored:
$_SESSION['intended_url'] = $request['uri'];  // '/upMVC/moda'

// NOT just the route:
// $_SESSION['intended_url'] = $request['route'];  // '/moda' ❌ WRONG
```

### 7. Redirect After Login (Auth Controller.php)
```php
$intendedUrl = $_SESSION['intended_url'];  // '/upMVC/moda'

// Redirect directly:
header("Location: " . $intendedUrl);  // "Location: /upMVC/moda"
```

### 8. Browser Interprets
```
Location: /upMVC/moda

Browser converts to:
http://localhost/upMVC/moda
^^^^^^^^^^^^^^^  ^^^^^^^^^^^
Current domain + Header value
```

## Why This Works

### HTTP Location Header Rules:
1. **Absolute URL:** `Location: http://localhost/upMVC/moda` ✅ Works
2. **Absolute Path:** `Location: /upMVC/moda` ✅ Works (browser adds domain)
3. **Relative Path:** `Location: moda` ❌ Problematic (relative to current path)

### We use Absolute Path (option 2):
```php
// Stored in session:
$_SESSION['intended_url'] = '/upMVC/moda';  // Absolute path from domain root

// Redirect:
header("Location: /upMVC/moda");  // Browser interprets as http://localhost/upMVC/moda
```

## Common Mistakes to Avoid

### ❌ WRONG: Store only the route
```php
// Bad:
$_SESSION['intended_url'] = $request['route'];  // '/moda'

// Redirect:
header("Location: /moda");  // Goes to http://localhost/moda (404!)
//                  ^^^^^ Missing /upMVC!
```

### ❌ WRONG: Double-prepend BASE_URL
```php
// Bad:
$_SESSION['intended_url'] = '/upMVC/moda';  // Already has /upMVC
header("Location: " . BASE_URL . $intendedUrl);  
// Results in: Location: http://localhost/upMVC/upMVC/moda ❌
```

### ✅ CORRECT: Use REQUEST_URI as-is
```php
// Good:
$_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];  // '/upMVC/moda'
header("Location: " . $intendedUrl);  // Location: /upMVC/moda ✅
```

## Visual Flow Chart

```
User Action: Visit http://localhost/upMVC/moda (not logged in)
     ↓
┌────────────────────────────────────────────────────────────┐
│ PHP: $_SERVER['REQUEST_URI'] = '/upMVC/moda'              │
└────────────────────────────────────────────────────────────┘
     ↓
┌────────────────────────────────────────────────────────────┐
│ Start.php: $this->reqURI = '/upMVC/moda'                  │
│            $this->reqRoute = '/moda' (cleaned)             │
└────────────────────────────────────────────────────────────┘
     ↓
┌────────────────────────────────────────────────────────────┐
│ Router.php: $request['uri'] = '/upMVC/moda'               │
│             $request['route'] = '/moda'                    │
└────────────────────────────────────────────────────────────┘
     ↓
┌────────────────────────────────────────────────────────────┐
│ AuthMiddleware: $_SESSION['intended_url'] = '/upMVC/moda' │
│                 Redirect to /auth                          │
└────────────────────────────────────────────────────────────┘
     ↓
User logs in
     ↓
┌────────────────────────────────────────────────────────────┐
│ Auth Controller: $url = $_SESSION['intended_url']         │
│                  header("Location: /upMVC/moda")           │
└────────────────────────────────────────────────────────────┘
     ↓
┌────────────────────────────────────────────────────────────┐
│ Browser: Redirects to http://localhost/upMVC/moda         │
└────────────────────────────────────────────────────────────┘
```

## Different Deployment Scenarios

### Scenario 1: Subfolder Installation (Current)
```php
SITE_PATH = '/upMVC'
User visits: http://localhost/upMVC/moda
REQUEST_URI = '/upMVC/moda' ✅
Redirect to: '/upMVC/moda' ✅
```

### Scenario 2: Root Installation
```php
SITE_PATH = ''
User visits: http://example.com/moda
REQUEST_URI = '/moda' ✅
Redirect to: '/moda' ✅
```

### Scenario 3: Multiple Subfolders
```php
SITE_PATH = '/projects/upMVC'
User visits: http://localhost/projects/upMVC/moda
REQUEST_URI = '/projects/upMVC/moda' ✅
Redirect to: '/projects/upMVC/moda' ✅
```

## Key Takeaway

**`$_SERVER['REQUEST_URI']` is the perfect value to store** because:
1. ✅ Contains full path from domain root
2. ✅ Includes SITE_PATH automatically
3. ✅ Works with any deployment configuration
4. ✅ Can be used directly in Location header
5. ✅ Preserves query parameters

**Never store just the route** (`$request['route']`) in `intended_url` because it's missing the SITE_PATH prefix needed for correct redirection.

## Testing

To verify, add debug output:
```php
// In AuthMiddleware:
error_log("Storing: " . $request['uri']);

// In Auth Controller:
error_log("Redirecting to: " . $_SESSION['intended_url']);
```

Check your debug log:
```log
[timestamp] DEBUG AuthMiddleware - Storing intended_url: /upMVC/moda
[timestamp] DEBUG Auth Controller - intended_url in session: /upMVC/moda
[timestamp] DEBUG Auth Controller - Redirecting to intended: /upMVC/moda
```

All values should include the `/upMVC` prefix! ✅
