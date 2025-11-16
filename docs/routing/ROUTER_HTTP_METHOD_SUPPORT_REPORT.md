# Router HTTP Method Support Report

**Date:** 2025-11-16  
**Scope:** `src/Etc/Router.php`, `src/Modules/Admin/Routes/*`, controllers relying on `$reqMet`  
**Status:** 🔴 Planned for v2.1.0 - Feature #1 Priority  
**Implementation Plan:** See `/vault/Features/1-HTTP_METHOD_ROUTING.md` (internal)

---

## Summary
The current Router v2 implementation matches requests solely by URI. Both `addRoute()` and `addParamRoute()` store only the path → controller mapping, so all HTTP verbs (`GET`, `POST`, `PUT`, etc.) that hit the same path are funneled into the same controller method. The framework passes `$reqMet` to controllers, leaving each handler to interpret the verb manually. This design works, but it prevents verb-specific routing, automatic `405 Method Not Allowed` responses, and verb-scoped middleware.

---

## Observations
- [`src/Etc/Router.php`](../src/Etc/Router.php) registers routes in `$this->routes[$route] = ['className' => ..., 'methodName' => ...]` without capturing the HTTP method.
- Parameterized routes collected via `addParamRoute()` are stored in `$this->paramRoutes` with pattern/segments/params, again without an allowed-method list.
- Admin controllers (and other modules) branch explicitly on `$reqMet` to decide whether to read, create, or delete data. Example: [`src/Modules/Admin/Controller.php::display`](../src/Modules/Admin/Controller.php).
- Alternate router examples (`Routesd.php`, `Routesc.php`) follow the same pattern: route definitions contain only the URI.

---

## Impact
- **Ambiguous routing:** Separate handlers for `GET /admin/users` and `POST /admin/users` cannot coexist; the last registered mapping wins.
- **Manual guards:** Developers must remember to return their own `405` responses when unsupported verbs reach a controller.
- **Dense controller methods:** Verb branching bloats handlers and complicates testing, especially for REST-style modules.
- **Middleware limitations:** Middleware cannot be scoped to specific verbs (e.g., CSRF only on `POST`, rate limiting only on `DELETE`).

---

## Recommended Fix
Extend the router to treat HTTP method as part of the routing key. Concretely:
1. Allow `addRoute()` and `addParamRoute()` to accept either a single method or an array of methods (defaulting to `GET` for backward compatibility).
2. Store route definitions in a structure keyed by method, e.g. `$this->routes[$method][$route]` and `$this->paramRoutesByMethod[$method]`.
3. Update `dispatcher()` to look up the incoming `$reqMet` first; if no match exists for that verb but another verb is registered for the same path, respond with `405 Method Not Allowed` (include `Allow` header).
4. Ensure parameter extraction, middleware execution, and named route helpers continue to work by carrying the method meta alongside the existing route data.
5. Maintain backwards compatibility by treating historical registrations (with no explicit method) as `GET` and/or optionally `['GET','POST']` based on a config flag.

---

## Implementation Outline
```php
// addRoute signature
public function addRoute(string $route, string $class, string $method, array $middleware = [], array $httpMethods = ['GET'])

// storage
foreach ($httpMethods as $httpMethod) {
    $normalized = strtoupper($httpMethod);
    $this->routes[$normalized][$route] = [
        'className' => $class,
        'methodName' => $method,
        'middleware' => $middleware,
        'httpMethods' => $httpMethods,
    ];
}

// dispatcher
$normalizedMethod = strtoupper($reqMet);
if (isset($this->routes[$normalizedMethod][$reqRoute])) {
    // dispatch
} elseif ($this->pathExistsWithOtherMethod($reqRoute)) {
    $this->send405($reqRoute);
} else {
    // fall back to param routes / 404
}
```
For parameterized routes use a similar structure: bucket them first by method, then by prefix to preserve existing performance optimizations.

---

## Testing Checklist
1. Register distinct handlers for `GET /admin/users` and `POST /admin/users` and verify each executes as expected.
2. Hit a path with an unsupported verb to confirm a `405` response and correct `Allow` header.
3. Regression-test existing modules to ensure legacy `GET` routes still work without code changes.
4. Validate parameterized routes with verb filtering (e.g., `PUT /admin/users/{id:int}`) still cast and validate parameters.
5. Confirm named route generation (`route('admin.user.edit', ['id' => 5])`) remains unaffected.

---

## Next Steps
- [x] Analysis complete - documented all impacts
- [x] Created detailed implementation plan in vault
- [ ] Schedule for v2.1.0 release (post-current release)
- [ ] Implement in dedicated feature branch
- [ ] Comprehensive testing with all modules
- [ ] Update documentation and migration guide

**Implementation Timeline:**  
- **Target Version:** v2.1.0  
- **Priority:** Critical - First feature after next release  
- **Estimated Effort:** 3-4 days  
- **Detailed Plan:** `/vault/Features/1-HTTP_METHOD_ROUTING.md`

---

## Appendix
- Router source: [`src/Etc/Router.php`](../src/Etc/Router.php)
- Admin routes examples: [`src/Modules/Admin/Routes/Routes.php`](../src/Modules/Admin/Routes/Routes.php), [`Routesd.php`](../src/Modules/Admin/Routes/Routesd.php), [`Routesc.php`](../src/Modules/Admin/Routes/Routesc.php)
- Related docs: [`docs/ROUTER_V2_STATUS.md`](ROUTER_V2_STATUS.md), `docs/routing/ROUTER_V2_EXAMPLES.md`
