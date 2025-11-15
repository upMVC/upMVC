<?php
namespace App\Modules\TestCrud\Routes;

use App\Modules\TestCrud\Controller;

/**
 * Enhanced App\Modules\TestCrud Routes
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
        $router->addRoute('/testcrud', Controller::class, 'display');
        $router->addRoute('/testcrud/api', Controller::class, 'api');
        $router->addRoute('/testcrud/search', Controller::class, 'search');
        
        // Enhanced: Environment-specific routes
        if (\App\Etc\Config\Environment::isDevelopment()) {
            $router->addRoute('/testcrud/debug', Controller::class, 'debug');
        }
    }
}