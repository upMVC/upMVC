<?php
namespace App\Modules\TestBlog\Routes;

use App\Modules\TestBlog\Controller;

/**
 * Enhanced App\Modules\TestBlog Routes
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
        $router->addRoute('/testblog', Controller::class, 'display');
        $router->addRoute('/testblog/about', Controller::class, 'about');
        $router->addRoute('/testblog/api', Controller::class, 'api');
        
        // Enhanced: Environment-specific routes
        if (\upMVC\Config\Environment::isDevelopment()) {
            $router->addRoute('/testblog/debug', Controller::class, 'debug');
        }
    }
}