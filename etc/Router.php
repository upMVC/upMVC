<?php
/*
 *   Simplified Router for upMVC
 *   Removes unnecessary complexity while maintaining functionality
 */

namespace upMVC;

use upMVC\Middleware\MiddlewareManager;

/**
 * Simplified Router
 * - No route parameters (use exact routes from database)
 * - No GET/POST wrappers (use $_GET/$_POST directly)
 * - Reuse REQUEST_URI/METHOD from Start.php
 */
class Router
{
    protected $routes = [];
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
     * Add route - simple exact matching
     */
    public function addRoute($route, $className, $methodName, array $middleware = [])
    {
        $this->routes[$route] = [
            'className' => $className, 
            'methodName' => $methodName,
            'middleware' => $middleware
        ];
    }
    
    public function addMiddleware(string $name, callable $middleware): void
    {
        $this->middleware[$name] = $middleware;
    }

    /**
     * Simplified dispatcher - uses your existing variables from Start.php
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

        // Simple exact route matching using your $reqRoute
        if (isset($this->routes[$reqRoute])) {
            $route = $this->routes[$reqRoute];
            
            // Execute middleware pipeline
            return $this->middlewareManager->execute(
                $reqRoute,
                $request,
                function ($request) use ($route, $reqRoute, $reqMet) {
                    // Execute legacy middleware for backward compatibility
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
     * Simple controller calling - controllers use $_GET/$_POST directly
     */
    private function callController($className, $methodName, $reqRoute, $reqMet)
    {
        $this->beforeMiddleware();
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