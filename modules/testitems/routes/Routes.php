<?php
namespace Testitems\Routes;

use Testitems\Controller;

/**
 * Enhanced TestItems Routes
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
        $router->addRoute('/testitemss', Controller::class, 'display');
        $router->addRoute('/testitemss/api', Controller::class, 'api');
        $router->addRoute('/testitemss/search', Controller::class, 'search');
        
        // Enhanced: Environment-specific routes
        if (\upMVC\Config\Environment::isDevelopment()) {
            $router->addRoute('/testitemss/debug', Controller::class, 'debug');
        }
    }
}