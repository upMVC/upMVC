# Feature #1: HTTP Method-Aware Routing

**Priority:** 🔴 Critical - Implement immediately after next release  
**Status:** Planned  
**Target Version:** v2.1.0  
**Assigned:** Core Team  
**Created:** 2025-11-16  
**Complexity:** Medium (2-3 days)

---

## Executive Summary

Upgrade the Router to recognize HTTP methods (GET, POST, PUT, DELETE, etc.) as part of route matching, enabling RESTful routing patterns, automatic 405 responses, and verb-specific middleware.

**Current limitation:** Router matches only by URI path, forcing controllers to manually check `$reqMet` and handle all HTTP methods in one bloated method.

**After implementation:** Router will route different HTTP methods to different controller methods automatically, improving security, code organization, and RESTful API development.

---

## Business Value

### Security Benefits
- ✅ Automatic 405 Method Not Allowed responses
- ✅ Prevents forgotten `$reqMet` checks (common vulnerability)
- ✅ Router acts as security gatekeeper before controller execution
- ✅ Method-specific middleware (CSRF only on POST, rate limiting on DELETE)

### Developer Experience
- ✅ Clean, focused controller methods (no more if/else chains)
- ✅ RESTful routing patterns match industry standards
- ✅ Easier testing (isolated verb-specific methods)
- ✅ Better IDE autocomplete and type hinting

### Framework Competitiveness
- ✅ Matches Laravel, Symfony, Express.js routing capabilities
- ✅ Enables modern REST API development
- ✅ Attracts developers familiar with standard frameworks

---

## Technical Specification

### 1. Router.php Changes

#### Modified Method Signatures

```php
// OLD:
public function addRoute($route, $className, $methodName, array $middleware = [])

// NEW:
public function addRoute(
    string $route, 
    string $className, 
    string $methodName, 
    array $middleware = [], 
    array $httpMethods = ['GET']  // <-- NEW PARAMETER
)
```

```php
// OLD:
public function addParamRoute(
    string $pattern, 
    string $className, 
    string $methodName, 
    array $middleware = [],
    array $constraints = []
)

// NEW:
public function addParamRoute(
    string $pattern, 
    string $className, 
    string $methodName, 
    array $middleware = [],
    array $constraints = [],
    array $httpMethods = ['GET']  // <-- NEW PARAMETER
)
```

#### Storage Structure Change

```php
// OLD STRUCTURE:
$this->routes[$route] = [
    'className' => ...,
    'methodName' => ...,
    'middleware' => []
];

// NEW STRUCTURE:
foreach ($httpMethods as $method) {
    $this->routes[strtoupper($method)][$route] = [
        'className' => ...,
        'methodName' => ...,
        'middleware' => [],
        'allowedMethods' => $httpMethods  // For 405 responses
    ];
}
```

#### Dispatcher Logic Update

```php
public function dispatcher($reqRoute, $reqMet, ?string $reqURI = null)
{
    $method = strtoupper($reqMet);
    
    // 1. Try exact route with correct method
    if (isset($this->routes[$method][$reqRoute])) {
        // Dispatch to controller
        return $this->execute($this->routes[$method][$reqRoute], $reqRoute, $reqMet);
    }
    
    // 2. Check if route exists with different method (405 case)
    if ($this->routeExistsWithDifferentMethod($reqRoute, $method)) {
        return $this->send405($reqRoute, $this->getAllowedMethods($reqRoute));
    }
    
    // 3. Try parameterized routes with method filtering
    $match = $this->matchParamRoute($reqRoute, $method);
    if ($match !== null) {
        return $this->executeParamRoute($match, $reqRoute, $reqMet);
    }
    
    // 4. 404 Not Found
    return $this->handle404($reqRoute);
}
```

#### New Helper Methods

