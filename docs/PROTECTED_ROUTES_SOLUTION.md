# Protected Routes Configuration - Best Practices Analysis

## 🤔 The Question

Where should `$protectedRoutes` be defined?

**Current location (Line 96):**
```php
private function setupEnhancedMiddleware($router): void
{
    // ...
    $protectedRoutes = ['/dashboardexample/*', '/admin/*', '/users/*', '/moda'];
    $middlewareManager->addGlobal(new AuthMiddleware($protectedRoutes));
}
```

---

## 🎯 Three Options

### **Option 1: Class Property (Recommended)**
Define as a class property at the top:

```php
class Start
{
    private $reqURI;
    private $reqMethod;
    private $reqRoute;
    
    // Protected routes configuration
    private $protectedRoutes = [
        '/dashboardexample/*',
        '/admin/*',
        '/users/*',
        '/moda'
    ];
    
    // ... rest of class
}
```

**Pros:**
- ✅ Clear and visible at class beginning
- ✅ Easy to modify
- ✅ No external dependency
- ✅ Fast (no file reading)
- ✅ Type-safe (PHP array)

**Cons:**
- ❌ Requires code change to modify
- ❌ Need to redeploy to change routes

---

### **Option 2: Static Class Property (Better for Consistency)**
Define as static property (matches other config):

```php
class Start
{
    /**
     * Protected routes requiring authentication
     * Add your protected routes here
     */
    private static $protectedRoutes = [
        '/dashboardexample/*',
        '/admin/*',
        '/users/*',
        '/moda'
    ];
    
    // ... rest of class
}
```

**Pros:**
- ✅ Same pattern as Config class
- ✅ Can be accessed statically if needed
- ✅ Clear documentation
- ✅ Easy to find and modify

**Cons:**
- ❌ Still requires code change

---

### **Option 3: .env File (Most Flexible)**
Define in .env and read from there:

```env
# .env
PROTECTED_ROUTES=/dashboardexample/*,/admin/*,/users/*,/moda
```

```php
// In Start.php
private function setupEnhancedMiddleware($router): void
{
    $protectedRoutesString = Environment::get('PROTECTED_ROUTES', '');
    $protectedRoutes = !empty($protectedRoutesString) 
        ? explode(',', $protectedRoutesString)
        : ['/admin/*']; // Default fallback
    
    $middlewareManager->addGlobal(new AuthMiddleware($protectedRoutes));
}
```

**Pros:**
- ✅ No code changes needed to modify
- ✅ Different per environment (dev, staging, prod)
- ✅ Can be changed without redeployment
- ✅ Follows 12-factor app principles

**Cons:**
- ❌ .env parsing adds complexity
- ❌ Comma-separated strings less clear than arrays
- ❌ Typos harder to catch
- ❌ Not type-safe

---

## 💡 My Recommendation: **Hybrid Approach**

Use **class property with .env override**:

```php
class Start
{
    /**
     * Default protected routes requiring authentication
     * Can be overridden via PROTECTED_ROUTES in .env (comma-separated)
     */
    private static $defaultProtectedRoutes = [
        '/dashboardexample/*',
        '/admin/*',
        '/users/*',
        '/moda'
    ];
    
    // ... rest of class
    
    private function getProtectedRoutes(): array
    {
        // Check if overridden in .env
        $envRoutes = Environment::get('PROTECTED_ROUTES', '');
        
        if (!empty($envRoutes)) {
            return array_map('trim', explode(',', $envRoutes));
        }
        
        // Use defaults
        return self::$defaultProtectedRoutes;
    }
    
    private function setupEnhancedMiddleware($router): void
    {
        $middlewareManager = $router->getMiddlewareManager();

        $middlewareManager->addGlobal(new LoggingMiddleware());
        
        if (ConfigManager::get('app.cors.enabled', false)) {
            $corsConfig = ConfigManager::get('app.cors', []);
            $middlewareManager->addGlobal(new CorsMiddleware($corsConfig));
        }

        // Get protected routes (from .env or defaults)
        $protectedRoutes = $this->getProtectedRoutes();
        $middlewareManager->addGlobal(new AuthMiddleware($protectedRoutes));
    }
}
```

