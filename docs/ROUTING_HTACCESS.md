# 🛣️ Routing & .htaccess System

## Overview

upMVC uses a **two-layer routing system** combining Apache's `.htaccess` URL rewriting with PHP Router class for maximum flexibility and SEO-friendly URLs.

**Architecture:**
```
Browser Request
    ↓
1. Apache .htaccess (URL Rewriting)
    ↓
2. index.php (Entry Point)
    ↓
3. Start.php (Initialization)
    ↓
4. Router.php (Route Matching)
    ↓
5. Module Routes.php (Route Registration)
    ↓
6. Controller->Method (Execution)
```

---

## 🔧 Layer 1: .htaccess URL Rewriting

### Location: `/.htaccess`

The `.htaccess` file handles URL rewriting **before** PHP processes the request.

### Purpose

✅ **SEO-Friendly URLs** - Transform `/test-product-123` to `/test?param=product-123`  
✅ **Clean URLs** - Remove `.php` extensions and query strings from URLs  
✅ **Pre-Processing** - Convert complex URLs to simple GET parameters  
✅ **Module-Specific Rules** - Custom rewrite rules per module

### Complete .htaccess File

```apache
Options -Indexes
RewriteEngine on
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f

# Level One - Test Module (Simple Parameters)
RewriteRule ^test-([\w\d~%.:_\-]+)$ test?param=$1 [NC]
RewriteRule ^test-([\w\d~%.:_\-]+)/([\w\d~%.:_\-]+)$ test?param=$1&another=$2 [NC]  

# Level Two - Moda Module (Page Parameters)
RewriteRule ^moda-page-([\w\d~%.:_\-]+)$ moda-page?param=$1 [NC] 
RewriteRule ^moda-page-([\w\d~%.:_\-]+)/([\w\d~%.:_\-]+)$ test?param=$1&another=$2 [NC] 

# Level Custom - UserORM Module (CRUD Operations)
RewriteRule ^usersorm/edit/([\w\d~%.:_\-]+)$ usersorm/edit?param=$1 [NC]
RewriteRule ^usersorm/delete/([\w\d~%.:_\-]+)$ usersorm/delete?param=$1 [NC]
RewriteRule ^usersorm/update/([\w\d~%.:_\-]+)$ usersorm/update?param=$1 [NC]

# Level Custom - Admin Module (Nested Routes)
RewriteRule ^admin/users/edit/([\w\d~%.:_\-]+)$ admin/users/edit/$1 [NC]
RewriteRule ^admin/users/delete/([\w\d~%.:_\-]+)$ admin/users/delete/$1 [NC]
RewriteRule ^admin/users/add$ admin/users/add [NC]
RewriteRule ^admin/users$ admin/users [NC]
RewriteRule ^admin$ admin [NC]

# Final Rule - Route everything to index.php
RewriteRule (.+) index.php [QSA,L]
```

### Understanding RewriteRule Syntax

```apache
RewriteRule ^pattern$ replacement [flags]
```

**Components:**
- `^` - Start of URL
- `$` - End of URL
- `[\w\d~%.:_\-]+` - Match alphanumeric, dash, underscore, etc.
- `(...)` - Capture group ($1, $2, $3, etc.)
- `[NC]` - No Case (case-insensitive)
- `[QSA,L]` - Query String Append, Last rule

### .htaccess Rewrite Levels

#### Level One: Simple Parameter Conversion

**Test Module** - Single and double parameter URLs

```apache
# One Parameter
RewriteRule ^test-([\w\d~%.:_\-]+)$ test?param=$1 [NC]

# Two Parameters
RewriteRule ^test-([\w\d~%.:_\-]+)/([\w\d~%.:_\-]+)$ test?param=$1&another=$2 [NC]
```

**Examples:**
```
User requests:    /test-shoes
Rewrites to:      /test?param=shoes

User requests:    /test-shoes/nike
Rewrites to:      /test?param=shoes&another=nike
```

#### Level Two: Page-Specific Patterns

**Moda Module** - Page name in URL

