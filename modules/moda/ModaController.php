<?php
/*
 * Created on Tue Oct 31 2023
 *
 * Copyright (c) 2023 BitsHost
 */

namespace Moda;

use Common\Bmvc\BaseController;
use Moda\ModaModel;
use Moda\ModaView;

/**
 * ModaController
 */
class ModaController extends BaseController
{
    /**
     * display
     *
     * @return void
     */
    public function display()
    {
        $users = [
            new ModaModel('John Doe', 'john@example.com'),
            new ModaModel('Jane Doe', 'jane@example.com')
        ];



        if (isset($_SESSION["username"])) {
            echo "<br>";
            echo $_SESSION["username"];
            echo "<br>";

        }
        else {
            echo " Not Logged In! Something else.";
        }

        $this->render('moda/ModaView', ['users' => $users]);


        //$newView = new TestView();
        //$newView->view();
    }

}