<?php
namespace App\Modules\TestAuth\Routes;

use App\Modules\TestAuth\Controller;

/**
 * Enhanced App\Modules\TestAuth Routes
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
        // Authentication routes (middleware-ready)
        $router->addRoute('/testauth/profile', Controller::class, 'profile');
        $router->addRoute('/testauth/settings', Controller::class, 'settings');
        
        // Enhanced: Environment-specific routes
        if (\App\Etc\Config\Environment::isDevelopment()) {
            $router->addRoute('/testauth/debug', Controller::class, 'debug');
        }
    }
}