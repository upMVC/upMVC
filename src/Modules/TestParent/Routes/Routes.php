<?php
namespace App\Modules\TestParent\Routes;

use App\Modules\TestParent\Controller;

/**
 * Enhanced App\Modules\TestParent Routes
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
        $router->addRoute('/testparent', Controller::class, 'display');
        $router->addRoute('/testparent/about', Controller::class, 'about');
        $router->addRoute('/testparent/api', Controller::class, 'api');
        
        // Enhanced: Environment-specific routes
        if (\App\Etc\Config\Environment::isDevelopment()) {
            $router->addRoute('/testparent/debug', Controller::class, 'debug');
        }
    }
}