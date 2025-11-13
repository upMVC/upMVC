<?php
/*
 *   Created on October 11, 2025
 *   Copyright (c) 2025 BitsHost
 *   Enhanced Module Routes
 */

namespace App\Modules\enhanced\Routes;

use App\Modules\enhanced\Controller;

/**
 * Enhanced Routes
 * 
 * Routes for the enhanced features demo module
 */
class Routes
{
    /**
     * Register routes
     *
     * @param mixed $router
     * @return void
     */
    public function routes($router): void
    {
        // Main demo page
        $router->addRoute('/enhanced', Controller::class, 'display');
        
        // API endpoint
        $router->addRoute('/enhanced/api', Controller::class, 'api');
        
        // Alternative routes
        $router->addRoute('/demo/enhanced', Controller::class, 'display');
        $router->addRoute('/features', Controller::class, 'display');
    }
}










