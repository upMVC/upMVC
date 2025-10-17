# upMVC Routing Strategies - Complete Guide

This document explains the different routing approaches available in upMVC, their trade-offs, and when to use each one.

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [The Three Approaches](#the-three-approaches)
3. [Detailed Comparison](#detailed-comparison)
4. [Performance Analysis](#performance-analysis)
5. [Implementation Examples](#implementation-examples)
6. [Pattern Matching Deep Dive](#pattern-matching-deep-dive)
7. [Cache Strategy Deep Dive](#cache-strategy-deep-dive)
8. [Choosing the Right Approach](#choosing-the-right-approach)
9. [Migration Guides](#migration-guides)

---

## Overview

upMVC follows a **NoFramework** philosophy with explicit route registration. However, as applications scale, different routing strategies become necessary. This guide explores three approaches:

1. **Dynamic Database Routes** - Query database on every request
2. **Cached Database Routes** - Query once, cache results
3. **Pattern Matching Routes** - No database queries, regex-based

---

## The Three Approaches

### 🔍 Approach 1: Dynamic Database Generation

**Philosophy:** Generate routes from database on every request

```php
// modules/yourmodule/routes/Routes.php

public function routes($router)
{
    // Query database for all users
    $model = new Model();
    $users = $model->getAllUsers();  // ❌ DB query on EVERY request
    
    // Create explicit route for each user
    foreach ($users as $user) {
        $router->addRoute('/admin/users/edit/' . $user['id'], Controller::class, 'display');
        $router->addRoute('/admin/users/delete/' . $user['id'], Controller::class, 'display');
    }
}
```

**How It Works:**
```
Request → Query DB → Generate routes for every record → Register routes → Execute
```

**Example:**
```
Database has users: [1, 5, 99]
Routes created:
  ✅ /admin/users/edit/1
  ✅ /admin/users/edit/5
  ✅ /admin/users/edit/99
  
Request: /admin/users/edit/777
Result: ❌ 404 (no route exists)
```

**Pros:**
- ✅ Only valid IDs have routes (secure)
- ✅ Simple to understand
- ✅ Follows upMVC explicit routing philosophy

**Cons:**
- ❌ Database query on every request
- ❌ Slow with many records (10,000+ users)
- ❌ Doesn't scale well

**Use When:**
- Development/testing
- Very small datasets (< 100 records)
- Database changes are very frequent

---

### 🔍 Approach 2: Cached Database Routes

**Philosophy:** Query database once, cache results to file

```php
// modules/yourmodule/routes/Routes.php

public function routes($router)
{
    // Check if cache is valid
    if ($this->isCacheValid()) {
        // Load from cache (FAST!)
        $cachedRoutes = include $this->cacheFile;
        foreach ($cachedRoutes as $route) {
            $router->addRoute($route['path'], $route['controller'], $route['method']);
        }
    } else {
        // Cache is stale - rebuild it
        $model = new Model();
        $users = $model->getAllUsers();  // ✅ DB query only when cache expires
        
        $cachedRoutes = [];
        foreach ($users as $user) {
            $route = [
                'path' => '/admin/users/edit/' . $user['id'],
                'controller' => Controller::class,
                'method' => 'display'
            ];
            $cachedRoutes[] = $route;
            $router->addRoute($route['path'], $route['controller'], $route['method']);
        }
        
        // Save to cache file
        $this->saveCache($cachedRoutes);
    }
}
```

**How It Works:**
```
First Request:
  → Query DB → Generate routes → Save to cache.php → Register routes

Next Requests (within cache lifetime):
  → Read cache.php → Register routes (NO DB QUERY!)

After CRUD Operation:
  → Clear cache → Next request rebuilds it
```

**Example:**
```
Cache file (modules/cache/admin_routes.php):
<?php
return [
    ['path' => '/admin/users/edit/1', 'controller' => Controller::class, 'method' => 'display'],
    ['path' => '/admin/users/edit/5', 'controller' => Controller::class, 'method' => 'display'],
    ['path' => '/admin/users/edit/99', 'controller' => Controller::class, 'method' => 'display'],
];

Every request: Read this file (2ms) instead of querying DB (100ms)
```

**Pros:**
- ✅ 50x faster than dynamic DB approach
- ✅ Only valid IDs have routes (secure)
- ✅ Follows upMVC explicit routing philosophy
- ✅ Scales well (< 100,000 records)
- ✅ Easy cache invalidation on CRUD operations

**Cons:**
- ⚠️ Requires cache management
- ⚠️ Cache can become stale if not invalidated properly
- ⚠️ First request after cache clear is slower

**Use When:**
- Production applications
- Moderate datasets (100 - 100,000 records)
- Routes need to be validated against database
- Security is important (only valid IDs accessible)
- **RECOMMENDED FOR MOST APPLICATIONS**

---

### 🔍 Approach 3: Pattern Matching Routes

**Philosophy:** Define route patterns, not specific routes

```php
// modules/yourmodule/routes/Routes.php

public function routes($router)
{
    // Define patterns with placeholders
    $router->addRoute('/admin/users/edit/{id}', Controller::class, 'display');
    $router->addRoute('/admin/users/delete/{id}', Controller::class, 'display');
    
    // NO database query needed!
}

// Requires modified Router with pattern matching
// Router converts patterns to regex: /admin/users/edit/{id} → /^\/admin\/users\/edit\/(?P<id>[^\/]+)$/
```

**How It Works:**
```
Define Pattern Once:
  → /admin/users/edit/{id}
  
Every Request:
  → Regex match → Extract ID from URL → Pass to controller
  → Controller validates ID against database
```

**Example:**
```
Pattern defined: /admin/users/edit/{id}

Request: /admin/users/edit/1
  ✅ Matches → Extract id=1 → Controller checks if user 1 exists

Request: /admin/users/edit/999999
  ✅ Matches → Extract id=999999 → Controller returns "User not found"

Request: /admin/users/edit/invalid
  ✅ Matches → Extract id=invalid → Controller returns "Invalid ID"
```

**Pros:**
- ✅ No database queries for routing
- ✅ 100x faster than dynamic DB
- ✅ Scales infinitely (same speed for 10 users or 10 million)
- ✅ Simple code, no cache management
- ✅ Supports complex patterns: `/books/{author}/{year}/{id}`

**Cons:**
- ❌ Invalid IDs reach controller (must validate there)
- ❌ Requires Router modification
- ❌ Not the traditional upMVC explicit routing style

**Use When:**
- Very large datasets (> 100,000 records)
- Maximum performance needed
- Route validation can happen in controller
- Flexibility more important than explicit control

---

## Detailed Comparison

| Feature | Dynamic DB | Cached DB | Pattern Matching |
|---------|-----------|-----------|------------------|
| **DB Query Per Request** | Yes (100ms) | No (cache read 2ms) | No (regex 0.5ms) |
| **First Request** | 100ms | 100ms + cache write | 0.5ms |
| **Subsequent Requests** | 100ms | 2ms | 0.5ms |
| **100 users** | 100ms | 2ms | 0.5ms |
| **10,000 users** | 500ms ❌ | 5ms | 0.5ms |
| **1 million users** | 30 seconds ❌ | 100ms | 0.5ms |
| **Invalid IDs** | 404 from Router | 404 from Router | Reach controller |
| **Security** | High | High | Medium (must validate) |
| **Code Complexity** | Low | Medium | Low |
| **Cache Management** | None | Required | None |
| **upMVC Philosophy** | ✅ Explicit | ✅ Explicit | ❌ Generic patterns |
| **Scalability** | Poor | Good | Excellent |
| **Best For** | Development | Production (most apps) | High-scale apps |

---

## Performance Analysis

### Real-World Scenario: Admin Dashboard with 1,000 Users

#### Approach 1: Dynamic DB
```
Request 1: Query DB (100ms) + Generate 2000 routes (20ms) = 120ms
Request 2: Query DB (100ms) + Generate 2000 routes (20ms) = 120ms
Request 3: Query DB (100ms) + Generate 2000 routes (20ms) = 120ms
...
Request 1000: Still 120ms

Total for 1000 requests: 120,000ms (2 minutes)
```

#### Approach 2: Cached DB
```
Request 1: Query DB (100ms) + Generate 2000 routes (20ms) + Save cache (5ms) = 125ms
Request 2: Read cache (2ms) + Register routes (1ms) = 3ms
Request 3: Read cache (2ms) + Register routes (1ms) = 3ms
...
Request 1000: Still 3ms

Total for 1000 requests: 125ms + (999 × 3ms) = 3,122ms (3 seconds)
Speedup: 38x faster!
```

#### Approach 3: Pattern Matching
```
Request 1: Regex match (0.5ms) = 0.5ms
Request 2: Regex match (0.5ms) = 0.5ms
Request 3: Regex match (0.5ms) = 0.5ms
...
Request 1000: Still 0.5ms

Total for 1000 requests: 500ms (0.5 seconds)
Speedup: 240x faster than dynamic, 6x faster than cache!
```

### Memory Usage

#### Dynamic DB: High
- 1,000 users = 2,000 routes in memory
- Each route: ~200 bytes
- Total: ~400 KB per request

#### Cached DB: High (first request), Medium (cached)
- Cache file: ~400 KB on disk
- Routes loaded into memory: ~400 KB
- Total: ~400 KB per request

#### Pattern Matching: Low
- 2 pattern routes in memory
- Each pattern: ~150 bytes
- Total: ~300 bytes per request
- **1,333x less memory than other approaches!**

---

## Implementation Examples

### Example 1: Admin Module with Cached Routes

**File Structure:**
```
modules/
  admin/
    Controller.php
    Model.php
    View.php
    routes/
      Routes.php         ← Uses cache
  cache/
    admin_routes.php     ← Generated cache file
```

**Routes.php:**
```php
<?php
namespace Admin\Routes;

use Admin\Controller;
use Admin\Model;

class Routes
{
    private string $cacheFile;
    private int $cacheLifetime = 3600; // 1 hour

    public function __construct()
    {
        $this->cacheFile = __DIR__ . '/../../cache/admin_routes.php';
    }

    public function routes($router)
    {
        // Static routes
        $router->addRoute('/admin', Controller::class, 'display');
        $router->addRoute('/admin/users', Controller::class, 'display');
        
        // Dynamic routes (cached)
        if ($this->isCacheValid()) {
            $this->loadCachedRoutes($router);
        } else {
            $this->rebuildCache($router);
        }
    }

    private function isCacheValid(): bool
    {
        if (!file_exists($this->cacheFile)) {
            return false;
        }
        return (time() - filemtime($this->cacheFile)) < $this->cacheLifetime;
    }

    private function loadCachedRoutes($router): void
    {
        $cachedRoutes = include $this->cacheFile;
        foreach ($cachedRoutes as $route) {
            $router->addRoute($route['path'], $route['controller'], $route['method']);
        }
    }

    private function rebuildCache($router): void
    {
        $model = new Model();
        $users = $model->getAllUsers();
        
        $cachedRoutes = [];
        foreach ($users as $user) {
            $editRoute = [
                'path' => '/admin/users/edit/' . $user['id'],
                'controller' => Controller::class,
                'method' => 'display'
            ];
            $cachedRoutes[] = $editRoute;
            $router->addRoute($editRoute['path'], $editRoute['controller'], $editRoute['method']);
        }
        
        $this->saveCache($cachedRoutes);
    }

    private function saveCache(array $routes): void
    {
        $cacheDir = dirname($this->cacheFile);
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        $content = "<?php\nreturn " . var_export($routes, true) . ";\n";
        file_put_contents($this->cacheFile, $content);
    }

    public static function clearCache(): void
    {
        $cacheFile = __DIR__ . '/../../cache/admin_routes.php';
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    }
}
```

**Controller.php (with cache invalidation):**
```php
<?php
namespace Admin;

use Admin\Routes\Routes;

class Controller
{
    private function createUser()
    {
        $result = $this->model->createUser($userData);
        
        if ($result) {
            Routes::clearCache(); // ← Invalidate cache
            $_SESSION['success'] = 'User created';
        }
        
        header('Location: ' . BASE_URL . '/admin/users');
        exit;
    }

    private function deleteUser(int $userId)
    {
        $result = $this->model->deleteUser($userId);
        
        if ($result) {
            Routes::clearCache(); // ← Invalidate cache
            $_SESSION['success'] = 'User deleted';
        }
        
        header('Location: ' . BASE_URL . '/admin/users');
        exit;
    }
}
```

---

### Example 2: Blog Module with Pattern Matching

**Router.php (modified with pattern support):**
```php
<?php
namespace Etc;

class Router
{
    private $routes = [];

    public function addRoute($route, $controller, $method = 'display')
    {
        $this->routes[$route] = [
            'controller' => $controller,
            'method' => $method,
            'pattern' => $this->convertToRegex($route) // ← Convert to regex
        ];
    }

    public function dispatch($requestUri)
    {
        // Try exact match first
        if (isset($this->routes[$requestUri])) {
            return $this->executeRoute($this->routes[$requestUri]);
        }

        // Try pattern matching
        foreach ($this->routes as $route => $config) {
            if (preg_match($config['pattern'], $requestUri, $matches)) {
                // Extract parameters and add to $_GET
                foreach ($matches as $key => $value) {
                    if (!is_numeric($key)) {
                        $_GET[$key] = $value;
                    }
                }
                return $this->executeRoute($config);
            }
        }

        // No match
        http_response_code(404);
        include __DIR__ . '/../common/404.php';
        exit;
    }

    private function convertToRegex($route)
    {
        // Escape forward slashes
        $pattern = str_replace('/', '\/', $route);
        
        // Convert wildcards: /users/* → /users/([^\/]+)
        $pattern = str_replace('*', '([^\/]+)', $pattern);
        
        // Convert named parameters: /users/{id} → /users/(?P<id>[^\/]+)
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^\/]+)', $pattern);
        
        return '/^' . $pattern . '$/';
    }

    private function executeRoute($config)
    {
        $controller = new $config['controller']();
        $method = $config['method'];
        $controller->$method();
    }
}
```

**Blog Routes.php:**
```php
<?php
namespace Blog\Routes;

use Blog\Controller;

class Routes
{
    public function routes($router)
    {
        // List all posts
        $router->addRoute('/blog', Controller::class, 'display');
        
        // View single post - pattern matching (no DB query needed!)
        $router->addRoute('/blog/post/{id}', Controller::class, 'display');
        
        // View posts by category
        $router->addRoute('/blog/category/{slug}', Controller::class, 'display');
        
        // View posts by author and year
        $router->addRoute('/blog/{author}/{year}/{slug}', Controller::class, 'display');
    }
}
```

**Blog Controller.php:**
```php
<?php
namespace Blog;

class Controller
{
    public function display()
    {
        $route = $_SERVER['REQUEST_URI'];
        
        // View single post
        if (isset($_GET['id']) && strpos($route, '/blog/post/') === 0) {
            $postId = (int)$_GET['id'];
            
            // Validate ID exists in database
            $post = $this->model->getPostById($postId);
            if (!$post) {
                http_response_code(404);
                echo "Post not found";
                return;
            }
            
            $this->view->render(['type' => 'single_post', 'post' => $post]);
            return;
        }
        
        // View by category
        if (isset($_GET['slug']) && strpos($route, '/blog/category/') === 0) {
            $categorySlug = $_GET['slug'];
            
            // Validate category exists
            $category = $this->model->getCategoryBySlug($categorySlug);
            if (!$category) {
                http_response_code(404);
                echo "Category not found";
                return;
            }
            
            $posts = $this->model->getPostsByCategory($category['id']);
            $this->view->render(['type' => 'category', 'posts' => $posts]);
            return;
        }
        
        // Default: List all posts
        $posts = $this->model->getAllPosts();
        $this->view->render(['type' => 'list', 'posts' => $posts]);
    }
}
```

---

## Pattern Matching Deep Dive

### How Pattern Conversion Works

```php
function convertToRegex($route)
{
    // Step 1: Escape forward slashes
    // Input:  /blog/post/{id}
    // Output: \/blog\/post\/{id}
    $pattern = str_replace('/', '\/', $route);
    
    // Step 2: Convert wildcards
    // Input:  \/blog\/post\/*
    // Output: \/blog\/post\/([^\/]+)
    $pattern = str_replace('*', '([^\/]+)', $pattern);
    
    // Step 3: Convert named parameters
    // Input:  \/blog\/post\/{id}
    // Output: \/blog\/post\/(?P<id>[^\/]+)
    $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^\/]+)', $pattern);
    
    // Step 4: Add anchors
    // Input:  \/blog\/post\/(?P<id>[^\/]+)
    // Output: /^\/blog\/post\/(?P<id>[^\/]+)$/
    return '/^' . $pattern . '$/';
}
```

### Pattern Examples

| Pattern | Regex | Matches | Doesn't Match |
|---------|-------|---------|---------------|
| `/blog/{id}` | `/^\/blog\/(?P<id>[^\/]+)$/` | `/blog/123`<br>`/blog/hello` | `/blog/123/edit`<br>`/blog/` |
| `/blog/*` | `/^\/blog\/([^\/]+)$/` | `/blog/123`<br>`/blog/anything` | `/blog/123/edit` |
| `/blog/{author}/{year}` | `/^\/blog\/(?P<author>[^\/]+)\/(?P<year>[^\/]+)$/` | `/blog/john/2025` | `/blog/john` |
| `/api/*/data` | `/^\/api\/([^\/]+)\/data$/` | `/api/users/data`<br>`/api/posts/data` | `/api/data` |

### Complex Pattern Example

```php
// Pattern
$router->addRoute('/books/{category}/{author}/{year}/{isbn}', Controller::class, 'display');

// Converts to regex:
// /^\/books\/(?P<category>[^\/]+)\/(?P<author>[^\/]+)\/(?P<year>[^\/]+)\/(?P<isbn>[^\/]+)$/

// Matches:
// /books/fiction/tolkien/1954/978-0-261-10320-7
//   $_GET['category'] = 'fiction'
//   $_GET['author'] = 'tolkien'
//   $_GET['year'] = '1954'
//   $_GET['isbn'] = '978-0-261-10320-7'

// /books/scifi/asimov/1951/978-0-553-29337-0
//   $_GET['category'] = 'scifi'
//   $_GET['author'] = 'asimov'
//   $_GET['year'] = '1951'
//   $_GET['isbn'] = '978-0-553-29337-0'
```

### Pattern Testing

```php
// Test pattern conversion
function testPattern($pattern, $testRoutes)
{
    $regex = convertToRegex($pattern);
    echo "Pattern: {$pattern}\n";
    echo "Regex: {$regex}\n\n";
    
    foreach ($testRoutes as $route) {
        if (preg_match($regex, $route, $matches)) {
            echo "✅ MATCH: {$route}\n";
            foreach ($matches as $key => $value) {
                if (!is_numeric($key)) {
                    echo "   {$key} = {$value}\n";
                }
            }
        } else {
            echo "❌ NO MATCH: {$route}\n";
        }
    }
}

// Test
testPattern('/blog/post/{id}', [
    '/blog/post/123',           // ✅ Matches, id=123
    '/blog/post/hello',         // ✅ Matches, id=hello
    '/blog/post/123/edit',      // ❌ No match
    '/blog/post/',              // ❌ No match
]);
```

---

## Cache Strategy Deep Dive

### Cache Lifetime Strategies

#### Time-Based Expiration
```php
private int $cacheLifetime = 3600; // 1 hour

private function isCacheValid(): bool
{
    if (!file_exists($this->cacheFile)) {
        return false;
    }
    return (time() - filemtime($this->cacheFile)) < $this->cacheLifetime;
}
```

**Pros:** Automatic cache refresh
**Cons:** Cache can be stale within lifetime

#### Manual Invalidation
```php
// Clear cache on every CRUD operation
Routes::clearCache();
```

**Pros:** Always fresh data
**Cons:** First request after CRUD is slow

#### Hybrid Approach
```php
private int $cacheLifetime = 86400; // 24 hours

public function routes($router)
{
    // Manual clear on CRUD (instant updates)
    // Time-based clear as backup (in case manual fails)
    if ($this->isCacheValid()) {
        $this->loadCachedRoutes($router);
    } else {
        $this->rebuildCache($router);
    }
}
```

**Pros:** Best of both worlds
**Cons:** Slight complexity

### Cache Storage Options

#### File-Based (Recommended)
```php
private string $cacheFile = __DIR__ . '/../../cache/admin_routes.php';

private function saveCache(array $routes): void
{
    $content = "<?php\nreturn " . var_export($routes, true) . ";\n";
    file_put_contents($this->cacheFile, $content);
}

private function loadCachedRoutes($router): void
{
    $cachedRoutes = include $this->cacheFile;
    // ...
}
```

**Pros:** Fast, persistent across requests
**Cons:** Requires write permissions

#### Session-Based
```php
private function saveCache(array $routes): void
{
    $_SESSION['admin_routes_cache'] = $routes;
}

private function loadCachedRoutes($router): void
{
    $cachedRoutes = $_SESSION['admin_routes_cache'];
    // ...
}
```

**Pros:** No file permissions needed
**Cons:** Per-user cache (duplicated), lost on logout

#### APCu/Memcached
```php
private function saveCache(array $routes): void
{
    apcu_store('admin_routes_cache', $routes, 3600);
}

private function loadCachedRoutes($router): void
{
    $cachedRoutes = apcu_fetch('admin_routes_cache');
    // ...
}
```

**Pros:** Fastest, shared across requests
**Cons:** Requires APCu extension

---

## Choosing the Right Approach

### Decision Tree

```
Start
  │
  ├─ Do you have < 100 records?
  │  └─ YES → Use Dynamic DB (simple, overhead doesn't matter)
  │  └─ NO → Continue
  │
  ├─ Do you have > 100,000 records?
  │  └─ YES → Use Pattern Matching (only approach that scales)
  │  └─ NO → Continue
  │
  ├─ Is security critical (only valid IDs should be accessible)?
  │  └─ YES → Use Cached DB (validates against database)
  │  └─ NO → Use Pattern Matching (simpler code)
  │
  └─ Default → Use Cached DB (best balance for most apps)
```

### By Application Type

**Admin Dashboard (100-10,000 users)**
→ **Cached DB Routes**
- Security is important
- Moderate dataset size
- CRUD operations are infrequent
- Cache invalidation is straightforward

**E-commerce (1,000-100,000 products)**
→ **Cached DB Routes** or **Pattern Matching**
- If product IDs must be validated → Cached DB
- If controller validation is acceptable → Pattern Matching

**Blog/CMS (10,000+ posts)**
→ **Pattern Matching Routes**
- Large dataset
- Public-facing (less security concern)
- Performance is critical
- Posts can be 404'd in controller

**API Endpoints**
→ **Pattern Matching Routes**
- RESTful design fits patterns naturally
- Validation happens in controller anyway
- Maximum performance needed

**Configuration/Settings (< 100 items)**
→ **Dynamic DB Routes**
- Very small dataset
- Overhead doesn't matter
- Simplest code

---

## Migration Guides

### Migrating from Dynamic DB to Cached DB

**Step 1: Backup current Routes.php**
```powershell
Copy-Item modules/admin/routes/Routes.php modules/admin/routes/Routes_BACKUP.php
```

**Step 2: Create cache directory**
```powershell
New-Item -Path modules/cache -ItemType Directory -Force
```

**Step 3: Update Routes.php**
Add caching logic (see [Example 1](#example-1-admin-module-with-cached-routes))

**Step 4: Update Controller.php**
Add cache invalidation:
```php
use Admin\Routes\Routes;

private function createUser() {
    // ... create user logic ...
    Routes::clearCache();
}

private function deleteUser($id) {
    // ... delete user logic ...
    Routes::clearCache();
}
```

**Step 5: Test**
```
1. Visit /admin → Cache should be created
2. Check modules/cache/admin_routes.php exists
3. Create new user → Cache should be regenerated
4. Delete user → Cache should be regenerated
```

---

### Migrating from Dynamic DB to Pattern Matching

**Step 1: Backup Router.php**
```powershell
Copy-Item etc/Router.php etc/Router_BACKUP.php
```

**Step 2: Update Router.php**
Add pattern matching methods (see [Example 2](#example-2-blog-module-with-pattern-matching))

**Step 3: Update Routes.php**
Replace explicit routes with patterns:
```php
// OLD:
foreach ($users as $user) {
    $router->addRoute('/admin/users/edit/' . $user['id'], ...);
}

// NEW:
$router->addRoute('/admin/users/edit/{id}', Controller::class, 'display');
```

**Step 4: Update Controller.php**
Add ID validation:
```php
private function showUserForm()
{
    // Extract ID from pattern match
    $userId = isset($_GET['id']) ? (int)$_GET['id'] : null;
    
    if ($userId) {
        // Validate ID exists in database
        $user = $this->model->getUserById($userId);
        if (!$user) {
            http_response_code(404);
            echo "User not found";
            return;
        }
    }
    
    // ... rest of logic ...
}
```

**Step 5: Test**
```
1. Visit /admin/users/edit/1 → Should work (valid ID)
2. Visit /admin/users/edit/999999 → Should show "User not found"
3. Visit /admin/users/edit/invalid → Should show "User not found" or "Invalid ID"
```

---

## Summary

### Quick Reference

| Approach | Best For | Speed | Code | Security |
|----------|----------|-------|------|----------|
| **Dynamic DB** | < 100 records, Development | ❌ Slow | ✅ Simple | ✅ High |
| **Cached DB** | 100-100k records, Production | ✅ Fast | ⚠️ Medium | ✅ High |
| **Pattern Matching** | > 100k records, High-scale | ✅ Fastest | ✅ Simple | ⚠️ Medium |

### Recommended Defaults

- **Small Project (<1000 records):** Start with **Dynamic DB**, migrate to **Cached DB** in production
- **Medium Project (1000-100k records):** Use **Cached DB**
- **Large Project (>100k records):** Use **Pattern Matching**
- **API/REST Services:** Use **Pattern Matching**

### Final Thoughts

All three approaches are valid! Choose based on:
1. Dataset size
2. Performance requirements
3. Security requirements
4. Code complexity tolerance
5. Team familiarity with patterns

**When in doubt, start with Cached DB** - it offers the best balance of performance, security, and code simplicity for most applications.

