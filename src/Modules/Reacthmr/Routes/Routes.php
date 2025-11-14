<?php

/*
 *   Created on October 17, 2025
 *   
 *   Copyright (c) 2025 BitsHost
 *   All rights reserved.
 */

namespace App\Modules\Reacthmr\Routes;

use App\Modules\Reacthmr\Controller;

/**
 * ReactHMR Routes
 * 
 * Register routes for Hot Module Reload demo
 */
class Routes
{
    public function routes($router)
    {
        // Main page
        $router->addRoute("/reacthmr", Controller::class, "display");
        
        // HMR SSE stream
        $router->addRoute("/reacthmr/hmr", Controller::class, "display");
        
        // Serve JavaScript component
        $router->addRoute("/reacthmr/component", Controller::class, "display");
    }
}











