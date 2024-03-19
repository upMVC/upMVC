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

//add Routes for your modules
namespace MVC;

use Test\Routes\TestRoutes;
use Moda\Routes\ModaRoutes;
use Suba\Routes\SubaRoutes;
use User\Routes\UserRoutes;
use New\Routes\NewRoutes;
use Auth\Routes\AuthRoutes;
use React\Routes\ReactRoutes;
use Reactb\Routes\ReactbRoutes;
use ReactCrud\Routes\ReactCrudRoutes;


class InitMods
{
    public function addRoutes($router)
    {
        $testR = new TestRoutes();
        $testR->Routes($router);

        $ModaR = new ModaRoutes();
        $ModaR->Routes($router);

        $SubaR = new SubaRoutes();
        $SubaR->Routes($router);

        $UserR = new UserRoutes();
        $UserR->Routes($router);

        $NewR = new NewRoutes();
        $NewR->Routes($router);

        $AuthR = new AuthRoutes();
        $AuthR->Routes($router);

        $ReactR = new ReactRoutes();
        $ReactR->Routes($router);

        $ReactbR = new ReactbRoutes();
        $ReactbR->Routes($router);

        $ReactCrudR = new ReactCrudRoutes();
        $ReactCrudR->Routes($router);



        /*
        include "./etc/file.php" ;
        You can insert a file.php here that can be automatically updated using a tool from a separate user.
        If you want developers to be able to extend your system. All users should be able to edit file.php.
        Following that, you may construct a class for all users. When the class is run, a new module is added to file.php.
        file.php content e.g.:

        $newModule = new myNewModuleRoutes();
        $newModule->Routes($router);

        */
    }
}