```apache
RewriteRule ^moda-page-([\w\d~%.:_\-]+)$ moda-page?param=$1 [NC]
```

**Examples:**
```
User requests:    /moda-page-about
Rewrites to:      /moda-page?param=about

User requests:    /moda-page-contact
Rewrites to:      /moda-page?param=contact
```

#### Level Custom: CRUD Operations

**UserORM Module** - RESTful-style URLs

```apache
RewriteRule ^usersorm/edit/([\w\d~%.:_\-]+)$ usersorm/edit?param=$1 [NC]
RewriteRule ^usersorm/delete/([\w\d~%.:_\-]+)$ usersorm/delete?param=$1 [NC]
RewriteRule ^usersorm/update/([\w\d~%.:_\-]+)$ usersorm/update?param=$1 [NC]
```

**Examples:**
```
User requests:    /usersorm/edit/42
Rewrites to:      /usersorm/edit?param=42

User requests:    /usersorm/delete/123
Rewrites to:      /usersorm/delete?param=123
```

#### Level Custom: Nested Admin Routes

**Admin Module** - Preserve nested structure

```apache
RewriteRule ^admin/users/edit/([\w\d~%.:_\-]+)$ admin/users/edit/$1 [NC]
RewriteRule ^admin/users/delete/([\w\d~%.:_\-]+)$ admin/users/delete/$1 [NC]
RewriteRule ^admin/users/add$ admin/users/add [NC]
RewriteRule ^admin/users$ admin/users [NC]
RewriteRule ^admin$ admin [NC]
```

**Examples:**
```
User requests:    /admin/users/edit/5
Keeps as:         /admin/users/edit/5 (passed to Router)

User requests:    /admin/users
Keeps as:         /admin/users (passed to Router)

User requests:    /admin
Keeps as:         /admin (passed to Router)
```

**Why no query string?** Admin module preserves clean URLs all the way through to the Router.

---

## 🎯 Layer 2: PHP Router Class

### Location: `/etc/Router.php`

After `.htaccess` processes the URL, the Router class matches routes to controllers.

### How It Works

```php
// 1. Request comes in (after .htaccess)
Request: /test?param=shoes

// 2. Router matches exact route
$router->addRoute('/test', Controller::class, 'display');

// 3. Controller method executes
public function display() {
    $param = $_GET['param']; // 'shoes'
    // Process...
}
```

### Route Registration

**Module Routes File:** `/modules/{module}/routes/Routes.php`

```php
namespace Module\Routes;

use Module\Controller;

class Routes {
    public function routes($router) {
        // Register routes
        $router->addRoute('/path', Controller::class, 'method');
    }
}
```

---

## 📦 Module Examples

### 1️⃣ Test Module - E-Commerce Product Routes

**File:** `/modules/test/routes/Routes.php`

#### Basic Routes

```php
namespace Test\Routes;
use Test\Controller;

class Routes {
    public function routes($router) {
        // Homepage and aliases
        $router->addRoute('/', Controller::class, 'display');
        $router->addRoute('/index.php', Controller::class, 'display');
        $router->addRoute('/test', Controller::class, 'display');
        $router->addRoute('/test/subpage', Controller::class, 'display');
        
        // Modern view
        $router->addRoute('/test/modern', Controller::class, 'displayModern');
        $router->addRoute('/test-modern', Controller::class, 'displayModern');
    }
}
```

#### Parameter Routes (Work with .htaccess)

```php
// GET parameters routes (see .htaccess Level One)
// One parameter
$router->addRoute('/test-one', Controller::class, 'display');
$router->addRoute('/test-page-one', Controller::class, 'display');

// Two parameters
$router->addRoute('/test-one/two', Controller::class, 'display');
$router->addRoute('/test-page-one/two', Controller::class, 'display');
```

**URL Flow:**
```
User visits:      /test-shoes
.htaccess:        /test?param=shoes
Router matches:   /test-one route
Controller gets:  $_GET['param'] = 'shoes'
```

#### Dynamic Product Routes (Database-Driven)

