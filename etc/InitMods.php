<?php
/*
 * Created on Tue Oct 31 2023
 *
 * Copyright (c) 2023 BitsHost
 */

//add Routes for your modules
namespace MVC;

use Test\Routes\TestRoutes;
use Moda\Routes\ModaRoutes;
use Suba\Routes\SubaRoutes;
use User\Routes\UserRoutes;

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
