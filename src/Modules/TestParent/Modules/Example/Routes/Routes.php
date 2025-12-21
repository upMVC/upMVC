<?php
namespace App\Modules\TestParent\Modules\Example\Routes;

use App\Modules\TestParent\Modules\Example\Controller;

/**
 * Example Submodule Routes
 * 
 * This submodule will be auto-discovered by InitModsImproved.php
 * Location: src/Modules/TestParent/Modules/Example/Routes/Routes.php
 */
class Routes
{
    /**
     * Register submodule routes with the router
     */
    public function Routes($router): void
    {
        $router->addRoute('/testparent/example', Controller::class, 'display');
        $router->addRoute('/testparent/example/test', Controller::class, 'test');
    }
}