**Why This is Best:**
1. ✅ **Clear defaults** in code (visible, documented)
2. ✅ **Flexible overrides** via .env (when needed)
3. ✅ **Best of both worlds** - clarity + flexibility
4. ✅ **Follows your pattern** - like `$fallbacks` in Config.php
5. ✅ **Production-ready** - different routes per environment if needed

---

## 🎨 Visual Comparison

### Current (Inline):
```
setupEnhancedMiddleware() {
    $protectedRoutes = [...]  ← Buried in method
    new AuthMiddleware($protectedRoutes)
}
```
❌ Hard to find
❌ Not reusable

---

### Option 1 (Class Property):
```
class Start {
    private $protectedRoutes = [...]  ← Visible at top
    
    setupEnhancedMiddleware() {
        new AuthMiddleware($this->protectedRoutes)
    }
}
```
✅ Easy to find
✅ Clear

---

### Hybrid (Recommended):
```
class Start {
    private static $defaultProtectedRoutes = [...]  ← Clear defaults
    
    getProtectedRoutes() {
        Check .env → Return custom OR defaults
    }
    
    setupEnhancedMiddleware() {
        $routes = $this->getProtectedRoutes()  ← Smart getter
        new AuthMiddleware($routes)
    }
}
```
✅ Clear defaults
✅ Flexible overrides
✅ Best practice

---

## 📝 Complete Implementation (Recommended)

```php
<?php

namespace upMVC;

use upMVC\Config\ConfigManager;
use upMVC\Config\Environment;
use upMVC\Exceptions\ErrorHandler;
use upMVC\Middleware\AuthMiddleware;
use upMVC\Middleware\LoggingMiddleware;
use upMVC\Middleware\CorsMiddleware;

class Start
{
    private $reqURI;
    private $reqMethod;
    private $reqRoute;
    
    /**
     * Default protected routes requiring authentication
     * 
     * These routes require user authentication before access.
     * Can be overridden via PROTECTED_ROUTES in .env (comma-separated list)
     * 
     * Examples:
     * - /admin/*          → All admin routes
     * - /dashboardexample/* → Dashboard routes
     * - /users/*          → User management
     * - /moda             → Specific route
     * 
     * IMPORTANT: Change these according to your application's protected areas!
     */
    private static $defaultProtectedRoutes = [
        '/dashboardexample/*',
        '/admin/*',
        '/users/*',
        '/moda'
    ];

    public function __construct()
    {
        $this->bootstrapApplication();
        $this->initializeRequest();
    }

    /**
     * Get protected routes from .env or use defaults
     * 
     * @return array Array of protected route patterns
     */
    private function getProtectedRoutes(): array
    {
        // Check if overridden in .env
        $envRoutes = Environment::get('PROTECTED_ROUTES', '');
        
        if (!empty($envRoutes)) {
            // Parse comma-separated routes from .env
            return array_map('trim', explode(',', $envRoutes));
        }
        
        // Use default routes
        return self::$defaultProtectedRoutes;
    }
    
    private function setupEnhancedMiddleware($router): void
    {
        $middlewareManager = $router->getMiddlewareManager();

        $middlewareManager->addGlobal(new LoggingMiddleware());
        
        if (ConfigManager::get('app.cors.enabled', false)) {
            $corsConfig = ConfigManager::get('app.cors', []);
            $middlewareManager->addGlobal(new CorsMiddleware($corsConfig));
        }

        // Get protected routes (from .env or defaults)
        $protectedRoutes = $this->getProtectedRoutes();
        $middlewareManager->addGlobal(new AuthMiddleware($protectedRoutes));
    }
    
    // ... rest of class
}
```

---

## 🔧 Optional .env Configuration

Add to `.env` if you want to override:

```env
# Protected Routes (comma-separated, optional - defaults in Start.php)
# PROTECTED_ROUTES=/dashboardexample/*,/admin/*,/users/*,/api/*,/moda
```

---

## ✅ Summary

**Best Solution: Hybrid Approach**

1. Define `$defaultProtectedRoutes` as **static property** at class beginning
2. Create `getProtectedRoutes()` method to check .env first
3. Falls back to defaults if .env not set
4. Add optional PROTECTED_ROUTES to .env

**Benefits:**
- ✅ Clear defaults visible in code
- ✅ Optional flexibility via .env
- ✅ Easy to find and modify
- ✅ Environment-specific if needed
- ✅ Follows same pattern as Config.php

**This gives you the best of both worlds!** 🚀
