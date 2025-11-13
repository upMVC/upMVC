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

namespace App\Modules\User;

use PDO;

//use App\Modules\User\Model
//use App\Modules\User\View


class Controller
{
    private $userModel;

    public $moduleRoute = BASE_URL . '/users';

    private $table = 'users';

    private $nameApi;
    private $emailApi;
    private $usernameApi;
    private $passwordApi;

    public function display($reqRoute, $reqMet)
    {
        if (isset($_SESSION["username"])) {
            $this->selectAction($reqMet);
            echo $reqMet . " " .  $reqRoute . " ";
        } else {
            header('Location: ' . BASE_URL . '/');
        }
    }

    public function selectAction($reqMet)
    {

        if ($reqMet === 'POST') {
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
            if (isset($_GET['action'])) {

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
            } else {
                $this->getUsersWithPagination();
            }
        }
    }

    private function getUserModel()
    {
        $userModel       = new Model();
        $this->userModel = $userModel;
        return $this->userModel;
    }

    private function getUserById($userId, $table)
    {
        $userRecord = $this->getUserModel()->getUserById($userId, $table);

        if ($userRecord) {
            print_r($userRecord);
        } else {
            echo "User not found.";
        }
    }

    private function getAllUsers($table)
    {
        $userRecords = $this->getUserModel()->getAllUsers($table);

        if ($userRecords) {
            print_r($userRecords);
        } else {
            echo "No users found.";
        }
    }


    private function getUsersWithPagination()
    {
        $view         = new View();
        $table        = "users";
        $page         = 1;
        $pageSize     = 5;
        $page         = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_NUMBER_INT) ?: 1;
        $itemsPerPage = 5; // Adjust this value based on your preference

        $userRecords = $this->getUserModel()->getUsersWithPagination($table, $page, $pageSize);

