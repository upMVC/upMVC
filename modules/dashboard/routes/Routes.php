<?php

namespace Dashboard\Routes;

use Dashboard\Controller;

class Routes
{
    public static function routes($router): array
    {
        return [
            $router->addRoute('/dashboard', Controller::class, 'index'),
            $router->addRoute('/dashboard/login', Controller::class, 'login'),
            $router->addRoute('/dashboard/logout', Controller::class, 'logout'),
            $router->addRoute('/dashboard/users', Controller::class, 'users'),
            $router->addRoute('/dashboard/users/add', Controller::class, 'addUser'),
            $router->addRoute('/dashboard/users/edit', Controller::class, 'editUser'),
            $router->addRoute('/dashboard/users/delete/', Controller::class, 'deleteUser'),
            $router->addRoute('/dashboard/settings', Controller::class, 'settings')
        ];
    }
}
