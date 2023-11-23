<?php
/*
 * Created on Tue Oct 31 2023
 *
 * Copyright (c) 2023 BitsHost
 */

namespace User;

use User\UserModel;
use User\UserView;


class UserController
{
    private $userModel;

    public $moduleRoute = BASE_URL . '/users';

    public function display($request)
    {

        if ($request === 'POST') {
            $action = $_POST['action'];

            switch ($action) {
                case 'create':
                    $this->createUser();
                    break;
                case 'update':
                    $this->updateUser();
                    break;
            }
        } else {
            $action = $_GET['action'];

            switch ($action) {
                case 'read':
                    $this->getUsersWithPagination();
                    break;
                case 'update':
                    $this->renderUpdateForm();
                    break;
                case 'delete':
                    $this->deleteUser();
                    break;
                case 'form':
                    $this->createForm();
                    break;
                default:
                    $this->getUsersWithPagination();
                    break;
            }
        }
    }

    public function getUserModel()
    {
        $userModel       = new UserModel();
        $this->userModel = $userModel;
        return $this->userModel;
    }

    public function getUserById($userId, $table)
    {
        $userRecord = $this->getUserModel()->getUserById($userId, $table);

        if ($userRecord) {
            print_r($userRecord);
        } else {
            echo "User not found.";
        }
    }

    public function getAllUsers($table)
    {
        $userRecords = $this->userModel->getAllUsers($table);

        if ($userRecords) {
            print_r($userRecords);
        } else {
            echo "No users found.";
        }
    }


    public function getUsersWithPagination()
    {
        $view         = new UserView();
        $table        = "users";
        $page         = 1;
        $pageSize     = 5;
        $page         = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_NUMBER_INT) ?: 1;
        $itemsPerPage = 5; // Adjust this value based on your preference

        $userRecords = $this->getUserModel()->getUsersWithPagination($table, $page, $pageSize);

        if ($userRecords) {
            //print_r($userRecords);
            $totalUsers = count($this->getUserModel()->getAllUsers($table));
            $totalPages = ceil($totalUsers / $itemsPerPage);
            $view->renderReadTable($userRecords, $page, $totalPages, $this->moduleRoute);
        } else {
            echo "No users found.";
        }
    }

    public function createUser()
    {
        $table    = 'users';
        $name     = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $email    = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $userData = [
            'name'  => $name,
            'email' => $email,
        ];

        $userId = $this->getUserModel()->createUser($userData, $table);

        if ($userId) {
            echo "User created successfully! (ID: $userId)";
            header('Location: ' . $this->moduleRoute . '?action=read');
        } else {
            echo "Error creating user.";
        }
    }

    public function updateUser()
    {
        $table    = 'users';
        $userId   = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
        $name     = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $email    = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $userData = [
            'name'  => $name,
            'email' => $email,
        ];

        $success = $this->getUserModel()->updateUser($userId, $userData, $table);

        if ($success) {
            echo "User updated successfully!";
            header('Location: ' . $this->moduleRoute . '?action=read');
        } else {
            echo "Error updating user.";
        }
    }

    public function deleteUser()
    {
        $table   = "users";
        $userId  = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $success = $this->getUserModel()->deleteUser($userId, $table);

        if ($success) {
            echo "User deleted successfully!";
            header('Location: ' . $this->moduleRoute . '?action=read');
        } else {
            echo "Error deleting user.";
        }
    }


    private function renderUpdateForm()
    {
        $view  = new UserView();
        $table = 'users';
        $id    = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $user  = $this->getUserModel()->getUserById($id, $table);

        if ($user) {
            $view->renderUpdateForm($user, $this->moduleRoute);
        } else {
            echo "User not found.";
        }
    }

    private function createForm()
    {
        $view = new UserView();
        $view->renderCreateForm($this->moduleRoute);
    }
}