```php
/**
 * Check if route exists with different HTTP method
 */
private function routeExistsWithDifferentMethod(string $route, string $method): bool
{
    foreach ($this->routes as $httpMethod => $routes) {
        if ($httpMethod !== $method && isset($routes[$route])) {
            return true;
        }
    }
    return false;
}

/**
 * Get all allowed methods for a route
 */
private function getAllowedMethods(string $route): array
{
    $allowed = [];
    foreach ($this->routes as $httpMethod => $routes) {
        if (isset($routes[$route])) {
            $allowed[] = $httpMethod;
        }
    }
    return $allowed;
}

/**
 * Send 405 Method Not Allowed response
 */
private function send405(string $route, array $allowedMethods): void
{
    http_response_code(405);
    header('Allow: ' . implode(', ', $allowedMethods));
    
    echo json_encode([
        'error' => 'Method Not Allowed',
        'message' => "Route {$route} does not support this HTTP method",
        'allowed_methods' => $allowedMethods
    ]);
    exit;
}
```

### 2. Parameterized Route Updates

Update `$paramRoutes` storage to include method filtering:

```php
// Add httpMethods to route data
$routeData = [
    'pattern' => $pattern,
    'segments' => $segments,
    'params' => $params,
    'types' => $types,
    'constraints' => $constraints,
    'className' => $className,
    'methodName' => $methodName,
    'middleware' => $middleware,
    'name' => null,
    'httpMethods' => $httpMethods  // <-- NEW
];

// Update matchParamRoute to accept method parameter
private function matchParamRoute(string $reqRoute, string $method): ?array
{
    // ... existing matching logic ...
    
    // Add method check before returning match
    if (!in_array($method, $route['httpMethods'])) {
        continue;  // Skip routes that don't support this method
    }
    
    return ['route' => $route, 'params' => $captured];
}
```

---

## Backwards Compatibility Strategy

### Default Parameter Value
All existing routes will default to `['GET']` if no HTTP methods specified:

```php
// This continues to work (defaults to GET):
$router->addRoute('/users', Controller::class, 'display');

// Equivalent to:
$router->addRoute('/users', Controller::class, 'display', [], ['GET']);
```

### Migration Period
- **Phase 1 (v2.1.0):** Router supports HTTP methods, all existing routes work
- **Phase 2 (v2.2.0):** Update documentation with new patterns
- **Phase 3 (v2.3.0):** Deprecate manual `$reqMet` checks in controllers
- **Phase 4 (v3.0.0):** Remove legacy support (breaking change)

---

## Implementation Checklist

### Core Router Implementation
- [ ] Update `addRoute()` signature with `$httpMethods` parameter
- [ ] Update `addParamRoute()` signature with `$httpMethods` parameter
- [ ] Refactor `$routes` array to be method-first indexed
- [ ] Refactor `$paramRoutes` to include `httpMethods` field
- [ ] Implement `routeExistsWithDifferentMethod()` helper
- [ ] Implement `getAllowedMethods()` helper
- [ ] Implement `send405()` response handler
- [ ] Update `dispatcher()` with method-aware logic
- [ ] Update `matchParamRoute()` with method filtering
- [ ] Add comprehensive PHPDoc comments

### Testing
- [ ] Test GET/POST to same route with different handlers
- [ ] Test 405 response with correct Allow header
- [ ] Test backwards compatibility (old routes still work)
- [ ] Test parameterized routes with method filtering
- [ ] Test named routes still generate correctly
- [ ] Test middleware execution with different methods
- [ ] Test constraint validation with HTTP methods
- [ ] Regression test all existing modules

### Documentation
- [ ] Update `README.md` routing examples
- [ ] Create `docs/routing/HTTP_METHOD_ROUTING.md` guide
- [ ] Update `docs/routing/ROUTER_V2_EXAMPLES.md`
- [ ] Add migration guide in `CHANGELOG.md`
- [ ] Update controller best practices guide
- [ ] Add REST API example module

### Code Generation Templates
- [ ] Update ModuleGeneratorEnhanced templates
- [ ] Update CrudModuleGenerator templates
- [ ] Generate separate methods for GET/POST/PUT/DELETE
- [ ] Remove `if ($reqMet)` checks from generated code

### Example Module Updates (Optional)
- [ ] Refactor Admin module routes
- [ ] Refactor Admin controller (split display method)
- [ ] Update Userorm module
- [ ] Create new REST API example module

---

## Example Usage (After Implementation)