```php
// Generate routes for products from database
$i = 0;
$routesArray = [];

while ($i < 5) {
    $routesArray[$i] = ['/test-a' . $i, Controller::class, 'display'];
    $i++;
}

foreach ($routesArray as $key => $value) {
    $router->addRoute($value[0], $value[1], $value[2]);
}
```

**Result:** Creates routes `/test-a0`, `/test-a1`, `/test-a2`, `/test-a3`, `/test-a4`

**Use Case:** Generate thousands of product routes from database without hardcoding each one.

**E-Commerce Example:**
```php
// Get products from database
$products = $model->getAllProducts();

foreach ($products as $product) {
    // /test-product-{id} or /test-{slug}
    $route = '/test-' . $product['slug'];
    $router->addRoute($route, Controller::class, 'displayProduct');
}
```

---

### 2️⃣ Moda Module - Multi-Page Testing

**File:** `/modules/moda/routes/Routes.php`

```php
namespace Moda\Routes;
use Moda\Controller;

class Routes {
    public function routes($router) {
        // Basic routes
        $router->addRoute('/moda.php', Controller::class, 'display');
        $router->addRoute('/moda', Controller::class, 'display');
        $router->addRoute('/moda/subpage', Controller::class, 'display');
        
        // Parameter routes (work with .htaccess Level Two)
        $router->addRoute('/moda-page-one', Controller::class, 'display');
        $router->addRoute('/moda-page-one/two', Controller::class, 'display');
    }
}
```

**URL Flow:**
```
User visits:      /moda-page-about
.htaccess:        /moda-page?param=about
Router matches:   /moda-page-one route
Controller gets:  $_GET['param'] = 'about'
```

**Use Cases:**
- Multi-page applications
- Landing pages with parameters
- A/B testing different page versions
- Dynamic content based on page name

---

### 3️⃣ UserORM Module - Dynamic CRUD Routes

**File:** `/modules/userorm/routes/Routes.php`

This is the most advanced example - routes are generated from database records!

```php
namespace Userorm\Routes;
use Userorm\Controller;
use Userorm\Model;

class Routes {
    private $model;
    private $table = 'users';
    
    public function routes($router) {
        $this->model = new Model();
        
        // Static routes
        $router->addRoute('/usersorm', Controller::class, 'display');
        $router->addRoute('/usersorm/getall/320', Controller::class, 'getAll');
        $router->addRoute('/usersorm/create', Controller::class, 'display');
        $router->addRoute('/usersorm/store', Controller::class, 'display');
        
        // Get all users from database
        $users = $this->model->getAllUsers($this->table);
        
        // Extract user IDs
        $userIds = [];
        foreach ($users as $user) {
            $userIds[] = $user->id;
        }
        
        $usersIdsLength = count($users);
        
        // Generate EDIT routes for each user
        $i = 0;
        $routesArray = [];
        while ($i < $usersIdsLength) {
            $routesArray[$i] = ['/usersorm/edit/' . $userIds[$i], Controller::class, 'display'];
            $i++;
        }
        foreach ($routesArray as $key => $value) {
            $router->addRoute($value[0], $value[1], $value[2]);
        }
        
        // Generate UPDATE routes for each user
        $i = 0;
        $routesArray = [];
        while ($i < $usersIdsLength) {
            $routesArray[$i] = ['/usersorm/update/' . $userIds[$i], Controller::class, 'display'];
            $i++;
        }
        foreach ($routesArray as $key => $value) {
            $router->addRoute($value[0], $value[1], $value[2]);
        }
        
        // Generate DELETE routes for each user
        $i = 0;
        $routesArray = [];
        while ($i < $usersIdsLength) {
            $routesArray[$i] = ['/usersorm/delete/' . $userIds[$i], Controller::class, 'display'];
            $i++;
        }
        foreach ($routesArray as $key => $value) {
            $router->addRoute($value[0], $value[1], $value[2]);
        }
    }
}
```

