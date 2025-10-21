<?php
/*
 *   Simplified Router for upMVC with Pattern Matching Support
 *   Adds support for route patterns like /admin/users/edit/* or /admin/users/edit/{id}
 */

namespace upMVC;

use upMVC\Middleware\MiddlewareManager;

/**
 * Router with Pattern Matching
 * - Supports exact routes (backward compatible)
 * - NEW: Supports pattern routes with * or {param}
 * - No GET/POST wrappers (use $_GET/$_POST directly)
 * - Reuse REQUEST_URI/METHOD from Start.php
 */
class Router
{
    protected $routes = [];
    protected $patternRoutes = [];  // NEW: Separate array for pattern routes
    private MiddlewareManager $middlewareManager;
    protected $middleware = [];

    public function __construct()
    {
        $this->middlewareManager = new MiddlewareManager();
    }

    public function getMiddlewareManager(): MiddlewareManager
    {
        return $this->middlewareManager;
    }

    /**
     * Add route - now supports patterns with * or {param}
     * 
     * Examples:
     * - '/admin' - exact match (old behavior)
     * - '/admin/users/edit/*' - matches any ID
     * - '/admin/users/edit/{id}' - named parameter
     */
    public function addRoute($route, $className, $methodName, array $middleware = [])
    {
        // Check if route contains patterns
        if (strpos($route, '*') !== false || strpos($route, '{') !== false) {
            // Store as pattern route with regex
            $pattern = $this->convertToRegex($route);
            $this->patternRoutes[] = [
                'pattern' => $pattern,
                'original' => $route,
                'className' => $className, 
                'methodName' => $methodName,
                'middleware' => $middleware
            ];
        } else {
            // Store as exact match (old behavior - backward compatible)
            $this->routes[$route] = [
                'className' => $className, 
                'methodName' => $methodName,
                'middleware' => $middleware
            ];
        }
    }
    
    /**
     * NEW: Convert route pattern to regex
     * 
     * '/admin/users/edit/*' → '/^\/admin\/users\/edit\/([^\/]+)$/'
     * '/admin/users/edit/{id}' → '/^\/admin\/users\/edit\/(?P<id>[^\/]+)$/'
     */
    private function convertToRegex($route)
    {
        // Escape forward slashes
        $pattern = str_replace('/', '\/', $route);
        
        // Replace * with regex pattern (unnamed capture group)
        $pattern = str_replace('*', '([^\/]+)', $pattern);
        
        // Replace {param} with named capture group
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^\/]+)', $pattern);
        
        // Add start and end anchors
        return '/^' . $pattern . '$/';
    }

    /**
     * NEW: Match route - tries exact match first, then patterns
     * 
     * Returns: ['route' => [...], 'params' => [...]] or null
     */
    private function matchRoute($reqRoute)
    {
        // 1. Try exact match first (fastest - O(1))
        if (isset($this->routes[$reqRoute])) {
            return [
                'route' => $this->routes[$reqRoute],
                'params' => []
            ];
        }

        // 2. Try pattern matching (slower - O(n))
        foreach ($this->patternRoutes as $patternRoute) {
            if (preg_match($patternRoute['pattern'], $reqRoute, $matches)) {
                // Extract parameters
                $params = [];
                foreach ($matches as $key => $value) {
                    if (!is_numeric($key)) {
                        // Named parameter like {id}
                        $params[$key] = $value;
                    } elseif ($key > 0) {
                        // Numeric parameter from *
                        $params[] = $value;
                    }
                }
                
                return [
                    'route' => $patternRoute,
                    'params' => $params
                ];
            }
        }

        // 3. No match found
        return null;
    }

    public function addMiddleware(string $name, callable $middleware): void
    {
        $this->middleware[$name] = $middleware;
    }

    /**
     * MODIFIED: Dispatcher with pattern matching support
     * @param string $reqRoute - from $config->getReqRoute($reqURI)
     * @param string $reqMet - from $_SERVER['REQUEST_METHOD']
     * @param string|null $reqURI - original URI with query parameters for middleware
     */
    public function dispatcher($reqRoute, $reqMet, ?string $reqURI = null)
    {
        // Simple request context (reuses your variables + original URI)
        $request = [
            'route' => $reqRoute,
            'method' => $reqMet,
            'uri' => $reqURI,  // ALWAYS use the original URI from Start.php
            'timestamp' => time()
        ];

        // NEW: Use matchRoute instead of direct array access
        $matchResult = $this->matchRoute($reqRoute);
        
        if ($matchResult !== null) {
            $route = $matchResult['route'];
            $params = $matchResult['params'];
            
            // Execute middleware pipeline
            return $this->middlewareManager->execute(
                $reqRoute,
                $request,
                function ($request) use ($route, $reqRoute, $reqMet, $params) {
                    // Execute legacy middleware for backward compatibility
                    foreach ($route['middleware'] ?? [] as $middlewareName) {
                        if (isset($this->middleware[$middlewareName])) {
                            $result = $this->middleware[$middlewareName]($reqRoute, $reqMet);
                            if ($result === false) return;
                        }
                    }
                    
                    // NEW: Pass params to controller
                    return $this->callController(
                        $route['className'], 
                        $route['methodName'], 
                        $reqRoute, 
                        $reqMet,
                        $params  // NEW: Pass extracted parameters
                    );
                }
            );
        } else {
            // Execute middleware for 404 as well
            return $this->middlewareManager->execute(
                $reqRoute,
                $request,
                function ($request) use ($reqRoute) {
                    return $this->handle404($reqRoute);
                }
            );
        }
    }

    /**
     * MODIFIED: Controller calling with parameter support
     */
    private function callController($className, $methodName, $reqRoute, $reqMet, $params = [])
    {
        $this->beforeMiddleware();
        
        // NEW: Store params in $_GET for backward compatibility
        // This allows controllers to access parameters via $_GET['id'] etc.
        foreach ($params as $key => $value) {
            if (is_string($key)) {
                $_GET[$key] = $value;  // Named params like {id}
            }
        }
        
        $controller = new $className();
        
        // Simple method call - controllers access $_GET/$_POST traditionally
        // No parameter injection complexity
        $controller->$methodName($reqRoute, $reqMet);
        
        $this->afterMiddleware();
    }

    private function beforeMiddleware()
    {
        // Implement your before middleware logic here
    }

    private function afterMiddleware()
    {
        // Implement your after middleware logic here
    }

    private function handle404($reqRoute)
    {
        ?>
        <meta http-equiv="refresh" content="3; URL='<?php echo BASE_URL ?>'" />
        <?php
        include_once './common/404.php';
    }
}
