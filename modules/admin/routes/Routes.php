<?php

namespace Admin\Routes;

use Admin\Controller;
//use App\Router\Route; // Assuming App\Router\Route exists in your upMVC

class Routes
{
    public static function routes($router): array
    {
        return [
            $router->addRoute('/admin', Controller::class, 'display'),
            $router->addRoute('/admin/other', Controller::class, 'display'),
        ];
    }
}