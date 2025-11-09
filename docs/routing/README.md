# Routing Documentation

Complete guide to routing strategies in upMVC.

## 🎯 Quick Start

**🌟 START HERE:** **[THE_COMPLETE_PICTURE.md](THE_COMPLETE_PICTURE.md)** - ⭐⭐⭐ **Everything explained from .htaccess to controller** - Why each piece exists, when to use each routing strategy, complete visual flow

**Then read:** **[ROUTING_GUIDE.md](ROUTING_GUIDE.md)** - ⭐ Complete unified routing guide - All routing types, decision tree, Router V2 features, and migration guides

## 📚 Documentation

### Main Guides
- **[ROUTING_GUIDE.md](ROUTING_GUIDE.md)** - ⭐ **NEW!** Complete unified guide covering all 5 routing types with decision tree
- **[ROUTER_V2_EXAMPLES.md](ROUTER_V2_EXAMPLES.md)** - ⭐ Router V2 enhanced features (type casting, validation, named routes)
- **[PARAMETERIZED_ROUTING.md](PARAMETERIZED_ROUTING.md)** - Complete guide to lightweight parameterized routing with admin module examples
- **[ROUTING_STRATEGIES.md](ROUTING_STRATEGIES.md)** - Detailed guide covering routing approaches, performance analysis, and implementation examples

## 📁 Examples

All working code examples are in the `examples/` directory:

### Pattern Matching
- **[Router_PatternMatching.php](examples/Router_PatternMatching.php)** - Production-ready Router with pattern matching support
- **[Router_PatternMatching_README.md](examples/Router_PatternMatching_README.md)** - Installation and usage guide
- **[Pattern_Tester.php](examples/Pattern_Tester.php)** - Test script for pattern matching

### Cached Database Routes
- **[Routes_WithCache.php](examples/Routes_WithCache.php)** - Routes implementation with file-based caching
- **[Controller_WithCache.php](examples/Controller_WithCache.php)** - Controller with cache invalidation

## 🎯 Quick Start

**NEW:** **[ROUTING_GUIDE.md](ROUTING_GUIDE.md)** - ⭐ **Complete Unified Routing Guide** - All routing types, when to use each, decision tree, Router V2 features, and migration guides

### Choose Your Routing Type

upMVC offers **5 routing strategies**. Use the decision tree:

```
How many records?
│
├─ 0 (Static pages) → Simple Static Routes
│
├─ < 100 records
│  ├─ Development? → Database-Driven (no cache)
│  └─ Production? → Cached Database Routes
│
├─ 100-1,000 records
│  ├─ Need type safety? → Router V2 Enhanced ⭐
│  ├─ Security-first? → Cached Database Routes
│  └─ Default → Parameterized Routing (Basic)
│
└─ > 1,000 records
   ├─ Need type safety? → Router V2 Enhanced ⭐
   └─ Default → Parameterized Routing (Basic)
```

