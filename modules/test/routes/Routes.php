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

namespace Test\Routes;

use Test\Controller;


/**
 * TestRoutes
 */
class Routes
{


    /**
     * Routes
     *
     * @param  mixed $router
     * @return void
     */
    public function routes($router)
    {
        //$router = new Router()

        $router->addRoute('/', Controller::class, 'display');
        $router->addRoute('/index.php', Controller::class, 'display');
        $router->addRoute('/test.php', Controller::class, 'display');
        $router->addRoute('/test', Controller::class, 'display');
        $router->addRoute('/test/subpage', Controller::class, 'display');
        
        // Modern view routes
        $router->addRoute('/test/modern', Controller::class, 'displayModern');
        $router->addRoute('/test-modern', Controller::class, 'displayModern');
        //GET parameters, look at .htaccess
        //one parameter
        $router->addRoute('/test-one', Controller::class, 'display');
        $router->addRoute('/test-page-one', Controller::class, 'display');
        //two parameters
        $router->addRoute('/test-one/two', Controller::class, 'display');
        $router->addRoute('/test-page-one/two', Controller::class, 'display');

        // Test with an array of values, such as goods, articles, and so on.
        // You may get data from the database here.
        // avoid including:
        // $router->addRoute('test-a0', Controller::class, 'display');
        // $router->addRoute('test-a1', Controller::class, 'display');...
        // ...one after the other

        $i           = 0;
        $routesArray = [];
        while ($i < 5) {
            $routesArray[$i] = ['/test-a' . $i, Controller::class, 'display'];
            $i++;
        }

        foreach ($routesArray as $key => $value) {
            $router->addRoute($value[0], "$value[1]", $value[2]);
        }
    }
}
