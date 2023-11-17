<?php
/*
 * Created on Tue Oct 31 2023
 *
 * Copyright (c) 2023 BitsHost
 */
namespace Suba\Routes;

use Suba\SubaController;

class SubaRoutes
{


    public function Routes($router)
    {
        $router->addRoute('/suba.php', SubaController::class, 'display');
        $router->addRoute('/suba', SubaController::class, 'display');
        $router->addRoute('/suba/subpage', SubaController::class, 'display');

    }




}