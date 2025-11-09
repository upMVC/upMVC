<?php
/**
 * Router.php - Simplified HTTP Request Router
 * 
 * This class provides streamlined routing functionality for upMVC:
 * - Exact route matching (no complex regex patterns)
 * - Middleware pipeline execution
 * - Controller dispatching
 * - 404 error handling
 * 
 * Design Philosophy:
 * - Uses exact routes from database (no route parameters)
 * - Controllers access $_GET/$_POST directly (no wrappers)
 * - Reuses REQUEST_URI/METHOD from Start.php (no duplication)
 * - Simple, predictable, fast
 * 
 * @package upMVC
 * @author BitsHost
 * @copyright 2023 BitsHost
 * @license MIT License
 * @link https://bitshost.biz/
 */

namespace upMVC;

use upMVC\Middleware\MiddlewareManager;

class Router
{
    // ========================================
    // Properties
    // ========================================
    
    /**
     * Registered routes array
     * 
     * Format: ['/route' => ['className' => ..., 'methodName' => ..., 'middleware' => [...]]]
     * 
     * @var array
     */
    protected $routes = [];
    
    /**
     * Registered parameterized routes
     * 
     * Each item structure:
     * [
     *   'pattern' => '/users/{id}',
     *   'segments' => ['users','{id}'],
     *   'params' => ['id'],
     *   'className' => Controller::class,
     *   'methodName' => 'show',
     *   'middleware' => []
     * ]
     * 
     * @var array
     */
    protected $paramRoutes = [];
    
    /**
     * Middleware manager for global middleware pipeline
     * 
     * @var MiddlewareManager
     */
    private MiddlewareManager $middlewareManager;
    
    /**
     * Named middleware for route-specific execution
     * 
     * Format: ['csrf' => callable, 'auth' => callable, ...]
     * 
     * @var array
     */
    protected $middleware = [];

    // ========================================
    // Initialization
    // ========================================

    /**
     * Constructor - Initialize middleware manager
     */
    public function __construct()
    {
        $this->middlewareManager = new MiddlewareManager();
    }

    /**
     * Get the middleware manager instance
     * 
     * @return MiddlewareManager The middleware manager
     */
    public function getMiddlewareManager(): MiddlewareManager
    {
        return $this->middlewareManager;
    }

    // ========================================
    // Route Registration
    // ========================================

    /**
     * Add a route with exact matching
     * 
     * Registers a route that maps to a specific controller and method.
     * Optionally attach route-specific middleware.
     * 
     * @param string $route The exact route path (e.g., '/users', '/dashboard')
     * @param string $className Fully qualified controller class name
     * @param string $methodName Controller method to call
     * @param array $middleware Array of middleware names to execute for this route
     * @return void
     * 
     * @example
     * $router->addRoute('/users', 'App\Controllers\UserController', 'index', ['auth']);
     */
    public function addRoute($route, $className, $methodName, array $middleware = [])
    {
        $this->routes[$route] = [
            'className' => $className, 
            'methodName' => $methodName,
            'middleware' => $middleware
        ];
    }
    
    /**
     * Add a parameterized route with simple placeholders
     * 
     * Example: /users/{id}, /orders/{orderId}/items/{itemId}
     * Placeholders are names enclosed in curly braces.
     * 
     * @param string $pattern Route pattern with placeholders
     * @param string $className Controller class
     * @param string $methodName Controller method
     * @param array $middleware Optional named middleware list
     * @return void
     */
    public function addParamRoute(string $pattern, string $className, string $methodName, array $middleware = []): void
    {
        $trimmed = trim($pattern, '/');
        $segments = $trimmed === '' ? [] : explode('/', $trimmed);
        $params = [];
        foreach ($segments as $seg) {
            if (preg_match('/^{([a-zA-Z_][a-zA-Z0-9_]*)}$/', $seg, $m)) {
                $params[] = $m[1];
            }
        }
        $this->paramRoutes[] = [
            'pattern' => $pattern,
            'segments' => $segments,
            'params' => $params,
            'className' => $className,
            'methodName' => $methodName,
            'middleware' => $middleware,
        ];
    }
    
    /**
     * Register a named middleware
     * 
     * Adds a middleware callable that can be referenced by name in routes.
     * 
     * @param string $name Middleware identifier (e.g., 'auth', 'csrf')
     * @param callable $middleware Middleware callable function
     * @return void
     * 
     * @example
     * $router->addMiddleware('auth', function($route, $method) {
     *     return isset($_SESSION['logged']);
     * });
     */
    public function addMiddleware(string $name, callable $middleware): void
    {
        $this->middleware[$name] = $middleware;
    }

    // ========================================
    // Request Dispatching
    // ========================================

