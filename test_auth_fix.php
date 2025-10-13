<?php
require_once 'vendor/autoload.php';

// Simulate a proper web request environment
$_SERVER['REQUEST_URI'] = '/upMVC/dashboard';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/upMVC/index.php';

echo "<h2>Testing Dashboard Middleware Fix</h2>";

echo "<h3>1. Testing Router Setup...</h3>";

try {
    // Initialize config first
    $config = new upMVC\Config();
    
    // Initialize router
    $router = new upMVC\Router();
    
    // Apply the modified middleware setup (without dashboard in protected routes)
    $start = new upMVC\Start();
    
    // Use reflection to access the private method
    $reflection = new ReflectionClass($start);
    $middlewareMethod = $reflection->getMethod('setupEnhancedMiddleware');
    $middlewareMethod->setAccessible(true);
    $middlewareMethod->invoke($start, $router);
    
    echo "✓ Middleware setup completed<br>";
    
    // Test the middleware manager
    $middlewareManager = $router->getMiddlewareManager();
    
    // Check what middleware is registered
    $middlewareReflection = new ReflectionClass($middlewareManager);
    $globalMiddleware = $middlewareReflection->getProperty('globalMiddleware');
    $globalMiddleware->setAccessible(true);
    $middleware = $globalMiddleware->getValue($middlewareManager);
    
    echo "Registered middleware count: " . count($middleware) . "<br>";
    
    foreach ($middleware as $i => $mw) {
        echo "- " . get_class($mw);
        if ($mw instanceof upMVC\Middleware\AuthMiddleware) {
            echo " (Auth middleware - checking protected routes)";
            
            // Check what routes are protected
            $authReflection = new ReflectionClass($mw);
            $protectedRoutes = $authReflection->getProperty('protectedRoutes');
            $protectedRoutes->setAccessible(true);
            $routes = $protectedRoutes->getValue($mw);
            
            echo "<br>  Protected routes: " . implode(', ', $routes);
            
            if (in_array('/dashboard/*', $routes)) {
                echo " ❌ Dashboard still protected!";
            } else {
                echo " ✅ Dashboard excluded from protection!";
            }
        }
        echo "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
}