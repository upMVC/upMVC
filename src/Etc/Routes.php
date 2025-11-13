<?php
/**
 * Routes.php - Application Route Registration
 * 
 * This class manages route registration and dispatching for upMVC:
 * - Registers system-level routes
 * - Loads and registers module routes dynamically
 * - Dispatches requests to the router
 * 
 * Route Registration Flow:
 * 1. System routes (hardcoded examples/defaults)
 * 2. Module routes (loaded from modules via InitModsImproved)
 * 3. Dispatch to router for middleware and controller execution
 * 
 * Custom Routes Example:
 * You can inject controllers from any module using namespace aliases
 * to prevent naming conflicts between modules.
 * 
 * @package upMVC
 * @author BitsHost
 * @copyright 2023 BitsHost
 * @license MIT License
 * @link https://bitshost.biz/
 * @created Tue Oct 31 2023
 */

namespace App\Etc;

// Example: Custom route imports with aliases to prevent naming conflicts
// Use 'Controller as Alias' syntax when multiple modules have same class names
use Test\Controller;
use Admin\Controller as AnythingElse;

use App\Etc\InitMods;
use App\Etc\InitModsImproved;
use App\Etc\Router;

class Routes
{
    // ========================================
    // Properties
    // ========================================
    
    /**
     * Router instance for route registration and dispatching
     * 
     * @var Router
     */
    private $router;

    // ========================================
    // Initialization
    // ========================================

    /**
     * Constructor - Inject router dependency
     * 
     * @param Router $router The router instance to use for route management
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    // ========================================
    // Route Management
    // ========================================

    /**
     * Start routing process - Register and dispatch
     * 
     * This is the main entry point called from Start.php.
     * It performs two steps:
     * 1. Register all routes (system + module routes)
     * 2. Dispatch the current request to appropriate controller
     *
     * @param string $reqRoute Clean route from Config::getReqRoute()
     * @param string $reqMet HTTP method from $_SERVER['REQUEST_METHOD']
     * @param string|null $reqURI Original URI with query parameters (for middleware)
     * @return void
     */
    public function startRoutes(string $reqRoute, string $reqMet, ?string $reqURI = null): void
    {
        $this->registerRoutes();
        $this->dispatchRoute($reqRoute, $reqMet, $reqURI);
    }

    // ========================================
    // Route Registration
    // ========================================

    /**
     * Register all application routes
     * 
     * Registers routes in this order:
     * 1. System routes (hardcoded examples/defaults)
     * 2. Module routes (dynamically loaded from modules)
     * 
     * Add your custom routes here or use module-based registration.
     *
     * @return void
     */
    private function registerRoutes(): void
    {
        // Example: Register system-level routes
        $this->router->addRoute('/abba', Controller::class, 'display');
        $this->router->addRoute('/abbac', AnythingElse::class, 'display');
        
        // Example: Add home route (uncomment and adjust as needed)
        // $this->router->addRoute('/', \New\Controller::class, 'display');

        // Load and register module routes dynamically
        // Note: InitModsImproved is the current approach (InitMods is legacy)
        $modulesRoutes = new InitModsImproved();
        $modulesRoutes->addRoutes($this->router);
    }

    // ========================================
    // Request Dispatching
    // ========================================

    /**
     * Dispatch request to router
     * 
     * Passes the request to the router's dispatcher which will:
     * 1. Execute middleware pipeline
     * 2. Call the appropriate controller
     * 3. Handle 404 if route not found
     *
     * @param string $reqRoute Clean route path
     * @param string $reqMet HTTP method
     * @param string|null $reqURI Original URI for middleware logging
     * @return void
     */
    private function dispatchRoute(string $reqRoute, string $reqMet, ?string $reqURI = null): void
    {
        $this->router->dispatcher($reqRoute, $reqMet, $reqURI);
    }
}





