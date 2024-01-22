<?php

namespace Reactb\Routes;

use Reactb\ReactbController;

class ReactbRoutes
{
    public function Routes($router)
    {
        $router->addRoute('/reactb', ReactbController::class, 'display');
        //react Build links from index.html 
        $router->addRoute('/logo', ReactbController::class, 'logo');
        $router->addRoute('/manifest', ReactbController::class, 'manifest');
        $router->addRoute('/mainjs', ReactbController::class, 'mainjs');
        $router->addRoute('/maincss', ReactbController::class, 'maincss');
    }
}
