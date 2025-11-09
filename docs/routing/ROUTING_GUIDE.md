# Complete Routing Guide - upMVC

**Version:** 1.4.7  
**Last Updated:** November 9, 2025

## 📖 Table of Contents

1. [Overview](#overview)
2. [Routing Types](#routing-types)
3. [When to Use Each Type](#when-to-use-each-type)
4. [Quick Decision Tree](#quick-decision-tree)
5. [Simple Static Routes](#simple-static-routes)
6. [Parameterized Routing (Basic)](#parameterized-routing-basic)
7. [Router V2 Enhanced Features](#router-v2-enhanced-features)
8. [Database-Driven Routes](#database-driven-routes)
9. [Cached Database Routes](#cached-database-routes)
10. [Performance Comparison](#performance-comparison)
11. [Migration Guide](#migration-guide)
12. [Best Practices](#best-practices)

---

## Overview

upMVC provides **5 routing strategies** to handle different scenarios, from simple static pages to large-scale dynamic applications:

1. **Simple Static Routes** - Fixed URLs (e.g., `/about`, `/contact`)
2. **Parameterized Routes (Basic)** - URL placeholders (e.g., `/users/{id}`)
3. **Router V2 Enhanced** - Type casting + validation + named routes
4. **Database-Driven Routes** - Dynamic routes from DB (no cache)
5. **Cached Database Routes** - DB routes with file caching

**Philosophy:** Choose the simplest approach that meets your needs. Don't over-engineer.

---

## Routing Types

### 1️⃣ Simple Static Routes
**Best for:** Fixed pages, dashboards, forms  
**Example:** `/about`, `/contact`, `/dashboard`

```php
$router->addRoute('/about', About\Controller::class, 'display');
$router->addRoute('/contact', Contact\Controller::class, 'display');
$router->addRoute('/dashboard', Dashboard\Controller::class, 'index');
```

**Pros:**
- ✅ Fastest (direct hash lookup)
- ✅ Zero learning curve
- ✅ No validation needed

**Cons:**
- ❌ Cannot handle dynamic URLs

---

### 2️⃣ Parameterized Routing (Basic)
**Best for:** RESTful APIs, blogs, e-commerce with moderate data  
**Example:** `/users/{id}`, `/products/{slug}`, `/blog/{category}/{post}`

```php
$router->addParamRoute('/users/{id}', User\Controller::class, 'show');
$router->addParamRoute('/products/{slug}', Product\Controller::class, 'show');
$router->addParamRoute('/blog/{category}/{post}', Blog\Controller::class, 'show');
```

**Controller:**
```php
public function show()
{
    $id = $_GET['id'] ?? null;
    
    // Validate in controller
    if (!$id || !ctype_digit($id)) {
        http_response_code(400);
        return;
    }
    
    $user = $this->model->getUserById((int)$id);
    if (!$user) {
        http_response_code(404);
        return;
    }
    
    // Display user...
}
```

**Pros:**
- ✅ Scalable (handles millions of records)
- ✅ Clean URLs
- ✅ No cache management
- ✅ Always shows current data

**Cons:**
- ❌ Manual validation required
- ❌ Manual type casting needed
- ❌ Validation logic in controller

---

### 3️⃣ Router V2 Enhanced Features
**Best for:** Type-safe APIs, complex validation, named routes  
**Example:** `/users/{id:int}`, `/products/{price:float}`, named route generation

**NEW in v1.4.7:** Complete Router V2 implementation with:
- **Type Casting:** `{id:int}`, `{price:float}`, `{active:bool}`
- **Validation:** Regex constraints at router level
- **Named Routes:** Generate URLs from route names
- **Route Grouping:** Auto-prefix optimization

#### Type Casting

```php
$router->addParamRoute('/users/{id:int}', User\Controller::class, 'show');
$router->addParamRoute('/products/{price:float}', Product\Controller::class, 'filter');
$router->addParamRoute('/settings/{active:bool}', Settings\Controller::class, 'toggle');
```

**Controller (simplified - no casting needed):**
```php
public function show()
{
    $id = $_GET['id']; // Already an integer!
    
    $user = $this->model->getUserById($id);
    if (!$user) {
        http_response_code(404);
        return;
    }
    
    // Display user...
}
```

#### Validation Constraints

```php
// Only numeric IDs
$router->addParamRoute('/users/{id}', User\Controller::class, 'show', [], [
    'id' => '\d+'
]);

// UUID format
$router->addParamRoute('/orders/{uuid}', Order\Controller::class, 'show', [], [
    'uuid' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}'
]);

// Date validation
$router->addParamRoute('/archive/{year}/{month}', Archive\Controller::class, 'show', [], [
    'year' => '\d{4}',
    'month' => '(0[1-9]|1[0-2])'
]);
```

#### Named Routes

```php
// Define named routes
$router->addParamRoute('/users/{id:int}', User\Controller::class, 'show')
        ->name('user.show');

$router->addParamRoute('/users/{id:int}/edit', User\Controller::class, 'edit')
        ->name('user.edit');

// Generate URLs
use upMVC\Helpers\HelperFacade;

$url = HelperFacade::route('user.show', ['id' => 123]);
// Result: /users/123

$editUrl = HelperFacade::route('user.edit', ['id' => 123]);
// Result: /users/123/edit
```

#### Route Grouping

```php
// Group related routes with shared prefix
$router->group('/admin', function($router) {
    $router->addParamRoute('/users/{id:int}', Admin\User\Controller::class, 'show')
            ->name('admin.user.show');
    
    $router->addParamRoute('/users/{id:int}/edit', Admin\User\Controller::class, 'edit')
            ->name('admin.user.edit');
    
    $router->addParamRoute('/settings', Admin\Settings\Controller::class, 'index')
            ->name('admin.settings');
});

// Results in:
// /admin/users/{id:int} → admin.user.show
// /admin/users/{id:int}/edit → admin.user.edit
// /admin/settings → admin.settings
```

**Pros:**
- ✅ Type-safe (automatic casting)
- ✅ Router-level validation
- ✅ Clean controller code
- ✅ Named route generation
- ✅ No manual type casting
- ✅ Better security (validated at router)

**Cons:**
- ❌ Slightly more setup
- ❌ Need to understand type hints

---

### 4️⃣ Database-Driven Routes (No Cache)
**Best for:** Development, small datasets (< 100 records)  
**Example:** Load user routes directly from database on each request

```php
public function routes($router)
{
    $users = $this->model->getAllUsers();
    
    foreach ($users as $user) {
        $router->addRoute('/users/edit/' . $user['id'], Controller::class, 'edit');
        $router->addRoute('/users/delete/' . $user['id'], Controller::class, 'delete');
    }
}
```

**Pros:**
- ✅ Always current data
- ✅ Simple to understand
- ✅ No cache management

**Cons:**
- ❌ Database query on every request
- ❌ Slow with large datasets
- ❌ Not production-ready

---

### 5️⃣ Cached Database Routes
**Best for:** Production, medium datasets (100-10,000 records)  
**Example:** Admin user management, product catalogs

```php
public function routes($router)
{
    $cacheFile = __DIR__ . '/../../etc/storage/cache/admin_routes.php';
    $cacheTTL = 3600; // 1 hour
    
    // Check cache validity
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTTL) {
        $routes = include $cacheFile;
    } else {
        // Build routes from database
        $users = $this->model->getAllUsers();
        $routes = [];
        
        foreach ($users as $user) {
            $routes[] = [
                'url' => '/admin/users/edit/' . $user['id'],
                'class' => Controller::class,
                'method' => 'edit'
            ];
        }
        
        // Save to cache
        file_put_contents($cacheFile, '<?php return ' . var_export($routes, true) . ';');
    }
    
    // Register cached routes
    foreach ($routes as $route) {
        $router->addRoute($route['url'], $route['class'], $route['method']);
    }
}
```

**Cache Invalidation:**
```php
public function createUser()
{
    $this->model->createUser($data);
    
    // Clear cache to rebuild routes
    $cacheFile = __DIR__ . '/routes/../../etc/storage/cache/admin_routes.php';
    if (file_exists($cacheFile)) {
        unlink($cacheFile);
    }
}
```

**Pros:**
- ✅ Fast (file cache lookup)
- ✅ Handles 100-10,000 records
- ✅ Pre-validates IDs at router level
- ✅ Security-first (only valid IDs routable)

**Cons:**
- ❌ Cache invalidation needed
- ❌ More complex than basic param routes
- ❌ Memory usage grows with dataset

---

## When to Use Each Type

### Simple Static Routes
**Use when:**
- ✅ Fixed pages (About, Contact, Terms)
- ✅ Dashboard pages
- ✅ Forms with static URLs

**Don't use when:**
- ❌ Need dynamic parameters
- ❌ Routes depend on database

---

### Parameterized Routing (Basic)
**Use when:**
- ✅ RESTful APIs
- ✅ Blog/CMS (10,000+ posts)
- ✅ E-commerce (1,000+ products)
- ✅ Any dynamic content > 1,000 records
- ✅ Frequently changing data

**Don't use when:**
- ❌ Dataset < 100 records (cached is simpler)
- ❌ Need pre-validation at router level
- ❌ Want type safety without manual casting

---

### Router V2 Enhanced
**Use when:**
- ✅ Need type safety (no manual casting)
- ✅ Complex validation rules
- ✅ Want named route generation
- ✅ Building APIs with strict types
- ✅ Want cleaner controller code
- ✅ Large-scale applications

**Don't use when:**
- ❌ Simple static routes are enough
- ❌ Team unfamiliar with type hints

---

### Database-Driven Routes (No Cache)
**Use when:**
- ✅ Development/testing
- ✅ Very small datasets (< 50 records)
- ✅ Prototyping

**Don't use when:**
- ❌ Production environment
- ❌ Dataset > 100 records
- ❌ Performance matters

---

### Cached Database Routes
**Use when:**
- ✅ Production admin panels
- ✅ 100-10,000 records
- ✅ Relatively stable data
- ✅ Need pre-validation at router
- ✅ Security-first applications

**Don't use when:**
- ❌ Dataset > 10,000 records (use param routes)
- ❌ Frequently changing data (cache invalidation overhead)
- ❌ Distributed systems (cache sync issues)

---

## Quick Decision Tree

```
How many records?
│
├─ 0 (Static pages)
│  └─ Use: Simple Static Routes
│
├─ < 100 records
│  ├─ Development? → Database-Driven (no cache)
│  └─ Production? → Cached Database Routes
│
├─ 100-1,000 records
│  ├─ Need type safety? → Router V2 Enhanced
│  ├─ Security-first? → Cached Database Routes
│  └─ Default → Parameterized Routing (Basic)
│
└─ > 1,000 records
   ├─ Need type safety? → Router V2 Enhanced
   └─ Default → Parameterized Routing (Basic)
```

**Need type safety or validation at any scale?**  
→ Use **Router V2 Enhanced**

**Need named routes?**  
→ Use **Router V2 Enhanced**

---

## Performance Comparison

| Routing Type | Request Time | Memory | Best For |
|--------------|--------------|---------|----------|
| Simple Static | 0.1ms | 10KB | Fixed pages |
| Parameterized (Basic) | 0.5ms | 20KB | 1,000+ records |
| Router V2 Enhanced | 0.6ms | 25KB | Type-safe apps |
| DB-Driven (no cache) | 100ms | 50KB | Development |
| Cached DB | 2ms | 500KB-2MB | 100-10,000 records |

**Tested with:**
- 10,000 user records
- PHP 8.1, OpCache enabled
- Apache 2.4

---

## Migration Guide

### From Static to Parameterized

**Before:**
```php
foreach ($users as $user) {
    $router->addRoute('/users/' . $user['id'], Controller::class, 'show');
}
```

**After:**
```php
$router->addParamRoute('/users/{id}', Controller::class, 'show');
```

**Controller changes:**
```php
public function show()
{
    // Add validation
    $id = $_GET['id'] ?? null;
    if (!$id || !ctype_digit($id)) {
        http_response_code(400);
        return;
    }
    
    // Existing logic...
}
```

---

### From Basic Parameterized to Router V2

**Before:**
```php
$router->addParamRoute('/users/{id}', Controller::class, 'show');

// Controller
public function show()
{
    $id = $_GET['id'] ?? null;
    if (!$id || !ctype_digit($id)) {
        http_response_code(400);
        return;
    }
    $id = (int)$id; // Manual casting
    
    $user = $this->model->getUserById($id);
    // ...
}
```

**After:**
```php
$router->addParamRoute('/users/{id:int}', Controller::class, 'show', [], [
    'id' => '\d+'
])->name('user.show');

// Controller (simplified!)
public function show()
{
    $id = $_GET['id']; // Already int, already validated!
    
    $user = $this->model->getUserById($id);
    if (!$user) {
        http_response_code(404);
        return;
    }
    // ...
}
```

---

### From Cached DB to Parameterized

**Before (Routes.php):**
```php
public function routes($router)
{
    $cacheFile = __DIR__ . '/cache/routes.php';
    
    if (file_exists($cacheFile)) {
        $routes = include $cacheFile;
    } else {
        $users = $this->model->getAllUsers();
        $routes = [];
        foreach ($users as $user) {
            $routes[] = ['url' => '/users/' . $user['id'], ...];
        }
        file_put_contents($cacheFile, '<?php return ' . var_export($routes, true) . ';');
    }
    
    foreach ($routes as $route) {
        $router->addRoute($route['url'], $route['class'], $route['method']);
    }
}
```

**After:**
```php
public function routes($router)
{
    $router->addParamRoute('/users/{id:int}', Controller::class, 'edit', [], [
        'id' => '\d+'
    ])->name('user.edit');
}
```

**Controller changes:**
```php
// Remove cache clearing code
public function createUser()
{
    $this->model->createUser($data);
    // No more cache clearing!
}

// Add ID validation
public function edit()
{
    $id = $_GET['id']; // Already int from Router V2
    
    $user = $this->model->getUserById($id);
    if (!$user) {
        http_response_code(404);
        return;
    }
    // ...
}
```

---

## Best Practices

### 1. Start Simple
Begin with the simplest approach that works:
- Static pages → Simple Static Routes
- Small dynamic → Cached DB Routes
- Large dynamic → Parameterized + Router V2

### 2. Validate Everything
```php
// Basic validation
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    http_response_code(400);
    return;
}

// Or use Router V2 for automatic validation
$router->addParamRoute('/users/{id:int}', Controller::class, 'show', [], [
    'id' => '\d+'
]);
```

### 3. Use Named Routes (Router V2)
```php
// Define
$router->addParamRoute('/users/{id:int}/edit', Controller::class, 'edit')
        ->name('user.edit');

// Use in views
<a href="<?= HelperFacade::route('user.edit', ['id' => $user['id']]) ?>">
    Edit User
</a>
```

### 4. Group Related Routes
```php
$router->group('/api', function($router) {
    $router->addParamRoute('/users/{id:int}', API\User\Controller::class, 'show')
            ->name('api.user.show');
    
    $router->addParamRoute('/products/{id:int}', API\Product\Controller::class, 'show')
            ->name('api.product.show');
});
```

### 5. Document Your Choice
Add a comment explaining why you chose a specific approach:

```php
// Using Router V2 for type safety and validation
// Dataset: 50,000+ users, frequently updated
$router->addParamRoute('/users/{id:int}', Controller::class, 'show', [], [
    'id' => '\d+'
])->name('user.show');
```

---

## Example: Admin Module (All Strategies)

The admin module demonstrates **3 strategies** for educational purposes:

### Current (Routes.php) - Router V2 Enhanced
```php
$router->addParamRoute('/admin/users/edit/{id:int}', Controller::class, 'edit', [], [
    'id' => '\d+'
])->name('admin.user.edit');
```

### Backup (Routesd.php) - Basic Parameterized
```php
$router->addParamRoute('/admin/users/edit/{id}', Controller::class, 'edit');
```

### Backup (Routesc.php) - Cached Database
```php
if (file_exists($cacheFile)) {
    $routes = include $cacheFile;
} else {
    $users = $this->model->getAllUsers();
    // Build and cache routes...
}
```

**See:** `modules/admin/README.md` for complete comparison

---

## Additional Resources

### Documentation
- **[ROUTER_V2_EXAMPLES.md](ROUTER_V2_EXAMPLES.md)** - Complete Router V2 usage examples
- **[ROUTER_V2_CHANGELOG.md](ROUTER_V2_CHANGELOG.md)** - What's new in v2.0
- **[PARAMETERIZED_ROUTING.md](PARAMETERIZED_ROUTING.md)** - Deep dive into parameterized routing
- **[ROUTING_STRATEGIES.md](ROUTING_STRATEGIES.md)** - Detailed strategy comparison
- **[HELPER_FUNCTIONS_GUIDE.md](HELPER_FUNCTIONS_GUIDE.md)** - Helper functions for routing

### Examples
- **[modules/admin/](../../modules/admin/)** - All 3 strategies demonstrated
- **[docs/routing/examples/](examples/)** - Working code examples

### Helpers
- **[HelperFacade](../../etc/Helpers/HelperFacade.php)** - PSR-4 helper facade
- **[RouteHelper](../../etc/Helpers/RouteHelper.php)** - Named route generation

---

## Support

**Questions?** Check the [FAQ](../FAQ.md) or open an issue.

**Need help choosing?** Use the [Decision Tree](#quick-decision-tree) above.

---

**Version:** 1.4.7  
**Last Updated:** November 9, 2025  
**License:** MIT
