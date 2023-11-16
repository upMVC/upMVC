<?php
/*
 * Created on Tue Oct 31 2023
 *
 * Copyright (c) 2023 BitsHost
 */

namespace Test;

use Test\TestModel;
use Test\TestView;

/**
 * TestController
 */
class TestController
{
    /**
     * display
     *
     * @return void
     */
    public function display($request)
    {
        $users = [
            new TestModel('John Doe', 'john@example.com'),
            new TestModel('Jane Doe', 'jane@example.com')
        ];


        //test for logged in user
        if (isset($_SESSION["username"])) {
            echo "<br>";
            echo $_SESSION["username"];
            echo "<br>";

        }
        else {
            echo " Not Logged In! Something else.";
        }

        $view = new TestView();
        $view->View($request, $users);



    }

}