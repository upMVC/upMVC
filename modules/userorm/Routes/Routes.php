<?php
/*
 *   Created on Tue Oct 31 2023
 
 *   Copyright (c) 2023 BitsHost
 *   All rights reserved.

 *   Permission is hereby granted, free of charge, to any person obtaining a copy
 *   of this software and associated documentation files (the "Software"), to deal
 *   in the Software without restriction, including without limitation the rights
 *   to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *   copies of the Software, and to permit persons to whom the Software is
 *   furnished to do so, subject to the following conditions:

 *   The above copyright notice and this permission notice shall be included in all
 *   copies or substantial portions of the Software.

 *   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *   IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *   FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *   AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *   LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *   OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 *   SOFTWARE.
 *   Here you may host your app for free:
 *   https://bitshost.biz/
 */

namespace Userorm\Routes;

use Userorm\Controller;
use Userorm\Model;



/**
 * ModaRoutes
 */
class Routes
{


    /**
     * Routes
     *
     * @param  mixed $router
     * @return void
     */
    private $model;
    private $table = 'users';
    public function routes($router)
    {
        $this->model = new Model();
        $router->addRoute('/usersorm', Controller::class, 'display');

        //list Route
        $router->addRoute('/usersorm/getall/320', Controller::class, 'getAll');
        $router->addRoute('/usersorm/create', Controller::class, 'display');
        $router->addRoute('/usersorm/store', Controller::class, 'display');


        $users = $this->model->getAllUsers($this->table);
        // \print_r($users);
        $obj = json_decode(json_encode($users));
        $usersIds = [];
        foreach ($obj as $o) {
            $userIds[] = $o->id;
            //echo $o->id . '<br>';
        }

        // print_r($userIds); 

        $usersIdsLength = count($users);

        //EDIT ROUTES
        $i           = 0;
        $routesArray = [];

        while ($i < $usersIdsLength) {
            $routesArray[$i] = ['/usersorm/edit/' . $userIds[$i], Controller::class, 'display'];
            $i++;
        }


        //print_r($routesArray);

        foreach ($routesArray as $key => $value) {
            $router->addRoute($value[0], $value[1], $value[2]);
        }


        //UPDATE ROUTES
        $i           = 0;
        $routesArray = [];

        while ($i < $usersIdsLength) {
            $routesArray[$i] = ['/usersorm/update/' . $userIds[$i], Controller::class, 'display'];
            $i++;
        }


        //print_r($routesArray);

        foreach ($routesArray as $key => $value) {
            $router->addRoute($value[0], $value[1], $value[2]);
        }


        //DELETE ROUTES
        $i           = 0;
        $routesArray = [];

        while ($i < $usersIdsLength) {
            $routesArray[$i] = ['/usersorm/delete/' . $userIds[$i], Controller::class, 'display'];
            $i++;
        }


        //print_r($routesArray);

        foreach ($routesArray as $key => $value) {
            $router->addRoute($value[0], $value[1], $value[2]);
        }
    }
}
