<?php

namespace Reactnb\Routes;

use Reactnb\Controller;
//use App\Router\Route; // Assuming App\Router\Route exists in your upMVC

class Routes
{
    public static function routes($router): array
    {
        return [
            $router->addRoute('/reactnb', Controller::class, 'display'),
            $router->addRoute('/reactnb/other', Controller::class, 'display'),
        ];
    }
}