**Generated Routes (if 3 users exist with IDs: 1, 5, 12):**
```
/usersorm/edit/1
/usersorm/edit/5
/usersorm/edit/12

/usersorm/update/1
/usersorm/update/5
/usersorm/update/12

/usersorm/delete/1
/usersorm/delete/5
/usersorm/delete/12
```

**URL Flow:**
```
User visits:      /usersorm/edit/5
.htaccess:        /usersorm/edit?param=5
Router matches:   /usersorm/edit/5 (exact match!)
Controller gets:  Route matched, user ID = 5
```

**Use Cases:**
- User management systems
- Blog post editing (one route per post)
- Product management (CRUD for each product)
- Any resource with database-driven IDs

**Performance Note:** Routes are generated on every request. For large datasets, use caching (see Admin module example).

---

### 4️⃣ Admin Module - Cached Dynamic Routes

**File:** `/modules/admin/routes/Routes.php`

**Problem:** UserORM approach queries database on every request - slow for 10,000+ users!

**Solution:** Cache routes to file, rebuild only when needed.

```php
namespace Admin\Routes;
use Admin\Controller;
use Admin\Model;

class Routes {
    private $model;
    private $table = 'user';
    private string $cacheFile;
    private int $cacheLifetime = 3600; // 1 hour
    
    public function __construct() {
        $this->cacheFile = __DIR__ . '/../../../etc/storage/cache/admin_routes.php';
    }
    
    public function routes($router) {
        // Static routes (always present)
        $router->addRoute('/admin', Controller::class, 'display');
        $router->addRoute('/admin/users', Controller::class, 'display');
        $router->addRoute('/admin/users/add', Controller::class, 'display');
        
        // Dynamic routes (cached)
        if ($this->isCacheValid()) {
            $this->loadCachedRoutes($router);
        } else {
            $this->rebuildCache($router);
        }
    }
    
    private function isCacheValid(): bool {
        if (!file_exists($this->cacheFile)) {
            return false;
        }
        
        $cacheAge = time() - filemtime($this->cacheFile);
        return $cacheAge < $this->cacheLifetime;
    }
    
    private function loadCachedRoutes($router): void {
        $cachedRoutes = include $this->cacheFile;
        
        foreach ($cachedRoutes as $route) {
            $router->addRoute($route['path'], $route['controller'], $route['method']);
        }
    }
    
    private function rebuildCache($router): void {
        $this->model = new Model();
        $users = $this->model->getAllUsers();
        
        $userIds = [];
        foreach ($users as $user) {
            $userIds[] = $user['id'];
        }
        
        $cachedRoutes = [];
        
        // Generate EDIT routes
        foreach ($userIds as $userId) {
            $editRoute = [
                'path' => '/admin/users/edit/' . $userId,
                'controller' => Controller::class,
                'method' => 'display'
            ];
            $cachedRoutes[] = $editRoute;
            $router->addRoute($editRoute['path'], $editRoute['controller'], $editRoute['method']);
        }
        
        // Generate DELETE routes
        foreach ($userIds as $userId) {
            $deleteRoute = [
                'path' => '/admin/users/delete/' . $userId,
                'controller' => Controller::class,
                'method' => 'display'
            ];
            $cachedRoutes[] = $deleteRoute;
            $router->addRoute($deleteRoute['path'], $deleteRoute['controller'], $deleteRoute['method']);
        }
        
        $this->saveCache($cachedRoutes);
    }
    
    private function saveCache(array $routes): void {
        $cacheDir = dirname($this->cacheFile);
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $routeCount = count($routes);
        
        $content = <<<PHP
<?php
/**
 * Auto-generated Route Cache for Admin Module
 * Generated: {$timestamp}
 * Total Routes: {$routeCount}
 */

return 
PHP;
        
        $content .= var_export($routes, true) . ";\n";
        file_put_contents($this->cacheFile, $content);
    }
    
    // Clear cache after creating/deleting users
    public static function clearCache(): void {
        $cacheFile = __DIR__ . '/../../../etc/storage/cache/admin_routes.php';
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    }
}
```

**Cache File Example:** `/etc/storage/cache/admin_routes.php`

