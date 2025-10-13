<?php

namespace Dashusers\Routes;

use Ashusers\Controller;

class Routes
{
    public function Routes($router)
    {
        $router->addRoute('/dashusers', Controller::class, 'index');
        $router->addRoute('/dashusers/create', Controller::class, 'create');
        $router->addRoute('/dashusers/view', Controller::class, 'view');
        $router->addRoute('/dashusers/edit', Controller::class, 'edit');
        $router->addRoute('/dashusers/delete', Controller::class, 'delete');
        $router->addRoute('/dashusers/bulk', Controller::class, 'bulk');
        $router->addRoute('/dashusers/export', Controller::class, 'export');
    }
}