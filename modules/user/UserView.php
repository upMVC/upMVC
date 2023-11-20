<?php
/*
 * Created on Tue Oct 31 2023
 *
 * Copyright (c) 2023 BitsHost
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