**Need type safety, validation, or named routes at any scale?**  
→ Use **[Router V2 Enhanced](ROUTING_GUIDE.md#router-v2-enhanced-features)** ⭐

**See:** [ROUTING_GUIDE.md](ROUTING_GUIDE.md) for complete decision tree and examples

### Installation Guides

#### Cached Database Approach (Recommended)

**Step 1:** Create cache directory
```powershell
New-Item -Path "modules/cache" -ItemType Directory -Force
```

**Step 2:** Copy example files
```powershell
# Copy Routes implementation
Copy-Item "docs/routing/examples/Routes_WithCache.php" "modules/yourmodule/routes/Routes.php"

# Copy Controller with cache invalidation
Copy-Item "docs/routing/examples/Controller_WithCache.php" "modules/yourmodule/Controller.php"
```

**Step 3:** Update namespace in copied files to match your module

**Step 4:** Test - cache will be created on first request

---

#### Pattern Matching Approach

**Step 1:** Backup original Router
```powershell
Copy-Item "etc/Router.php" "etc/Router_BACKUP.php"
```

**Step 2:** Install pattern matching Router
```powershell
Copy-Item "docs/routing/examples/Router_PatternMatching.php" "etc/Router.php" -Force
```

**Step 3:** Update your routes to use patterns
```php
// OLD: Explicit routes for each ID
foreach ($users as $user) {
    $router->addRoute('/users/edit/' . $user['id'], ...);
}

// NEW: Single pattern
$router->addRoute('/users/edit/{id}', Controller::class, 'display');
```

**Step 4:** Update controller to validate IDs
```php
public function display()
{
    $userId = isset($_GET['id']) ? (int)$_GET['id'] : null;
    
    if (!$userId) {
        http_response_code(400);
        return;
    }
    
    $user = $this->model->getUserById($userId);
    if (!$user) {
        http_response_code(404);
        return;
    }
    
    // ... rest of logic
}
```

## 📊 Performance Comparison

| Approach | Request Time | Memory | Best For |
|----------|-------------|---------|----------|
| **Simple Static** | 0.1ms | 10KB | Fixed pages |
| **Parameterized Routes** | 0.5ms | 20KB | ⭐ 1,000+ records, dynamic data |
| **Router V2 Enhanced** | 0.6ms | 25KB | ⭐ Type-safe apps, APIs |
| **Dynamic DB** | 100ms | 50KB | Development, < 100 records |
| **Cached DB** | 2ms | 500KB-2MB | Production, 100-10,000 records |

**Learn more:** [ROUTING_GUIDE.md](ROUTING_GUIDE.md) - Complete guide with decision tree, Router V2 features, and migration guides

## 🔍 Detailed Documentation

### Routing Strategies Guide

The main [ROUTING_STRATEGIES.md](ROUTING_STRATEGIES.md) document covers:

1. **Overview** - Philosophy and approach comparison
2. **The Three Approaches** - Detailed explanation of each method
3. **Detailed Comparison** - Feature-by-feature comparison table
4. **Performance Analysis** - Real-world benchmarks
5. **Implementation Examples** - Complete working code
6. **Pattern Matching Deep Dive** - How pattern conversion works
7. **Cache Strategy Deep Dive** - Cache management techniques
8. **Choosing the Right Approach** - Decision tree
9. **Migration Guides** - Step-by-step migration instructions

## 💡 Real-World Examples

### Admin Dashboard (Cached DB)
```php
// routes/Routes.php
public function routes($router)
{
    if ($this->isCacheValid()) {
        $this->loadCachedRoutes($router);
    } else {
        $users = $model->getAllUsers();
        foreach ($users as $user) {
            $router->addRoute('/admin/users/edit/' . $user['id'], ...);
        }
        $this->saveCache($routes);
    }
}

// Controller.php
private function createUser()
{
    $result = $this->model->createUser($data);
    Routes::clearCache(); // Invalidate cache
}
```

### Blog/CMS (Pattern Matching)
```php
// routes/Routes.php
public function routes($router)
{
    $router->addRoute('/blog/post/{id}', Controller::class, 'display');
    $router->addRoute('/blog/{category}/{slug}', Controller::class, 'display');
}

// Controller.php
public function display()
{
    if (isset($_GET['id'])) {
        $post = $this->model->getPostById($_GET['id']);
        // Validate and display...
    }
}
```

## 🛠️ Testing

### Test Pattern Matching
```powershell
php docs/routing/examples/Pattern_Tester.php
```

This will test various patterns and show regex conversion results.

### Test Cache Performance
```php
// Add to your controller
$start = microtime(true);
$routes = new Routes();
$routes->routes($router);
$time = (microtime(true) - $start) * 1000;
echo "Route loading: {$time}ms";
```

## 📖 See Also

- [upMVC Main Documentation](../)
- [Module Philosophy](../MODULE_PHILOSOPHY.md) - Understanding reference implementations
- [Admin Module Example](../../modules/admin/README.md) - Cached routing in action
- [Pure PHP Philosophy](../PHILOSOPHY_PURE_PHP.md) - The upMVC approach

## ❓ FAQ

**Q: Which approach should I use?**
A: Start with Cached DB for most applications. Only use Pattern Matching if you have > 100k records.

**Q: Can I mix approaches?**
A: Yes! Use Cached DB for some modules and Pattern Matching for others.

**Q: Is pattern matching secure?**
A: Yes, but you must validate IDs in your controller. Pattern matching only validates the URL structure, not the data.

**Q: How often does cache refresh?**
A: Default is 1 hour, but you can configure it. Cache is also cleared on CRUD operations.

**Q: What if I have millions of records?**
A: Use Pattern Matching. It has the same performance regardless of record count.

