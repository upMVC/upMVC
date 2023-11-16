<?php
/*
 * Created on Tue Oct 31 2023
 *
 * Copyright (c) 2023 BitsHost
 */
namespace Moda\Routes;
use MVC\Routes;
use Moda\ModaController;


/**
 * ModaRoutes
 */
class ModaRoutes extends Routes
{

    
    /**
     * Routes
     *
     * @param  mixed $router
     * @return void
     */
    public function Routes($router)
    {
    
        $router->addRoute('/moda.php', ModaController::class, 'display');
        $router->addRoute('/moda', ModaController::class, 'display');
        $router->addRoute('/moda/subpage', ModaController::class, 'display');
        $router->addRoute('/moda-page-one', ModaController::class, 'display');
        $router->addRoute('/moda-page-one/two', ModaController::class, 'display');
    
      
    }




}