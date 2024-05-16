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

namespace upMVC;

use Test\Controller;
use upMVC\InitMods;


/**
 * Routes
 */
class Routes
{

    /**
     * startRoutes
     *
     * @param  mixed $url
     * @return void
     */
    public function startRoutes($url, $request)
    {
        $router = new Router();

        //-1. default system routes
        $router->addRoute('/abba', Controller::class, 'display');
        ///////////////////route, class, function()////////////////
        ///////////////////////////////////////////////////////////

        //-2. modules routes
        //$userR = new UserRoutes();
        //$userR->Routes($router);

        //combining all modules routes

        $modulesRoutes = new InitMods();
        $modulesRoutes->addRoutes($router);
        ///////////////////////////////////////////////////////////

        #-3. call Dispatcher
        $router->dispatcher($url, $request);
    }
}
