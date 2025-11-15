<?php
namespace App\Modules\Product\Routes;

use App\Modules\Product\Controller;

/**
 * Enhanced App\Modules\Product Routes
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
        // CRUD routes - full Create, Read, Update, Delete
        $router->addRoute('/product', Controller::class, 'display');
        $router->addRoute('/product/create', Controller::class, 'create');
        $router->addRoute('/product/store', Controller::class, 'store');
        $router->addRoute('/product/edit', Controller::class, 'edit');
        $router->addRoute('/product/update', Controller::class, 'update');
        $router->addRoute('/product/delete', Controller::class, 'delete');
        
        // Enhanced: Environment-specific routes
        if (\App\Etc\Config\Environment::isDevelopment()) {
            $router->addRoute('/product/debug', Controller::class, 'debug');
        }
    }
}