```php
<?php
/**
 * Auto-generated Route Cache for Admin Module
 * Generated: 2025-10-24 14:30:00
 * Total Routes: 200
 */

return array (
  0 => array (
    'path' => '/admin/users/edit/1',
    'controller' => 'Admin\\Controller',
    'method' => 'display',
  ),
  1 => array (
    'path' => '/admin/users/edit/2',
    'controller' => 'Admin\\Controller',
    'method' => 'display',
  ),
  // ... 198 more routes
);
```

**Performance:**
```
First request (cache miss):  ~100ms (DB query + file write)
Cached requests:             ~2ms (file include)
Speed improvement:           50x faster!
```

**Clear cache after user changes:**

```php
// In Admin Controller after creating/deleting user
\Admin\Routes\Routes::clearCache();
```

**URL Flow:**
```
User visits:      /admin/users/edit/42
.htaccess:        /admin/users/edit/42 (no rewrite, passes through)
Router matches:   /admin/users/edit/42 (from cache)
Controller:       Displays edit form for user 42
```

---

## 🔀 Complete Request Flow

### Example: Visiting `/test-shoes`

**Step 1: Browser Request**
```
GET /test-shoes HTTP/1.1
Host: localhost
```

**Step 2: Apache .htaccess**
```apache
RewriteRule ^test-([\w\d~%.:_\-]+)$ test?param=$1 [NC]
```
Transforms to: `/test?param=shoes`

**Step 3: index.php Entry Point**
```php
require_once 'vendor/autoload.php';
$fireUpMVC = new Start();
$fireUpMVC->upMVC();
```

**Step 4: Start.php Initialization**
- Loads `.env` configuration
- Initializes error handling
- Parses REQUEST_URI: `/test`
- Captures GET parameters: `param=shoes`

**Step 5: Router Matching**
```php
// Router finds matching route
$routes['/test'] = [
    'className' => 'Test\Controller',
    'methodName' => 'display'
];
```

**Step 6: Controller Execution**
```php
namespace Test;

class Controller extends BaseController {
    public function display() {
        $param = $_GET['param']; // 'shoes'
        
        // Display products filtered by $param
        $this->view->render('products', [
            'category' => $param,
            'products' => $this->model->getProductsByCategory($param)
        ]);
    }
}
```

**Step 7: Response**
```html
HTTP/1.1 200 OK
Content-Type: text/html

<html>
    <h1>Shoes Category</h1>
    <!-- Product list -->
</html>
```

---

## 🎨 .htaccess Patterns & Best Practices

### Pattern: Single Parameter

**Use Case:** Category, product ID, page name

```apache
RewriteRule ^module-([\w\d~%.:_\-]+)$ module?param=$1 [NC]
```

**Examples:**
```
/test-electronics     → /test?param=electronics
/moda-page-about      → /moda-page?param=about
/blog-post-123        → /blog?param=post-123
```

### Pattern: Multiple Parameters

**Use Case:** Category + subcategory, filter combinations

```apache
RewriteRule ^module-([\w\d~%.:_\-]+)/([\w\d~%.:_\-]+)$ module?param=$1&another=$2 [NC]
```

**Examples:**
```
/test-shoes/nike      → /test?param=shoes&another=nike
/blog-tech/php        → /blog?param=tech&another=php
```

### Pattern: RESTful CRUD

**Use Case:** Edit/delete/update resources

```apache
RewriteRule ^module/action/([\w\d~%.:_\-]+)$ module/action?param=$1 [NC]
```

**Examples:**
```
/users/edit/42        → /users/edit?param=42
/posts/delete/100     → /posts/delete?param=100
```

### Pattern: Preserve Clean URLs

**Use Case:** Admin panels, nested structures

```apache
RewriteRule ^admin/users/edit/([\w\d~%.:_\-]+)$ admin/users/edit/$1 [NC]
```

**No transformation** - Router receives clean URL as-is

---

## ⚡ Performance Optimization

### 1. Route Caching (Admin Module Pattern)

