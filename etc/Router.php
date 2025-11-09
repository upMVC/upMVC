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
     *   'types' => ['id' => 'int'],
     *   'constraints' => ['id' => '\d+'],
     *   'className' => Controller::class,
     *   'methodName' => 'show',
     *   'middleware' => [],
     *   'name' => 'user.show'
     * ]
     * 
     * @var array
     */
    protected $paramRoutes = [];
    
    /**
     * Parameterized routes grouped by first segment for optimization
     * 
     * Format: ['users' => [route1, route2], 'products' => [route3]]
     * 
     * @var array
     */
    protected $paramRoutesByPrefix = [];
    
    /**
     * Named routes registry for URL generation
     * 
     * Format: ['user.show' => routeData, 'post.edit' => routeData]
     * 
     * @var array
     */
    protected $namedRoutes = [];
    
    /**
     * Last registered route (for chaining)
     * 
     * @var array|null
     */
    protected $lastRoute = null;
    
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
     * Example: /users/{id:int}, /orders/{orderId}/items/{itemId}
     * Placeholders support type hints: {id:int}, {price:float}, {active:bool}
     * 
     * @param string $pattern Route pattern with placeholders
     * @param string $className Controller class
     * @param string $methodName Controller method
     * @param array $middleware Optional named middleware list
     * @param array $constraints Optional regex constraints ['id' => '\d+']
     * @return self For method chaining (->name())
     */
    public function addParamRoute(
        string $pattern, 
        string $className, 
        string $methodName, 
        array $middleware = [],
        array $constraints = []
    ): self
    {
        $trimmed = trim($pattern, '/');
        $segments = $trimmed === '' ? [] : explode('/', $trimmed);
        $params = [];
        $types = [];
        
        // Extract first segment for prefix grouping
        $prefix = $segments[0] ?? '';
        
        // Parse segments for params and type hints
        foreach ($segments as $seg) {
            // Match {name:type} or {name}
            if (preg_match('/^{([a-zA-Z_][a-zA-Z0-9_]*)(?::([a-z]+))?}$/', $seg, $m)) {
                $paramName = $m[1];
                $paramType = $m[2] ?? 'string';
                
                $params[] = $paramName;
                $types[$paramName] = $paramType;
            }
        }
        
        $routeData = [
            'pattern' => $pattern,
            'segments' => $segments,
            'params' => $params,
            'types' => $types,
            'constraints' => $constraints,
            'className' => $className,
            'methodName' => $methodName,
            'middleware' => $middleware,
            'name' => null,  // Set via name() method
        ];
        
        // Store in main array
        $this->paramRoutes[] = $routeData;
        
        // Store in prefix-grouped array for optimization
        if (!isset($this->paramRoutesByPrefix[$prefix])) {
            $this->paramRoutesByPrefix[$prefix] = [];
        }
        $this->paramRoutesByPrefix[$prefix][] = $routeData;
        
        // Keep reference for chaining
        $this->lastRoute = &$this->paramRoutes[count($this->paramRoutes) - 1];
        
        return $this;
    }
    
    /**
     * Assign a name to the last registered parameterized route
     * 
     * Enables URL generation via route() helper
     * 
     * @param string $name Route name (e.g., 'user.show', 'post.edit')
     * @return self For method chaining
     * 
     * @example
     * $router->addParamRoute('/users/{id}', Controller::class, 'show')->name('user.show');
     */
    public function name(string $name): self
    {
        if ($this->lastRoute !== null) {
            $this->lastRoute['name'] = $name;
            $this->namedRoutes[$name] = $this->lastRoute;
        }
        return $this;
    }
    
    /**
     * Generate URL from named route
     * 
     * @param string $name Route name
     * @param array $params Parameters to inject
     * @return string Generated URL
     * @throws \RuntimeException If route not found or missing params
     * 
     * @example
     * $router->route('user.show', ['id' => 123]); // Returns: /users/123
     */
    public function route(string $name, array $params = []): string
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new \RuntimeException("Route '{$name}' not found");
        }
        
        $route = $this->namedRoutes[$name];
        $pattern = $route['pattern'];
        
        // Replace placeholders with values
        foreach ($params as $key => $value) {
            // Match both {key} and {key:type}
            $pattern = preg_replace('/{' . $key . '(?::[a-z]+)?}/', $value, $pattern);
        }
        
        // Check for unreplaced placeholders
        if (preg_match('/{[^}]+}/', $pattern)) {
            throw new \RuntimeException("Missing parameters for route '{$name}'");
        }
        
        return $pattern;
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

                        // Inject params into $_GET with type casting
                        foreach ($params as $k => $v) {
                            if (!array_key_exists($k, $_GET)) {
                                $type = $route['types'][$k] ?? 'string';
                                $_GET[$k] = $this->castParam($v, $type);
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
     * Uses prefix-based optimization for better performance
     * Validates against constraints if provided
     * 
     * @param string $reqRoute The requested route (e.g., '/users/123')
     * @return array|null ['route' => routeDefArray, 'params' => ['id' => '123']] or null
     */
    private function matchParamRoute(string $reqRoute): ?array
    {
        $path = trim($reqRoute, '/');
        $reqSegments = $path === '' ? [] : explode('/', $path);
        $reqCount = count($reqSegments);
        
        // Optimization: Get prefix and check only matching group
        $prefix = $reqSegments[0] ?? '';
        $routesToCheck = $this->paramRoutesByPrefix[$prefix] ?? [];
        
        // Fallback: If no prefix match, check all routes
        if (empty($routesToCheck)) {
            $routesToCheck = $this->paramRoutes;
        }

        foreach ($routesToCheck as $route) {
            $patSegments = $route['segments'];
            if (count($patSegments) !== $reqCount) {
                continue;
            }

            $captured = [];
            $ok = true;
            
            foreach ($patSegments as $i => $seg) {
                $reqSeg = $reqSegments[$i];
                
                // Match {name:type} or {name}
                if (preg_match('/^{([a-zA-Z_][a-zA-Z0-9_]*)(?::[a-z]+)?}$/', $seg, $m)) {
                    $paramName = $m[1];
                    
                    // Validate against constraint if provided
                    if (isset($route['constraints'][$paramName])) {
                        $pattern = $route['constraints'][$paramName];
                        if (!preg_match('/^' . $pattern . '$/', $reqSeg)) {
                            $ok = false;
                            break;
                        }
                    }
                    
                    $captured[$paramName] = $reqSeg;
                } else {
                    if ($seg !== $reqSeg) { 
                        $ok = false; 
                        break; 
                    }
                }
            }

            if ($ok) {
                return ['route' => $route, 'params' => $captured];
            }
        }
        
        return null;
    }
    
    /**
     * Cast parameter value to specified type
     * 
     * @param mixed $value Value to cast
     * @param string $type Target type (int, float, bool, string)
     * @return mixed Casted value
     */
    private function castParam($value, string $type)
    {
        switch ($type) {
            case 'int':
            case 'integer':
                return (int)$value;
                
            case 'float':
            case 'double':
                return (float)$value;
                
            case 'bool':
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
                
            case 'string':
            default:
                return (string)$value;
        }
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