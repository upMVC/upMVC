<?php
/*
 * Admin Module - Controller
 * Handles admin dashboard and user CRUD operations
 */

namespace App\Modules\Admin;

use App\Modules\Admin\View;
use App\Modules\Admin\Model;

class Controller 
{
    private Model $model;
    private View $view;

    public function __construct()
    {
        $this->model = new Model();
        $this->view = new View();
    }

    /**
     * Main display handler with authentication check
     */
    public function display($reqRoute, $reqMet)
    {
        // Check authentication
        if (!isset($_SESSION["logged"]) || $_SESSION["logged"] !== true) {
            header('Location: ' . BASE_URL . '/auth');
            exit;
        }

        $this->handleRoute($reqRoute, $reqMet);
    }

    /**
     * Route handler
     */
    private function handleRoute($reqRoute, $reqMet)
    {
        switch ($reqRoute) {
            case '/admin':
                $this->dashboard();
                break;

            case '/admin/users':
                $this->listUsers();
                break;

            case '/admin/users/add':
                if ($reqMet === 'POST') {
                    $this->createUser();
                } else {
                    $this->showUserForm();
                }
                break;

            case (preg_match('/^\/admin\/users\/edit\/(\d+)$/', $reqRoute, $matches) ? true : false):
                $userId = (int) $matches[1];
                if ($reqMet === 'POST') {
                    $this->updateUser($userId);
                } else {
                    $this->showUserForm($userId);
                }
                break;

            case (preg_match('/^\/admin\/users\/delete\/(\d+)$/', $reqRoute, $matches) ? true : false):
                $userId = (int) $matches[1];
                $this->deleteUser($userId);
                break;

            default:
                $this->view->render(['view' => 'error', 'message' => '404 - Page not found']);
                break;
        }
    }

    /**
     * Display dashboard with stats
     */
    private function dashboard()
    {
        $userCount = $this->model->getUserCount();
        $data = [
            'view' => 'dashboard',
            'stats' => [
                'userCount' => $userCount
            ]
        ];
        $this->view->render($data);
    }

    /**
     * List all users
     */
    private function listUsers()
    {
        $users = $this->model->getAllUsers();
        $data = [
            'view' => 'users_list',
            'users' => $users
        ];
        $this->view->render($data);
    }

    /**
     * Show user form (add or edit)
     */
    private function showUserForm(?int $userId = null)
    {
        $user = null;
        if ($userId) {
            $user = $this->model->getUserById($userId);
            if (!$user) {
                $_SESSION['error'] = 'User not found';
                header('Location: ' . BASE_URL . '/admin/users');
                exit;
            }
        }

        $data = [
            'view' => 'user_form',
            'user' => $user,
            'isEdit' => $userId !== null
        ];
        $this->view->render($data);
    }

    /**
     * Create new user
     */
    private function createUser()
    {
        $userData = [
            'username' => $_POST['username'] ?? '',
            'email' => $_POST['email'] ?? '',
            'password' => $_POST['password'] ?? '',
            'fullname' => $_POST['fullname'] ?? ''
        ];

        $result = $this->model->createUser($userData);
        
        if ($result) {
            $_SESSION['success'] = 'User created successfully';
        } else {
            $_SESSION['error'] = 'Failed to create user';
        }

        header('Location: ' . BASE_URL . '/admin/users');
        exit;
    }

    /**
     * Update existing user
     */
    private function updateUser(int $userId)
    {
        $userData = [
            'username' => $_POST['username'] ?? '',
            'email' => $_POST['email'] ?? '',
            'fullname' => $_POST['fullname'] ?? ''
        ];

        // Only update password if provided
        if (!empty($_POST['password'])) {
            $userData['password'] = $_POST['password'];
        }

        $result = $this->model->updateUser($userId, $userData);
        
        if ($result) {
            $_SESSION['success'] = 'User updated successfully';
        } else {
            $_SESSION['error'] = 'Failed to update user';
        }

        header('Location: ' . BASE_URL . '/admin/users');
        exit;
    }

    /**
     * Delete user
     */
    private function deleteUser(int $userId)
    {
        $result = $this->model->deleteUser($userId);
        
        if ($result) {
            $_SESSION['success'] = 'User deleted successfully';
        } else {
            $_SESSION['error'] = 'Failed to delete user';
        }

        header('Location: ' . BASE_URL . '/admin/users');
        exit;
    }
}










