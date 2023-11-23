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

namespace User;

use Common\Bmvc\BaseView;

class UserView
{

    public $title = "Create User";

    public function renderCreateForm($moduleRoute)
    {
        $view        = new BaseView();
        $this->title = "Create Users";
        $view->startHead($this->title);
        echo "<style>";
        require THIS_DIR . "/modules/user/etc/styles.css";
        echo "</style>";
        echo "<script>";
        include THIS_DIR . "/modules/user/etc/script.js";
        echo "</script>";
        $view->endHead();
        $view->startBody($this->title);
        echo '<div class="container">';
        echo '<form method="post" action="' . $moduleRoute . '">';
        echo '<input type="hidden" name="action" value="create">';
        echo '<label for="name">Name:</label>';
        echo '<input type="text" name="name" required>';
        echo '<br>';
        echo '<label for="email">Email:</label>';
        echo '<input type="email" name="email" required>';
        echo '<br>';
        echo '<input type="submit" value="Create User">';
        echo '</form>';
        echo '</div>';
        $view->endBody();
        $view->startFooter();
        $view->endFooter();
    }




    public function renderReadTable($users, $currentPage, $totalPages, $moduleRoute)
    {
        $view        = new BaseView();
        $this->title = "Read Users";
        $view->startHead($this->title);
        echo "<style>";
        require THIS_DIR . "/modules/user/etc/styles.css";
        echo "</style>";
        echo "<script>";
        include THIS_DIR . "/modules/user/etc/script.js";
        echo "</script>";
        $view->endHead();
        $view->startBody($this->title);
        echo '<div class="container">';
        //echo '<h2>Read Users</h2>';
        echo '<table>';
        echo '<tr>';
        echo '<th>ID</th>';
        echo '<th>Name</th>';
        echo '<th>Email</th>';
        echo '<th>Action</th>';
        echo '<th>Action</th>';
        echo '<th>Action</th>';
        echo '</tr>';

        foreach ($users as $user) {
            echo '<tr>';
            echo '<td>' . $user['id'] . '</td>';
            echo '<td>' . $user['name'] . '</td>';
            echo '<td>' . $user['email'] . '</td>';
            echo '<td>';
            echo '<a href=" ' . $moduleRoute . '?action=update&id=' . $user['id'] . '">Update</a>';
            echo '</td>';
            echo '<td>';
            echo '<a href="' . $moduleRoute . '?action=delete&id=' . $user['id'] . '" class="delete-link" onclick="return confirmDeletion()">Delete</a>';
            echo '</td>';
            echo '<td>';
            echo '<a href="' . $moduleRoute . '?action=form">Create</a>';
            echo '</td>';
            echo '</tr>';
        }

        echo '</table>';

        // Pagination links
        echo '<div class="pagination">';
        for ($i = 1; $i <= $totalPages; $i++) {
            echo '<a href="' . $moduleRoute . '?action=read&page=' . $i . '" ';
            if ($i == $currentPage) {
                echo 'class="active"';
            }
            echo '>' . $i . '</a>';
        }
        echo '</div>';
        echo '</div>';
        $view->startFooter();
        $view->endFooter();
    }


    public function renderUpdateForm($user, $moduleRoute)
    {
        $view        = new BaseView();
        $this->title = "Update Users";
        $view->startHead($this->title);
        echo "<style>";
        require THIS_DIR . "/modules/user/etc/styles.css";
        echo "</style>";
        echo "<script>";
        include THIS_DIR . "/modules/user/etc/script.js";
        echo "</script>";
        $view->endHead();
        $view->startBody($this->title);
        //print_r($user);
        echo '<div class="container">';
        echo '<form method="post" action=" ' . $moduleRoute . ' ">';
        echo '<input type="hidden" name="action" value="update">';
        echo '<input type="hidden" name="id" value="' . $user['id'] . '">';
        echo '<label for="name">Name:</label>';
        echo '<input type="text" name="name" value="' . $user['name'] . '" required>';
        echo '<br>';
        echo '<label for="email">Email:</label>';
        echo '<input type="email" name="email" value="' . $user['email'] . '" required>';
        echo '<br>';
        echo '<input type="submit" value="Update User">';
        echo '</form>';
        echo '</div>';
        $view->startFooter();
        $view->endFooter();
    }
}
