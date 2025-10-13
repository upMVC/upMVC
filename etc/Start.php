<?php
/*
 *   Created on Tue Oct 31 2023
 *   Enhanced on October 11, 2025
 *   Copyright (c) 2023-2025 BitsHost
 *   All rights reserved.
 */

namespace upMVC;

use upMVC\Config\ConfigManager;
use upMVC\Config\Environment;
use upMVC\Container\Container;
use upMVC\Exceptions\ErrorHandler;
use upMVC\Exceptions\ConfigurationException;
use upMVC\Middleware\AuthMiddleware;
use upMVC\Middleware\LoggingMiddleware;
use upMVC\Middleware\CorsMiddleware;

class Start
{
    /**
     * @var Container
     */
    private Container $container;

    public function __construct()
    {
        $this->container = new Container();
        $this->bootstrapApplication();
    }

    public function upMVC()
    {
        try {
            // Initialize core services
            $router = $this->container->make(Router::class);
            $config = $this->container->make(Config::class);

            // Setup middleware (both new and legacy)
            $this->setupEnhancedMiddleware($router);
            $this->registerMiddleware($router); // Keep legacy middleware for backward compatibility

            // Get request data
            $reqURI = $_SERVER['REQUEST_URI'];
            $reqMet = $_SERVER['REQUEST_METHOD'];

            // Process request
            $reqRoute = $config->getReqRoute($reqURI);

            // Initialize and start routing
            $initRoutes = new Routes($router);
            $initRoutes->startRoutes($reqRoute, $reqMet);

        } catch (\Exception $e) {
            // Error handling is managed by ErrorHandler
            throw $e;
        }
    }

    /**
     * Bootstrap the application
     *
     * @return void
     */
    private function bootstrapApplication(): void
    {
        // Load configuration
        ConfigManager::load();

        // Setup error handling
        $errorHandler = new ErrorHandler(
            Environment::isDevelopment(),
            'logs/errors.log'
        );
        $errorHandler->register();

        // Register core services in container
        $this->registerCoreServices();

        // Validate configuration
        try {
            ConfigManager::validate();
        } catch (\Exception $e) {
            // Log validation error but continue with defaults
            error_log("Configuration validation warning: " . $e->getMessage());
        }
    }

    /**
     * Register core services in the dependency injection container
     *
     * @return void
     */
    private function registerCoreServices(): void
    {
        // Register container instance
        $this->container->instance(Container::class, $this->container);

        // Register router as singleton
        $this->container->singleton(Router::class);

        // Register config as singleton
        $this->container->singleton(Config::class);

        // Register database as singleton
        $this->container->singleton(Database::class);
    }

    /**
     * Setup enhanced middleware for the router
     *
     * @param Router $router
     * @return void
     */
    private function setupEnhancedMiddleware(Router $router): void
    {
        $middlewareManager = $router->getMiddlewareManager();

        // Add global middleware
        $middlewareManager->addGlobal(new LoggingMiddleware());
        
        // Add CORS middleware if enabled
        if (ConfigManager::get('app.cors.enabled', false)) {
            $corsConfig = ConfigManager::get('app.cors', []);
            $middlewareManager->addGlobal(new CorsMiddleware($corsConfig));
        }

        // Add authentication middleware for protected routes
        $protectedRoutes = ['/dashboard/*', '/admin/*', '/users/*'];
        $middlewareManager->addGlobal(new AuthMiddleware($protectedRoutes));
    }
    
    /**
     * Legacy middleware registration (for backward compatibility)
     *
     * @param Router $router
     * @return void
     */
    private function registerMiddleware(Router $router): void
    {
        // CSRF Protection Middleware
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
        
        // Rate Limiting Middleware
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
        
        // Auth Middleware
        $router->addMiddleware('auth', function($route, $method) {
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                header('Location: /login');
                return false;
            }
            return true;
        });
    }

    /**
     * Get the container instance
     *
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }
}
