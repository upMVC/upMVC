<?php
/*
 * Admin Module - Routes
 * Defines all admin panel routes
 */

namespace App\Modules\Admin\Routes;

use App\Modules\Admin\Controller;
use App\Modules\Admin\Model;

class Routes
{
    private $model;
    private $table = 'user';

    public function routes($router)
    {
        $this->model = new Model();

        // Static routes
        $router->addRoute('/admin', Controller::class, 'display');
        $router->addRoute('/admin/users', Controller::class, 'display');
        $router->addRoute('/admin/users/add', Controller::class, 'display');

        // Get all users to generate dynamic routes
        $users = $this->model->getAllUsers();
        
        // Extract user IDs
        $userIds = [];
        foreach ($users as $user) {
            $userIds[] = $user['id'];
        }

        $usersIdsLength = count($userIds);

        // EDIT ROUTES - Generate route for each user ID
        $i = 0;
        $routesArray = [];
        while ($i < $usersIdsLength) {
            $routesArray[$i] = ['/admin/users/edit/' . $userIds[$i], Controller::class, 'display'];
            $i++;
        }

        foreach ($routesArray as $key => $value) {
            $router->addRoute($value[0], $value[1], $value[2]);
        }

        // DELETE ROUTES - Generate route for each user ID
        $i = 0;
        $routesArray = [];
        while ($i < $usersIdsLength) {
            $routesArray[$i] = ['/admin/users/delete/' . $userIds[$i], Controller::class, 'display'];
            $i++;
        }

        foreach ($routesArray as $key => $value) {
            $router->addRoute($value[0], $value[1], $value[2]);
        }
    }
}










