<?php
/*
 *   Created on Tue Oct 31 2023
 *   Enhanced on October 11, 2025
 *   Copyright (c) 2023-2025 BitsHost
 *   All rights reserved.
 *
 *   Permission is hereby granted, free of charge, to any person obtaining a copy
 *   of this software and associated documentation files (the "Software"), to deal
 *   in the Software without restriction, including without limitation the rights
 *   to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *   copies of the Software, and to permit persons to whom the Software is
 *   furnished to do so, subject to the following conditions:
 *
 *   The above copyright notice and this permission notice shall be included in all
 *   copies or substantial portions of the Software.
 *
 *   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *   IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *   FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *   AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *   LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *   OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 *   SOFTWARE.
 *   Here you may host your app for free:
 *   https://bitshost.biz/
 */

namespace upMVC;

use upMVC\Middleware\MiddlewareManager;

/**
 * Router
 * Enhanced with middleware support
 */
class Router
{
    protected $routes = [];
    
    /**
     * @var MiddlewareManager
     */
    private MiddlewareManager $middlewareManager;

    public function __construct()
    {
        $this->middlewareManager = new MiddlewareManager();
    }

    /**
     * Get middleware manager
     *
     * @return MiddlewareManager
     */
    public function getMiddlewareManager(): MiddlewareManager
    {
        return $this->middlewareManager;
    }
    protected $middleware = [];
    protected $routeParams = [];

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

    public function dispatcher($reqRoute, $reqMet)
    {
        // Create request context
        $request = [
            'route' => $reqRoute,
            'method' => $reqMet,
            'uri' => $_SERVER['REQUEST_URI'] ?? '',
            'query_params' => $_GET,
            'post_params' => $_POST,
            'headers' => getallheaders() ?: [],
            'timestamp' => time()
        ];

        $matchedRoute = $this->matchRoute($reqRoute);
        
        if ($matchedRoute) {
            $route = $this->routes[$matchedRoute['route']];
            $this->routeParams = $matchedRoute['params'];
            
            // Add matched parameters to request
            $request['params'] = $matchedRoute['params'];
            
            // Execute enhanced middleware pipeline
            return $this->middlewareManager->execute(
                $matchedRoute['route'],
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
    
    private function matchRoute(string $reqRoute): ?array
    {
        // Exact match first
        if (isset($this->routes[$reqRoute])) {
            return ['route' => $reqRoute, 'params' => []];
        }
        
        // Pattern matching for dynamic routes
        foreach ($this->routes as $route => $config) {
            $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $route);
            $pattern = '#^' . $pattern . '$#';
            
            if (preg_match($pattern, $reqRoute, $matches)) {
                array_shift($matches); // Remove full match
                
                // Extract parameter names
                preg_match_all('/\{([^}]+)\}/', $route, $paramNames);
                $params = array_combine($paramNames[1] ?? [], $matches);
                
                return ['route' => $route, 'params' => $params];
            }
        }
        
        return null;
    }
    
    public function getRouteParams(): array
    {
        return $this->routeParams;
    }

    private function callController($className, $methodName, $reqRoute, $reqMet)
    {
        $this->beforeMiddleware();
        $controller = new $className();
        
        // Inject route parameters if controller supports it
        if (method_exists($controller, 'setRouteParams')) {
            $controller->setRouteParams($this->routeParams);
        }
        
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