### Before (Current):
```php
// Routes
$router->addRoute('/admin/users', Controller::class, 'display');

// Controller - bloated method
public function display($reqRoute, $reqMet) {
    if ($reqMet === 'GET') {
        // Show list
    } elseif ($reqMet === 'POST') {
        // Create user
    } elseif ($reqMet === 'DELETE') {
        // Delete user
    }
}
```

### After (Method-Aware):
```php
// Routes - clean separation
$router->addRoute('/admin/users', Controller::class, 'index', [], ['GET']);
$router->addRoute('/admin/users', Controller::class, 'store', [], ['POST']);
$router->addParamRoute('/admin/users/{id}', Controller::class, 'destroy', [], [], ['DELETE']);

// Controllers - focused methods
public function index($reqRoute, $reqMet) {
    // Show list (guaranteed GET)
    $users = $this->model->getAll();
    $this->view->render(['users' => $users]);
}

public function store($reqRoute, $reqMet) {
    // Create user (guaranteed POST)
    $this->model->create($_POST);
    redirect('/admin/users');
}

public function destroy($reqRoute, $reqMet) {
    // Delete user (guaranteed DELETE, $id already validated)
    $this->model->delete($_GET['id']);
    echo json_encode(['success' => true]);
}
```

### Convenience Methods (Future Enhancement):
```php
// Helper methods for common patterns
$router->get('/admin/users', Controller::class, 'index');
$router->post('/admin/users', Controller::class, 'store');
$router->put('/admin/users/{id}', Controller::class, 'update');
$router->delete('/admin/users/{id}', Controller::class, 'destroy');

// Route groups with shared middleware
$router->group(['prefix' => '/admin', 'middleware' => ['auth']], function($router) {
    $router->get('/users', Controller::class, 'index');
    $router->post('/users', Controller::class, 'store');
});
```

---

## Risk Assessment

### Low Risk ✅
- Backwards compatible by design (default to GET)
- No changes to existing route files required
- Gradual migration possible

### Medium Risk ⚠️
- Parameterized route storage refactoring (test thoroughly)
- Middleware execution order (ensure consistency)
- Named route generation (verify unaffected)

### Mitigation
- Comprehensive test suite before release
- Beta testing with existing modules
- Detailed migration documentation
- Fallback to old behavior via config flag if needed

---

## Success Metrics

### Code Quality
- [ ] 90%+ code coverage for Router.php
- [ ] All existing tests pass
- [ ] No performance regression (benchmark dispatcher)

### Developer Experience
- [ ] 50%+ reduction in controller method sizes
- [ ] Zero manual `$reqMet` checks in new code
- [ ] Positive feedback from beta testers

### Security
- [ ] 100% 405 response coverage for invalid methods
- [ ] Zero security regressions in existing modules

---

## Timeline Estimate

| Phase | Task | Duration | Dependencies |
|-------|------|----------|--------------|
| 1 | Core Router.php updates | 1 day | None |
| 2 | Testing & debugging | 1 day | Phase 1 |
| 3 | Documentation | 0.5 days | Phase 2 |
| 4 | Template updates | 0.5 days | Phase 2 |
| 5 | Beta testing | 1 day | All phases |

**Total:** 3-4 days development time

---

## Related Documents

- [Router HTTP Method Support Report](../../docs/routing/ROUTER_HTTP_METHOD_SUPPORT_REPORT.md) - Analysis & diagnosis
- [Router V2 Status](../../docs/routing/ROUTER_V2_STATUS.md) - Current capabilities
- [Router V2 Examples](../../docs/routing/ROUTER_V2_EXAMPLES.md) - Usage patterns

---

## Notes & Decisions

### Why not break backwards compatibility?
upMVC philosophy emphasizes stability. Defaulting to GET allows gradual migration without breaking existing applications.

### Why not use regex in route strings?
Complexity vs. benefit. Parameterized routes with constraints cover 99% of use cases without regex overhead.

### Why not auto-generate HEAD/OPTIONS?
Future enhancement. Phase 1 focuses on core HTTP method routing. Auto-generated OPTIONS for CORS can come in v2.2.0.

---

**Next Action:** Implement in dedicated branch `feature/http-method-routing` after v2.0.0 release

**Review Date:** After next stable release

**Last Updated:** 2025-11-16
