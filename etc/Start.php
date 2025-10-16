<?php
/*
 *   Pure PHP Start.php for upMVC
 *   Simple, efficient, no container complexity
 */

namespace upMVC;

use upMVC\Config\ConfigManager;
use upMVC\Config\Environment;
use upMVC\Exceptions\ErrorHandler;
use upMVC\Middleware\AuthMiddleware;
use upMVC\Middleware\LoggingMiddleware;
use upMVC\Middleware\CorsMiddleware;

class Start
{
    private $reqURI;      // Store for reuse
    private $reqMethod;   // Store for reuse
    private $reqRoute;    // Store for reuse

    public function __construct()
    {
        $this->bootstrapApplication();
        $this->initializeRequest();  // Get request data once
    }

    /**
     * Initialize request data once - pure PHP, no container
     */
    private function initializeRequest(): void
    {
        $this->reqURI = $_SERVER['REQUEST_URI'];
        $this->reqMethod = $_SERVER['REQUEST_METHOD'];
        
        // Simple direct instantiation
        $config = new Config();
        $this->reqRoute = $config->getReqRoute($this->reqURI);
    }

    public function upMVC()
    {
        try {
            // Pure PHP - simple and direct
            $router = new Router();

            // Setup middleware
            $this->setupEnhancedMiddleware($router);
            $this->registerMiddleware($router);

            // Use already processed request data (no duplication)
            $initRoutes = new Routes($router);
            $initRoutes->startRoutes($this->reqRoute, $this->reqMethod, $this->getRequestURI());

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Getters for request data (if needed elsewhere)
     */
    public function getRequestURI(): string { return $this->reqURI; }
    public function getRequestMethod(): string { return $this->reqMethod; }
    public function getRequestRoute(): string { return $this->reqRoute; }

    private function bootstrapApplication(): void
    {
        ConfigManager::load();

        $errorHandler = new ErrorHandler(
            Environment::isDevelopment(),
            'logs/errors.log'
        );
        $errorHandler->register();

        try {
            ConfigManager::validate();
        } catch (\Exception $e) {
            error_log("Configuration validation warning: " . $e->getMessage());
        }
    }

    private function setupEnhancedMiddleware($router): void
    {
        $middlewareManager = $router->getMiddlewareManager();

        $middlewareManager->addGlobal(new LoggingMiddleware());
        
        if (ConfigManager::get('app.cors.enabled', false)) {
            $corsConfig = ConfigManager::get('app.cors', []);
            $middlewareManager->addGlobal(new CorsMiddleware($corsConfig));
        }

        // Simplified auth - use $_SESSION['logged'] directly
        $protectedRoutes = ['/dashboard/*', '/admin/*', '/users/*', '/moda'];
        $middlewareManager->addGlobal(new AuthMiddleware($protectedRoutes));
    }
    
    private function registerMiddleware($router): void
    {
        // Simplified CSRF - use $_POST directly
        $router->addMiddleware('csrf', function($route, $method) {
            if ($method === 'POST' && ConfigManager::get('security.csrf_protection', true)) {
                $token = $_POST['csrf_token'] ?? '';
                if (!Security::validateCsrf($token)) {
                    http_response_code(403);
                    echo 'CSRF token validation failed';
                    return false;
                }
            }
            return true;
        });
        
        // Simplified rate limiting
        $router->addMiddleware('rate_limit', function($route, $method) {
            $identifier = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $limit = ConfigManager::get('security.rate_limit', 100);
            
            if (!Security::rateLimit($identifier, $limit)) {
                http_response_code(429);
                echo 'Rate limit exceeded';
                return false;
            }
            return true;
        });
        
        // Simplified auth - use existing session variable
        $router->addMiddleware('auth', function($route, $method) {
            if (!isset($_SESSION['logged']) || $_SESSION['logged'] !== true) {
                http_response_code(401);
                header('Location: /auth');
                return false;
            }
            return true;
        });
    }
}