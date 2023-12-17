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

namespace Test;

use Common\Bmvc\BaseView;


/**
 * Testview
 */
class Testview extends BaseView
{
    /**
     * View
     *
     * @param  mixed $request
     * @param  mixed $users
     * @return void
     */
    public function View($request, $users)
    {

        $title = "List";
        $this->startHead($title);
?>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <?php
        $this->endHead();
        $this->startBody($title);
        ?>
        <ul id="ul">
            <strong>Click or Double Click on the first line.</strong>
            <?php foreach ($users as $user) : ?>
                <li id="cell">
                    <?= $user->name ?> (
                    <?= $user->email ?>)
                </li>
            <?php endforeach; ?>
        </ul>
        <script>
            $(document).ready(function() {
                $("#cell").click(function() {
                    $(this).hide();
                });
                $("#ul").dblclick(function() {
                    $("li").show();
                });

            });
        </script>
    <?php
        echo $request . "<br>";
        print_r($_GET);
        $this->endBody();
        $this->startFooter();
        $this->endFooter();
    }

    public function notLoggedIn()
    {
        $title = "List";
        $this->startHead($title);
        ?>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <?php
        $this->endHead();
        $this->startBody($title);
        echo " Not Logged In! Something else.";
        $this->endBody();
        $this->startFooter();
        $this->endFooter();
    }
}
