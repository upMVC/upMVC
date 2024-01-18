<?php

namespace React\Routes;

use React\ReactController;

class ReactRoutes
{
    public function Routes($router)
    {
        $router->addRoute('/react', ReactController::class, 'display');
        $router->addRoute('/comp', ReactController::class, 'comp');
    }
}
