# Routing Quick Reference Card

Quick reference for upMVC routing strategies.

## 🎯 Which Approach?

| Records | Approach | File |
|---------|----------|------|
| < 100 | Dynamic DB | Default |
| 100 - 100k | **Cached DB** ⭐ | [Routes_WithCache.php](examples/Routes_WithCache.php) |
| > 100k | Pattern Matching | [Router_PatternMatching.php](examples/Router_PatternMatching.php) |

## ⚡ Performance

| Approach | Speed | DB Query | Memory |
|----------|-------|----------|--------|
| Dynamic DB | 100ms | Every request | High |
| Cached DB | 2ms | Once/hour | High |
| Pattern Matching | 0.5ms | Never | Low |

## 📝 Code Examples

### Dynamic DB (Default)
```php
public function routes($router)
{
    $users = $model->getAllUsers();  // Query DB
    foreach ($users as $user) {
        $router->addRoute('/users/edit/' . $user['id'], ...);
    }
}
```

### Cached DB (Recommended)
```php
public function routes($router)
{
    if ($this->isCacheValid()) {
        $routes = include $this->cacheFile;  // Read cache
    } else {
        $users = $model->getAllUsers();  // Query DB once
        foreach ($users as $user) {
            $routes[] = '/users/edit/' . $user['id'];
        }
        file_put_contents($this->cacheFile, $routes);
    }
    foreach ($routes as $route) {
        $router->addRoute($route, ...);
    }
}
```

### Pattern Matching (Scalable)
```php
public function routes($router)
{
    $router->addRoute('/users/edit/{id}', ...);  // No DB query
}

// Controller must validate:
public function display()
{
    $userId = (int)$_GET['id'];
    $user = $this->model->getUserById($userId);
    if (!$user) {
        http_response_code(404);
        return;
    }
}
```

## 🔧 Installation Commands

### Cached DB
```powershell
# Create cache directory
New-Item -Path "modules/cache" -ItemType Directory

# Copy example
Copy-Item "docs/routing/examples/Routes_WithCache.php" "modules/yourmodule/routes/Routes.php"

# Update namespace in Routes.php
```

### Pattern Matching
```powershell
# Backup Router
Copy-Item "etc/Router.php" "etc/Router_BACKUP.php"

# Install
Copy-Item "docs/routing/examples/Router_PatternMatching.php" "etc/Router.php" -Force
```

## 🧪 Testing

### Test Pattern
```powershell
php docs/routing/examples/Pattern_Tester.php
```

### Test Cache
```php
Routes::getCacheStats();
// Returns: ['exists' => true, 'age' => 300, 'routes' => 48]
```

## 📊 Pattern Examples

| Pattern | Matches | Parameters |
|---------|---------|------------|
| `/users/{id}` | `/users/123` | `$_GET['id'] = '123'` |
| `/users/*` | `/users/123` | None |
| `/blog/{author}/{year}` | `/blog/john/2025` | `$_GET['author'] = 'john'`<br>`$_GET['year'] = '2025'` |
| `/api/{version}/users/{id}` | `/api/v1/users/123` | `$_GET['version'] = 'v1'`<br>`$_GET['id'] = '123'` |

## 🔐 Security

### Cached DB
✅ Only valid IDs have routes (validated by DB)
✅ Invalid IDs get 404 from Router

### Pattern Matching
⚠️ All IDs reach controller
✅ Must validate in controller:

```php
$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($userId <= 0) {
    http_response_code(400);
    return;
}
$user = $this->model->getUserById($userId);
if (!$user) {
    http_response_code(404);
    return;
}
```

## 💾 Cache Management

### Clear Cache
```php
\YourModule\Routes\Routes::clearCache();
```

### When to Clear
- After user creation ✅
- After user deletion ✅
- After user ID change (rare)
- After update (optional)

### Cache Location
```
modules/cache/yourmodule_routes.php
```

### Cache Lifetime
```php
private int $cacheLifetime = 3600; // 1 hour
```

## 🔄 Migration

### Dynamic → Cached
1. Add cache directory
2. Add `isCacheValid()` method
3. Add `loadCachedRoutes()` method
4. Add `saveCache()` method
5. Add `clearCache()` calls in controller

### Dynamic → Pattern
1. Install pattern Router
2. Replace loops with patterns
3. Add validation in controller

## ⚠️ Common Pitfalls

### Cached DB
❌ Forgot to clear cache after CRUD
❌ Cache directory not writable
❌ Cache file too large (> 1MB)

### Pattern Matching
❌ No validation in controller
❌ SQL injection from $_GET
❌ Patterns too greedy (match too much)

## 📚 Full Documentation

- **[ROUTING_STRATEGIES.md](../ROUTING_STRATEGIES.md)** - Complete guide
- **[README.md](../README.md)** - Routing overview
- **[Router_PatternMatching_README.md](examples/Router_PatternMatching_README.md)** - Pattern installation

## 🎯 Decision Tree

```
Start
  │
  ├─ < 100 records?
  │  └─ YES → Dynamic DB
  │  └─ NO → Continue
  │
  ├─ > 100,000 records?
  │  └─ YES → Pattern Matching
  │  └─ NO → Continue
  │
  ├─ Security critical?
  │  └─ YES → Cached DB
  │  └─ NO → Pattern Matching
  │
  └─ Default → Cached DB ⭐
```

## 💡 Tips

1. **Start simple** - Use Dynamic DB in development
2. **Add cache** - Switch to Cached DB for production
3. **Scale up** - Use Pattern Matching only if needed
4. **Measure first** - Test your actual performance before optimizing
5. **Validate always** - Never trust user input, regardless of routing approach