        if ($userRecords) {
            //print_r($userRecords)
            $totalUsers = count($this->getUserModel()->getAllUsers($table));
            $totalPages = ceil($totalUsers / $itemsPerPage);
            $view->renderReadTable($userRecords, $page, $totalPages, $this->moduleRoute);
        } else {
            echo "No users found.";
        }
    }

    private function createUser()
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

    private function updateUser()
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

    private function deleteUser()
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
        $view  = new View();
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
        $view = new View();
        $view->renderCreateForm($this->moduleRoute);
    }



    /////////API EXAMPLE///////////////////////////////////////////////////////////////////////

    public function apiResponse($reqRoute, $reqMet)
    {
        if ($reqMet === 'POST') {
            if (isset($_POST['task'])) {
                $task = $_POST['task'];

                //$postData = json_decode(file_get_contents('php://input'), true)
                switch ($task) {
                    case 'create':
                        if (isset($_POST['name'], $_POST['email'], $_POST['username'], $_POST['password'])) {
                            $this->nameApi = $_POST["name"];
                            $this->emailApi = $_POST["email"];
                            $this->usernameApi = $_POST["username"];
                            $this->passwordApi = $_POST["password"];
                            $this->createUserApi();
                        } else {
                            $this->errorParameters($reqMet);
                        }
                        break;
                    case 'update':
                        if (isset($_POST['id'], $_POST['name'], $_POST['email'], $_POST['username'], $_POST['password'])) {
                            $userId = $_POST['id'];
                            $this->nameApi = $_POST["name"];
                            $this->emailApi = $_POST["email"];
                            $this->usernameApi = $_POST["username"];
                            $this->passwordApi = $_POST["password"];
                            $this->updateUserApi($userId);
                        } else {
                            $this->errorParameters($reqMet);
                        }
                        break;
                    case 'delete':
                        if (isset($_POST['id'])) {
                            $userId = $_POST['id'];
                            $this->deleteUserApi($userId);
                        } else {
                            $this->errorParameters($reqMet);
                        }
                        break;
                    case 'readall':
                        $this->getAllUsersApi();
                        break;
                    case 'readById':
                        if (isset($_POST['id'])) {
                            $userId = $_POST['id'];
                            $this->getUserByIdApi($userId);
                        } else {
                            $this->errorParameters($reqMet);
                        }
                        break;
                    default:
                        $this->badRequest($reqMet);
                }
            } else {
                $this->errorParameters($reqMet);
                //code....
            }
        } elseif ($reqMet === 'GET') {
            if (isset($_GET["task"])) {
                $task = $_GET['task'];

                //code...

            }

            $this->badRequest($reqMet);

            //code....

        } else {
            $this->badRequest($reqMet);
            //code....
        }
    }

    private function getAllUsersApi()
    {

        $userRecords = $this->getUserModel()->getAllUsers($this->table);
        $numberOfRecords = count($userRecords);

        if ($userRecords) {
            for ($i = 0; $i < $numberOfRecords; $i++) {
                $output[$i]['id'] = $userRecords[$i]['id'];
                $output[$i]['name'] = $userRecords[$i]['name'];
                $output[$i]['email'] = $userRecords[$i]['email'];
                $output[$i]['username'] = $userRecords[$i]['username'];
                $output[$i]['password'] = $userRecords[$i]['password'];
                header('Access-Control-Allow-Origin: *');
            }
            print_r(\json_encode($output));
        } else {
            echo "No users found.";
        }
    }

    private function getUserByIdApi($userId)
    {
        $userRecord = $this->getUserModel()->getUserById($userId, $this->table);

        if ($userRecord) {
            print_r(\json_encode($userRecord));
        } else {
            $answer["actionResult"] = "User not found.";
            print_r(json_encode($answer));
        }
    }

    private function getUserByIdApiForUpdate($userId)
    {
        $userRecord = $this->getUserModel()->getUserById($userId, $this->table);

        if ($userRecord) {
            return json_encode($userRecord);
        } else {
            $answer["actionResult"] = "User not found.";
            return json_encode($answer);
        }
    }

    private function createUserApi()
    {
        $userData = [
            'name'  => $this->nameApi,
            'email' => $this->emailApi,
            'username' => $this->usernameApi,
            'password' => $this->passwordApi,
        ];

        $userId = $this->getUserModel()->createUser($userData, $this->table);

        //get data for created user
        $createdUser = $this->getUserByIdApiForUpdate($userId);
        if ($userId) {
            print_r($createdUser);
            // header('Access-Control-Allow-Origin: *');
        } else {
            $output["response"] = "Error creating user.";
            print_r(json_encode($output));
        }
    }


    private function updateUserApi($userId)
    {
        $userData = [
            'name'  => $this->nameApi,
            'email' => $this->emailApi,
            'username' => $this->usernameApi,
            'password' => $this->passwordApi,
        ];

        $success = $this->getUserModel()->updateUser($userId, $userData, $this->table);

        if ($success === false) {
            //get data for created user
            $updatedUser = $this->getUserByIdApiForUpdate($userId);
            print_r($updatedUser);
        } else {

            $updatedUser = $this->getUserByIdApiForUpdate($userId);
            print_r($updatedUser);
        }
    }

    private function deleteUserApi($userId)
    {
        //get deleted user data befor delete
        $deletedUser = $this->getUserByIdApiForUpdate($userId);
        $success = $this->getUserModel()->deleteUser($userId, $this->table);

        if ($success === true) {
            print_r($deletedUser);
        } else {
            $answer["actionResult"] = $success;
            $answer["status"] = "User not found, not deleted.";
            print_r(\json_encode($answer));
        }
    }


    private function errorParameters($reqMet)
    {
        $output = [];
        $output["answer"] = "Bad Data. Parameters missing! Request is: " . $reqMet;
        print_r(json_encode($output));
    }

    private function badRequest($reqMet)
    {
        $output = [];
        $output["answer"] = "Bad Request. Request is: " . $reqMet;
        print_r(json_encode($output));
    }


    public function apiInfo()
    {
        $html = new View;
        return $html->apiInfo($this->moduleRoute);
    }
}











