<?php
/*
 * Created on Tue Oct 31 2023
 *
 * Copyright (c) 2023 BitsHost
 */
namespace User\Routes;

use User\UserController;


/**
 * ModaRoutes
 */
class UserRoutes
{


    /**
     * Routes
     *
     * @param  mixed $router
     * @return void
     */
    public function Routes($router)
    {

        $router->addRoute('/users', UserController::class, 'display');
        
    


    }




}