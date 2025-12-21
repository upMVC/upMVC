<?php
namespace App\Modules\TestApi\Routes;

use App\Modules\TestApi\Controller;

/**
 * Enhanced App\Modules\TestApi Routes
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
        // RESTful API routes
        $router->addRoute('/api/testapi', Controller::class, 'api');
        $router->addRoute('/api/testapi/v1', Controller::class, 'apiV1');
        
        // Enhanced: Environment-specific routes
        if (\App\Etc\Config\Environment::isDevelopment()) {
            $router->addRoute('/testapi/debug', Controller::class, 'debug');
        }
    }
}