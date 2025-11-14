<?php

namespace App\Modules\DashboardExample\Routes;

use App\Modules\dashboardexample\Controller;

class Routes
{
    public function Routes($router): void
    {
        $router->addRoute('/dashboardexample', Controller::class, 'index');
        $router->addRoute('/dashboardexample/login', Controller::class, 'login');
        $router->addRoute('/dashboardexample/logout', Controller::class, 'logout');
        $router->addRoute('/dashboardexample/users', Controller::class, 'users');
        $router->addRoute('/dashboardexample/users/add', Controller::class, 'addUser');
        $router->addRoute('/dashboardexample/users/edit', Controller::class, 'editUser');
        $router->addRoute('/dashboardexample/users/delete/', Controller::class, 'deleteUser');
        $router->addRoute('/dashboardexample/settings', Controller::class, 'settings');
    }
}











