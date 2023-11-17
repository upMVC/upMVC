<?php
/*
 * Created on Tue Oct 31 2023
 *
 * Copyright (c) 2023 BitsHost
 */

namespace Suba;

use Common\Bmvc\BaseController;
use Suba\SubaModel;
use Suba\SubaView;

class SubaController extends BaseController
{
    public function display()
    {
        $users = [
            new SubaModel('John Doe', 'john@example.com'),
            new SubaModel('Jane Doe', 'jane@example.com')
        ];



        if (isset($_SESSION["username"])) {
            echo "<br>";
            echo $_SESSION["username"];
            echo "<br>";


        }
        else {
            echo " Not Logged In! Something else.";
        }

        $this->render('moda/modules/suba/SubaView', ['users' => $users]);

    }

}