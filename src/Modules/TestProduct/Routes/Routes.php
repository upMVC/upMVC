<?php
namespace App\Modules\TestProduct\Routes;

use App\Modules\TestProduct\Controller;

/**
 * Enhanced App\Modules\TestProduct Routes
 * 
 * Auto-discovered by InitModsImproved.php
 * No manual registration required!
 * 
 * Features:
 * - Automatic route discovery
 * - Submodule support
 * - Caching integration
 * - Environment awareness
 */
class Routes
{
    /**
     * Register routes with the router
     * 
     * This method is automatically called by InitModsImproved.php
     * when the module is discovered.
     */
    public function Routes($router): void
    {
        // Main CRUD routes
        $router->addRoute('/testproduct', Controller::class, 'display');
        $router->addRoute('/testproduct/api', Controller::class, 'api');
        $router->addRoute('/testproduct/search', Controller::class, 'search');
        
        // Enhanced: Environment-specific routes
        if (\upMVC\Config\Environment::isDevelopment()) {
            $router->addRoute('/testproduct/debug', Controller::class, 'debug');
        }
    }
}