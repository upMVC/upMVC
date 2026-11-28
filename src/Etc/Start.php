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

namespace App\Etc;

use App\Etc\Config\ConfigManager;
use App\Etc\Config\Environment;
use App\Etc\Exceptions\ErrorHandler;
use App\Etc\Middleware\AuthMiddleware;
use App\Etc\Middleware\LoggingMiddleware;
use App\Etc\Middleware\CorsMiddleware;
use App\Etc\Middleware\JwtAuthMiddleware;
use App\Etc\Middleware\SecurityHeadersMiddleware;
use App\Etc\Helpers\HelperFacade;

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
     * Kernel defaults for session AuthMiddleware patterns.
     *
     * Thin Core: empty. Set PROTECTED_ROUTES in .env for app areas, or register
     * via Application::addProtectedRoutes() from a pack/provider.
     * Demos zip examples (put in .env, do not re-add here):
     *   /dashboardexample/*, /admin/*, /users/*, /moda
     *
     * @var array<int, string>
     */
    private static $defaultProtectedRoutes = [];

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
            filter_var(Environment::get('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN),
            $this->resolveLogPath()
        );
        $errorHandler->register();

        try {
            ConfigManager::validate();
        } catch (\Exception $e) {
            error_log("Configuration validation warning: " . $e->getMessage());
        }
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
        $router = new Router();

        // Initialize HelperFacade with router instance (PSR-4 autoloaded)
        HelperFacade::setRouter($router);

        // Allow application packages to register module paths and route policy.
        $app = Application::getInstance();
        $app->registerProviders();

        // Setup middleware stack
        $this->setupEnhancedMiddleware($router);
        $this->registerMiddleware($router);
        $app->bootProviders($router);

        // Initialize and start routing
        $initRoutes = new Routes($router);
        $initRoutes->startRoutes($this->reqRoute, $this->reqMethod, $this->reqURI);
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
        $middlewareManager->addGlobal(new SecurityHeadersMiddleware());

        // Optional CORS support
        if (ConfigManager::get('app.cors.enabled', false)) {
            $corsConfig = $this->getCorsConfig();
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
        // CSRF for mutating methods (POST body or X-CSRF-Token header — never query)
        $router->addMiddleware('csrf', function($route, $method) {
            $mutating = in_array(strtoupper((string) $method), ['POST', 'PUT', 'PATCH', 'DELETE'], true);
            if ($mutating && ConfigManager::get('security.csrf_protection', true)) {
                $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
                if (!Security::validateCsrf((string) $token)) {
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

        // Route-level CORS support for routes declaring ['cors'].
        $router->addMiddleware('cors', function($route, $method) {
            $cors = new CorsMiddleware($this->getCorsConfig());
            $request = [
                'route' => $route,
                'method' => $method,
                'uri' => $_SERVER['REQUEST_URI'] ?? null,
            ];

            $result = $cors->handle($request, fn() => true);
            return $result !== false;
        });

        // JWT bearer token verification — sets $GLOBALS['current_user']
        $router->addMiddleware('jwt', new JwtAuthMiddleware());

        // Session-based authentication (same flags as AuthMiddleware)
        $router->addMiddleware('auth', function($route, $method) {
            $ok = (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true)
                || (isset($_SESSION['logged']) && $_SESSION['logged'] === true);
            if ($ok) {
                return true;
            }

            $wantsJson = str_contains((string) ($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json')
                || str_starts_with((string) ($route ?? ''), '/api');

            if ($wantsJson) {
                http_response_code(401);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['error' => 'Unauthenticated']);
                return false;
            }

            $baseUrl = defined('BASE_URL') ? BASE_URL : '';
            http_response_code(302);
            header('Location: ' . $baseUrl . '/auth');
            return false;
        });
    }

    // ========================================
    // Helper Methods
    // ========================================

    /**
     * Resolve the error log file path from LOG_PATH in .env or fall back to src/logs/errors.log.
     * Supports absolute paths (Windows and Unix) and relative paths from app root.
     */
    private function resolveLogPath(): string
    {
        $appRoot  = Application::getInstance()->getAppRoot();
        $envPath  = Environment::get('LOG_PATH', '');

        if ($envPath === '') {
            return $appRoot . '/src/logs/errors.log';
        }

        $isAbs = str_starts_with($envPath, '/') || (bool) preg_match('/^[A-Za-z]:[\\\\\\/]/', $envPath);
        $dir   = $isAbs ? $envPath : $appRoot . '/' . ltrim($envPath, '\\/');

        return rtrim($dir, '\\/') . '/errors.log';
    }

    /**
     * Resolve session-protected route patterns for AuthMiddleware.
     *
     * Non-empty PROTECTED_ROUTES replaces kernel defaults (then unions provider
     * routes). Empty env keeps defaults (Thin Core: []) then unions providers.
     *
     * @param string             $envRoutes       Raw PROTECTED_ROUTES value
     * @param array<int, string> $defaults        Kernel defaults
     * @param array<int, string> $fromApplication From Application::addProtectedRoutes()
     * @return array<int, string>
     */
    public static function resolveProtectedRouteList(
        string $envRoutes,
        array $defaults,
        array $fromApplication
    ): array {
        $envRoutes = trim($envRoutes);
        if ($envRoutes !== '') {
            $fromEnv = array_values(array_filter(
                array_map('trim', explode(',', $envRoutes)),
                static fn(string $pattern): bool => $pattern !== ''
            ));
            $base = $fromEnv;
        } else {
            $base = $defaults;
        }

        return array_values(array_unique(array_merge($base, $fromApplication)));
    }

    /**
     * Get protected routes from .env / defaults / Application providers.
     *
     * @return array<int, string>
     */
    private function getProtectedRoutes(): array
    {
        return self::resolveProtectedRouteList(
            (string) Environment::get('PROTECTED_ROUTES', ''),
            self::$defaultProtectedRoutes,
            Application::getInstance()->getProtectedRoutes()
        );
    }

    /**
     * Normalize CORS config keys for CorsMiddleware.
     *
     * @return array
     */
    private function getCorsConfig(): array
    {
        $config = ConfigManager::get('app.cors', []);

        $origins = $config['origins'] ?? $config['allowed_origins'] ?? ['*'];
        if (is_string($origins)) {
            $origins = array_map('trim', explode(',', $origins));
        } elseif (is_array($origins)) {
            $origins = array_values(array_map(
                static fn($o) => is_string($o) ? trim($o) : $o,
                $origins
            ));
        } else {
            $origins = ['*'];
        }

        $headers = $config['headers'] ?? $config['allowed_headers'] ?? ['Content-Type', 'Authorization', 'X-Requested-With', 'X-CSRF-Token'];
        if (is_string($headers)) {
            $headers = array_map('trim', explode(',', $headers));
        }

        return [
            'origins' => $origins,
            'methods' => $config['methods'] ?? $config['allowed_methods'] ?? ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
            'headers' => $headers,
            'credentials' => $config['credentials'] ?? false,
            'max_age' => $config['max_age'] ?? 86400,
        ];
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




