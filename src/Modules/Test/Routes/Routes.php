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

namespace App\Modules\Test\Routes;

use App\Modules\Test\Controller;


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
        $router->addRoute('/',          Controller::class, 'display');
        $router->addRoute('/index.php', Controller::class, 'display');
        $router->addRoute('/test.php',  Controller::class, 'display');
        $router->addRoute('/test',      Controller::class, 'display');
        $router->addRoute('/test/subpage', Controller::class, 'display');

        // Modern view route
        $router->addRoute('/test/modern', Controller::class, 'displayModern');

        // --- Parameterized routes (Router extracts values — no .htaccess rules needed) ---

        // Single param:  /test/item/123   → $_GET['id'] = 123
        $router->addParamRoute('/test/item/{id:int}', Controller::class, 'display');

        // Single string param:  /test/article/my-slug  → $_GET['slug'] = 'my-slug'
        $router->addParamRoute('/test/article/{slug}', Controller::class, 'display');

        // Two params:  /test/pair/hello/world  → $_GET['first'] = 'hello', $_GET['second'] = 'world'
        $router->addParamRoute('/test/pair/{first}/{second}', Controller::class, 'display');
    }
}