    /**
     * Dispatch request to appropriate controller or 404
     * 
     * This is the main routing method that:
     * 1. Creates request context from provided variables
     * 2. Checks if route exists in registered routes
     * 3. Executes middleware pipeline
     * 4. Calls controller method or handles 404
     * 
     * @param string $reqRoute Clean route from getReqRoute() (e.g., '/users')
     * @param string $reqMet HTTP method from $_SERVER['REQUEST_METHOD']
     * @param string|null $reqURI Original URI with query parameters (for middleware logging)
     * @return mixed Controller response or 404 page
     * 
     * @example
     * $router->dispatcher('/users', 'GET', '/users?page=1');
     */
    public function dispatcher($reqRoute, $reqMet, ?string $reqURI = null)
    {
        // Build request context for middleware
        $request = [
            'route' => $reqRoute,
            'method' => $reqMet,
            'uri' => $reqURI,
            'timestamp' => time()
        ];

        // Check if route exists
        if (isset($this->routes[$reqRoute])) {
            $route = $this->routes[$reqRoute];
            
            // Execute global middleware pipeline, then route handler
            return $this->middlewareManager->execute(
                $reqRoute,
                $request,
                function ($request) use ($route, $reqRoute, $reqMet) {
                    // Execute route-specific middleware for backward compatibility
                    foreach ($route['middleware'] ?? [] as $middlewareName) {
                        if (isset($this->middleware[$middlewareName])) {
                            $result = $this->middleware[$middlewareName]($reqRoute, $reqMet);
                            if ($result === false) return;
                        }
                    }
                    
                    return $this->callController($route['className'], $route['methodName'], $reqRoute, $reqMet);
                }
            );
        } else {
            // Try parameterized routes if no exact match was found
            $match = $this->matchParamRoute($reqRoute);
            if ($match !== null) {
                $route = $match['route'];
                $params = $match['params'];

                // Enrich request context with extracted params
                $request['params'] = $params;

                return $this->middlewareManager->execute(
                    $reqRoute,
                    $request,
                    function ($request) use ($route, $reqRoute, $reqMet, $params) {
                        // Execute route-specific named middleware
                        foreach ($route['middleware'] ?? [] as $middlewareName) {
                            if (isset($this->middleware[$middlewareName])) {
                                $result = $this->middleware[$middlewareName]($reqRoute, $reqMet);
                                if ($result === false) return;
                            }
                        }

                        // Inject params into $_GET non-destructively
                        foreach ($params as $k => $v) {
                            if (!array_key_exists($k, $_GET)) {
                                $_GET[$k] = $v;
                            }
                        }

                        return $this->callController($route['className'], $route['methodName'], $reqRoute, $reqMet);
                    }
                );
            }

            // Route not found - execute middleware for 404 as well
            return $this->middlewareManager->execute(
                $reqRoute,
                $request,
                function ($request) use ($reqRoute) {
                    return $this->handle404($reqRoute);
                }
            );
        }
    }
    
    // ========================================
    // Parameterized Route Matching
    // ========================================
    
    /**
     * Attempt to match a request route against parameterized routes
     * 
     * @param string $reqRoute The requested route (e.g., '/users/123')
     * @return array|null ['route' => routeDefArray, 'params' => ['id' => '123']] or null
     */
    private function matchParamRoute(string $reqRoute): ?array
    {
        $path = trim($reqRoute, '/');
        $reqSegments = $path === '' ? [] : explode('/', $path);
        $reqCount = count($reqSegments);

        foreach ($this->paramRoutes as $route) {
            $patSegments = $route['segments'];
            if (count($patSegments) !== $reqCount) {
                continue; // simple length check for MVP
            }

            $captured = [];
            $ok = true;
            foreach ($patSegments as $i => $seg) {
                $reqSeg = $reqSegments[$i];
                if (preg_match('/^{([a-zA-Z_][a-zA-Z0-9_]*)}$/', $seg, $m)) {
                    $captured[$m[1]] = $reqSeg;
                } else {
                    if ($seg !== $reqSeg) { $ok = false; break; }
                }
            }

            if ($ok) {
                return ['route' => $route, 'params' => $captured];
            }
        }
        return null;
    }

    // ========================================
    // Controller Execution
    // ========================================

    /**
     * Instantiate and call controller method
     * 
     * Creates controller instance and calls the specified method.
     * Controllers access $_GET/$_POST directly (no parameter injection).
     * Executes before/after middleware hooks.
     * 
     * @param string $className Fully qualified controller class name
     * @param string $methodName Controller method to call
     * @param string $reqRoute Clean route path
     * @param string $reqMet HTTP method
     * @return void
     */
    private function callController($className, $methodName, $reqRoute, $reqMet)
    {
        $this->beforeMiddleware();
        
        $controller = new $className();
        $controller->$methodName($reqRoute, $reqMet);
        
        $this->afterMiddleware();
    }

    // ========================================
    // Middleware Hooks
    // ========================================

    /**
     * Execute before controller middleware
     * 
     * Hook for executing logic before controller method.
     * Currently placeholder for future enhancements.
     * 
     * @return void
     */
    private function beforeMiddleware()
    {
        // Placeholder for before middleware logic
    }

    /**
     * Execute after controller middleware
     * 
     * Hook for executing logic after controller method.
     * Currently placeholder for future enhancements.
     * 
     * @return void
     */
    private function afterMiddleware()
    {
        // Placeholder for after middleware logic
    }

    // ========================================
    // Error Handling
    // ========================================

    /**
     * Handle 404 Not Found error
     * 
     * Displays 404 error page and sets up auto-redirect to home page.
     * 
     * @param string $reqRoute The requested route that was not found
     * @return void
     */
    private function handle404($reqRoute)
    {
        ?>
        <meta http-equiv="refresh" content="3; URL='<?php echo BASE_URL ?>'" />
        <?php
        include_once __DIR__ . '/../common/404.php';
    }
}