**When to use:**
- 1000+ dynamic routes
- Database-driven routes
- Routes rarely change

**Performance gain:** 50x faster (100ms → 2ms)

**Implementation:**
```php
// Cache routes to file
// Rebuild on:
// - Cache miss
// - Cache expiry (1 hour)
// - Manual clear after data changes
```

### 2. Early .htaccess Matching

**Strategy:** Most common routes first

```apache
# Popular routes first (matched faster)
RewriteRule ^products/(.+)$ products.php?id=$1 [NC,L]
RewriteRule ^about$ about.php [NC,L]

# Less common routes last
RewriteRule ^archive/(.+)$ archive.php?date=$1 [NC,L]
```

### 3. Minimal Database Queries in Routes

**❌ Bad - Query on every request:**
```php
public function routes($router) {
    $products = $model->getAllProducts(); // 10,000 products!
    foreach ($products as $product) {
        $router->addRoute('/product-' . $product['id'], ...);
    }
}
```

**✅ Good - Cache or use generic route:**
```php
public function routes($router) {
    // Generic route handles all products
    $router->addRoute('/product', Controller::class, 'display');
}

// In .htaccess
RewriteRule ^product-([\w\d]+)$ product?id=$1 [NC]

// Controller fetches specific product
public function display() {
    $id = $_GET['id'];
    $product = $this->model->getProduct($id);
}
```

---

## 🔍 Debugging Routes

### Check .htaccess Rewrites

Add to `.htaccess` temporarily:

```apache
RewriteEngine On
RewriteLog "/tmp/rewrite.log"
RewriteLogLevel 3
```

### Check Router Matches

Add to controller:

```php
public function display() {
    // See what router received
    echo "Route: " . $_SERVER['REQUEST_URI'] . "<br>";
    echo "GET params: ";
    print_r($_GET);
    echo "POST params: ";
    print_r($_POST);
}
```

### Test Specific Route

```php
// In Start.php or index.php (temporary)
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "QUERY_STRING: " . $_SERVER['QUERY_STRING'] . "\n";
die();
```

---

## 📝 Quick Reference

### .htaccess Location
```
/
└── .htaccess (root level)
```

### Module Routes Location
```
/modules/{module}/
└── routes/
    └── Routes.php
```

### Adding New Module Routes

**1. Create Routes file:**
```php
namespace YourModule\Routes;
use YourModule\Controller;

class Routes {
    public function routes($router) {
        $router->addRoute('/yourmodule', Controller::class, 'display');
    }
}
```

**2. Add .htaccess rule (optional):**
```apache
# Clean URLs for your module
RewriteRule ^yourmodule-([\w\d~%.:_\-]+)$ yourmodule?param=$1 [NC]
```

**3. Test:**
```
Visit: http://localhost/yourmodule
Visit: http://localhost/yourmodule-test
```

---

## 🎯 Use Case Summary

| Module | .htaccess | Router | Use Case |
|--------|-----------|--------|----------|
| **Test** | Converts `/test-X` to `/test?param=X` | Static + dynamic routes | E-commerce with product categories |
| **Moda** | Converts `/moda-page-X` to `/moda-page?param=X` | Static routes | Multi-page testing |
| **UserORM** | Converts `/usersorm/edit/X` to `/usersorm/edit?param=X` | Database-driven routes | User CRUD without caching |
| **Admin** | Preserves `/admin/users/edit/X` | Cached database routes | High-performance admin panel |

---

## 🚀 Best Practices

### DO:
✅ Use .htaccess for SEO-friendly URLs  
✅ Cache routes for large datasets  
✅ Use generic routes when possible  
✅ Keep .htaccess rules simple  
✅ Document custom rewrite rules  
✅ Clear route cache after data changes

### DON'T:
❌ Query database on every request for routes  
❌ Create 10,000+ individual routes without caching  
❌ Use complex regex in .htaccess  
❌ Forget to test .htaccess changes  
❌ Hard-code routes for dynamic data

---

**Last Updated:** October 2025  
**upMVC Version:** 1.4.x+
