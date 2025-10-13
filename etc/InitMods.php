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

use Admin\Routes\Routes as AdminRoutes;
use Test\Routes\Routes as TestRoutes;
use Moda\Routes\Routes as ModaRoutes;
use Suba\Routes\Routes as SubaRoutes;
use User\Routes\Routes as UserRoutes;
use Userorm\Routes\Routes as UserormRoutes;
use Newmod\Routes\Routes as NewmodRoutes;
use Auth\Routes\Routes as AuthRoutes;
use React\Routes\Routes as ReactRoutes;
use Reactb\Routes\Routes as ReactbRoutes;
use ReactCrud\Routes\Routes as ReactCrudRoutes;
use Dashboard\Routes\Routes as DashboardRoutes;
use Enhanced\Routes\Routes as EnhancedRoutes;
//add other module routes

//custom routes example
//For instance, purpose - we are injecting the controller from the 'Test' module here, as a custom route or using system routes.
//We utilize an alias to prevent naming conflicts between the 'Test' module's 'Controller' and the 'Admin' module's 'Controller'.

use Test\Controller;
use Admin\Controller as AnythingElseHere;
//end custom routes example

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
        $router->addRoute('/customb', AnythingElseHere::class, 'display');
        
    }

    /**
     * getModules
     *
     * @return array
     */
    private function getModules(): array
    {
        return [
            new AdminRoutes(),
            new TestRoutes(),
            new ModaRoutes(),
            new SubaRoutes(),
            new UserRoutes(),
            new UserormRoutes(),
            new NewmodRoutes(),
            new AuthRoutes(),
            new ReactRoutes(),
            new ReactbRoutes(),
            new ReactCrudRoutes(),
            new DashboardRoutes(),
            new EnhancedRoutes(),
            //new OtherRoutes()
        ];
    }
}
