<?php
namespace App\Modules\TestDashboard\Routes;

use App\Modules\TestDashboard\Controller;

/**
 * Enhanced App\Modules\TestDashboard Routes
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
        $router->addRoute('/testdashboard', Controller::class, 'display');
        $router->addRoute('/testdashboard/about', Controller::class, 'about');
        $router->addRoute('/testdashboard/api', Controller::class, 'api');
        
        // Enhanced: Environment-specific routes
        if (\App\Etc\Config\Environment::isDevelopment()) {
            $router->addRoute('/testdashboard/debug', Controller::class, 'debug');
        }
    }
}