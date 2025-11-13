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

namespace App\Modules\Test;

//use App\Modules\Test\Model
//use App\Modules\Test\View

/**
 * TestController
 */
class Controller
{
    /**
     * display
     *
     * @return void
     */
    public function display($reqRoute, $reqMet)
    {
        $view = new View();
        $users = [
            new Model('John Doe', 'john@example.com'),
            new Model('Jane Doe', 'jane@example.com'),
            new Model('Alice Smith', 'alice@example.com'),
            new Model('Bob Wilson', 'bob@example.com'),
            new Model('Carol Brown', 'carol@example.com')
        ];

        if (isset($_SESSION["logged"])) {
            $view->View($reqMet, $users);
            echo $reqMet . " " .  $reqRoute . " ";
        } else {
            $view->notLoggedIn();
        }
    }

    /**
     * displayModern - Modern UI demo
     *
     * @return void
     */
    public function displayModern($reqRoute, $reqMet)
    {
        $view = new ViewModern();
        $users = [
            new Model('Emma Johnson', 'emma.johnson@modernui.dev'),
            new Model('Liam Rodriguez', 'liam.rodriguez@modernui.dev'),
            new Model('Olivia Chen', 'olivia.chen@modernui.dev'),
            new Model('Noah Kim', 'noah.kim@modernui.dev'),
            new Model('Ava Martinez', 'ava.martinez@modernui.dev'),
            new Model('Isabella Thompson', 'isabella.thompson@modernui.dev'),
            new Model('Sophia García', 'sophia.garcia@modernui.dev')
        ];

        if (isset($_SESSION["logged"])) {
            $view->View($reqMet, $users);
            echo $reqMet . " " .  $reqRoute . " ";
        } else {
            $view->notLoggedIn();
        }
    }
}











