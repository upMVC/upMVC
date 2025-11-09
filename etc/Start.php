<?php
/**
 * Start.php - Application Bootstrap and Initialization
 * 
 * This class handles the complete application startup sequence including:
 * - Configuration loading and validation
 * - Error handling setup
 * - Request initialization
 * - Middleware registration
 * - Routing setup
 * 
 * Pure PHP implementation - simple, efficient, no container complexity
 * 
 * @package upMVC
 * @author BitsHost
 * @copyright 2023 BitsHost
 * @license MIT License
 * @link https://bitshost.biz/
 * @created Tue Oct 31 2023
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
    // ========================================
    // Properties
    // ========================================
    
    /** @var string Cached request URI */
    private $reqURI;
    
    /** @var string Cached request method (GET, POST, etc.) */
    private $reqMethod;
    
    /** @var string Parsed route from URI */
    private $reqRoute;
    
    /**
     * Default protected routes requiring authentication
     * 
     * These routes require user authentication before access.
     * Can be overridden via PROTECTED_ROUTES in .env (comma-separated list)
     * 
     * IMPORTANT: Change these according to your application's protected areas!
     * 
     * @var array
     */
    private static $defaultProtectedRoutes = [
        '/dashboardexample/*',
        '/admin/*',
        '/users/*',
        '/moda'
    ];

    // ========================================
    // Initialization
    // ========================================

    /**
     * Constructor - Bootstrap and initialize application
     */
    public function __construct()
    {
        $this->bootstrapApplication();
        $this->initializeRequest();
    }

    /**
     * Bootstrap application: Load config, setup error handling, validate
     * 
     * @return void
     */
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
        
        // Load global helper functions (both OOP class and procedural functions)
        // File contains: Helpers class + route(), url(), redirect(), etc.
        require_once __DIR__ . '/helpers.php';
    }

    /**
     * Initialize request data once and cache for reuse
     * 
     * @return void
     */
    private function initializeRequest(): void
    {
        $this->reqURI = $_SERVER['REQUEST_URI'];
        $this->reqMethod = $_SERVER['REQUEST_METHOD'];
        
        $config = new Config();
        $this->reqRoute = $config->getReqRoute($this->reqURI);
    }

    // ========================================
    // Core Application Flow
    // ========================================

    /**
     * Main application entry point - Setup routing and middleware
     * 
     * @return void
     * @throws \Exception
     */
    public function upMVC()
    {
        try {
            $router = new Router();
            
            // Initialize Helpers with router instance
            Helpers::setRouter($router);

            // Setup middleware stack
            $this->setupEnhancedMiddleware($router);
            $this->registerMiddleware($router);

            // Initialize and start routing
            $initRoutes = new Routes($router);
            $initRoutes->startRoutes($this->reqRoute, $this->reqMethod, $this->reqURI);

        } catch (\Exception $e) {
            throw $e;
        }
    }

    // ========================================
    // Middleware Setup
    // ========================================

    /**
     * Setup enhanced global middleware (logging, CORS, auth)
     * 
     * @param Router $router The router instance
     * @return void
     */
    private function setupEnhancedMiddleware($router): void
    {
        $middlewareManager = $router->getMiddlewareManager();

        // Global logging for all requests
        $middlewareManager->addGlobal(new LoggingMiddleware());
        
        // Optional CORS support
        if (ConfigManager::get('app.cors.enabled', false)) {
            $corsConfig = ConfigManager::get('app.cors', []);
            $middlewareManager->addGlobal(new CorsMiddleware($corsConfig));
        }

        // Authentication for protected routes
        $protectedRoutes = $this->getProtectedRoutes();
        $middlewareManager->addGlobal(new AuthMiddleware($protectedRoutes));
    }
    
    /**
     * Register named middleware for specific routes
     * 
     * @param Router $router The router instance
     * @return void
     */
    private function registerMiddleware($router): void
    {
        // CSRF protection for POST requests
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
        
        // Rate limiting by IP address
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
        
        // Session-based authentication check
        $router->addMiddleware('auth', function($route, $method) {
            if (!isset($_SESSION['logged']) || $_SESSION['logged'] !== true) {
                http_response_code(401);
                header('Location: /auth');
                return false;
            }
            return true;
        });
    }

    // ========================================
    // Helper Methods
    // ========================================

    /**
     * Get protected routes from .env or use defaults
     * 
     * @return array Array of protected route patterns
     */
    private function getProtectedRoutes(): array
    {
        $envRoutes = Environment::get('PROTECTED_ROUTES', '');
        
        if (!empty($envRoutes)) {
            return array_map('trim', explode(',', $envRoutes));
        }
        
        return self::$defaultProtectedRoutes;
    }

    // ========================================
    // Public Getters
    // ========================================

    /**
     * Get cached request URI
     * 
     * @return string The request URI
     */
    public function getRequestURI(): string 
    { 
        return $this->reqURI; 
    }

    /**
     * Get cached request method
     * 
     * @return string The request method (GET, POST, etc.)
     */
    public function getRequestMethod(): string 
    { 
        return $this->reqMethod; 
    }

    /**
     * Get parsed request route
     * 
     * @return string The parsed route
     */
    public function getRequestRoute(): string 
    { 
        return $this->reqRoute; 
    }
}