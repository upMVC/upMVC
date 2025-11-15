<?php
namespace App\Modules\TestBasic\Routes;

use App\Modules\TestBasic\Controller;

/**
 * Enhanced App\Modules\TestBasic Routes
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
        // Basic module routes
        $router->addRoute('/testbasic', Controller::class, 'display');
        $router->addRoute('/testbasic/about', Controller::class, 'about');
        $router->addRoute('/testbasic/api', Controller::class, 'api');
        
        // Enhanced: Environment-specific routes
        if (\App\Etc\Config\Environment::isDevelopment()) {
            $router->addRoute('/testbasic/debug', Controller::class, 'debug');
        }
    }
}