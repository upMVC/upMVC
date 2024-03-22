<?php
/*
 *   Created on Tue Oct 31 2023
 
 *   Copyright (c) 2023 
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

use User\UserModel;
use User\UserView;


class UserController
{
    private $userModel;

    public $moduleRoute = BASE_URL . '/users';

    private $table = 'users';

    private $nameApi;
    private $emailApi;
    private $usernameApi;
    private $passwordApi;

    public function display($request)
    {
        if (isset($_SESSION["username"])) {
            $this->selectAction($request);
        } else {
            header('Location: ' . BASE_URL . '/');
        }
    }

    public function selectAction($request)
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
        $userRecords = $this->getUserModel()->getAllUsers($table);

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
        $name     = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
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
        $name     = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
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



    /////////API EXAMPLE///////////////////////////////////////////////////////////////////////

    public function apiResponse($request)
    {
        if ($request === 'POST') {
            $task = $_POST['task'];
            if ($_POST['id']) {
                $userId = $_POST['id'];
            }
            //$postData = json_decode(file_get_contents('php://input'), true);
            switch ($task) {
                case 'create':
                    $this->nameApi = $_POST["name"];
                    $this->emailApi = $_POST["email"];
                    $this->usernameApi = $_POST["username"];
                    $this->passwordApi = $_POST["password"];
                    $this->createUserApi();
                    break;
                case 'update':
                    $this->nameApi = $_POST["name"];
                    $this->emailApi = $_POST["email"];
                    $this->usernameApi = $_POST["username"];
                    $this->passwordApi = $_POST["password"];
                    $this->updateUserApi($userId);
                    break;
                case 'delete':
                    $this->deleteUserApi($userId);
                    break;
                case 'readall':
                    $this->getAllUsersApi();
                    break;
                case 'readById':
                    $this->getUserByIdApi($userId);
                    break;
                default:
                    echo "Bad Request";
            }
        } else {
            $task = $_GET['task'];

            //code....

        }
    }

    public function getAllUsersApi()
    {

        $userRecords = $this->getUserModel()->getAllUsers($this->table);

        if ($userRecords) {
            print(json_encode($userRecords));
            header('Access-Control-Allow-Origin: *');
        } else {
            echo "No users found.";
        }
    }

    public function getUserByIdApi($userId)
    {
        $userRecord = $this->getUserModel()->getUserById($userId, $this->table);

        if ($userRecord) {
            // print_r(json_encode($userRecord));
            return $userRecord;
        } else {
            echo "User not found.";
        }
    }

    public function createUserApi()
    {
        $userData = [
            'name'  => $this->nameApi,
            'email' => $this->emailApi,
            'username' => $this->usernameApi,
            'password' => $this->passwordApi,
        ];

        $userId = $this->getUserModel()->createUser($userData, $this->table);
        //get data for created user
        $createddUser = $this->getUserByIdApi($userId);
        $output = [];
        if ($userId) {

            $output["response"] = "User created successfully! (ID: $userId)";
            print_r(json_encode($createddUser));
            header('Access-Control-Allow-Origin: *');
        } else {
            $output["response"] = "Error creating user.";
            print_r(json_encode($output));
        }
    }


    public function updateUserApi($userId)
    {
        $userData = [
            'name'  => $this->nameApi,
            'email' => $this->emailApi,
            'username' => $this->usernameApi,
            'password' => $this->passwordApi,
            ];
        
        $success = $this->getUserModel()->updateUser($userId, $userData, $this->table);
        //get updated user data 
        $updateddUser = $this->getUserByIdApi($userId);
        if ($success) {
            print_r(json_encode($updateddUser));
            header('Access-Control-Allow-Origin: *');
        } else {
            print_r(json_encode($success));
        }
    }

    public function deleteUserApi($userId)
    {
        //get deleted user data befor delete
        $deletedUser = $this->getUserByIdApi($userId);
        $success = $this->getUserModel()->deleteUser($userId, $this->table);

        if ($success) {
            print_r(json_encode($deletedUser));
            header('Access-Control-Allow-Origin: *');
        } else {
            print_r(json_encode($success));
        }
    }

    public function apiInfo()
    {
        $html = new UserView;
        return $html->apiInfo($this->moduleRoute);
    }
}
