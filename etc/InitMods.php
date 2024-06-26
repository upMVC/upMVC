<?php
/*
 *   Created on Tue Oct 31 2023
 *   Copyright (c) 2023 BitsHost
 *   All rights reserved.
 *
 *   Permission is hereby granted, free of charge, to any person obtaining a copy
 *   of this software and associated documentation files (the "Software"), to deal
 *   in the Software without restriction, including without limitation the rights
 *   to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *   copies of the Software, and to permit persons to whom the Software is
 *   furnished to do so, subject to the following conditions:
 *
 *   The above copyright notice and this permission notice shall be included in all
 *   copies or substantial portions of the Software.
 *
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

use Test\Routes\Routes as TestRoutes;
use Moda\Routes\Routes as ModaRoutes;
use Suba\Routes\Routes as SubaRoutes;
use User\Routes\Routes as UserRoutes;
use New\Routes\Routes as NewRoutes;
use Auth\Routes\Routes as AuthRoutes;
use React\Routes\Routes as ReactRoutes;
use Reactb\Routes\Routes as ReactbRoutes;
use ReactCrud\Routes\Routes as ReactCrudRoutes;
//add other module routes
//custom routes example
use Test\Controller;

/**
 * InitMods
 */
class InitMods
{
    /**
     * addRoutes
     *
     * @param Router $router
     * @return void
     */
    public function addRoutes(Router $router): void
    {
        $this->registerModuleRoutes($router);
        $this->registerCustomRoutes($router);
    }

    /**
     * registerModuleRoutes
     *
     * @param Router $router
     * @return void
     */
    private function registerModuleRoutes(Router $router): void
    {
        $modules = $this->getModules();
        foreach ($modules as $module) {
            $module->Routes($router);
        }
    }

    /**
     * registerCustomRoutes
     *
     * @param Router $router
     * @return void
     */
    private function registerCustomRoutes(Router $router): void
    {
        // Register custom routes here
        // $router->addRoute('/custom-route', CustomController::class, 'customAction');
        $router->addRoute('/custom', Controller::class, 'display');
    }

    /**
     * getModules
     *
     * @return array
     */
    private function getModules(): array
    {
        return [
            new TestRoutes(),
            new ModaRoutes(),
            new SubaRoutes(),
            new UserRoutes(),
            new NewRoutes(),
            new AuthRoutes(),
            new ReactRoutes(),
            new ReactbRoutes(),
            new ReactCrudRoutes(),
            //new OtherRoutes()
        ];
    }
}
