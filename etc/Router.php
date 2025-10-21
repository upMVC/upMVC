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
        include_once './common/404.php';
    }
}