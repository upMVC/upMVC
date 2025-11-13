<?php
/**
 * Backup copy of previous Controller (with cache invalidation & regex route handling).
 * Created to preserve original behavior before migrating to parameterized routing.
 * Date: 2025-11-08
 */

namespace App\Modules\Admin;

use App\Modules\Admin\View;
use App\Modules\Admin\Model;
use App\Modules\Admin\Routes\Routes;

class Controllerc
{
    private Model $model;
    private View $view;

    public function __construct()
    {
        $this->model = new Model();
        $this->view = new View();
    }

    public function display($reqRoute, $reqMet)
    {
        if (!isset($_SESSION["logged"]) || $_SESSION["logged"] !== true) {
            header('Location: ' . BASE_URL . '/auth');
            exit;
        }
        $this->handleRoute($reqRoute, $reqMet);
    }

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

    private function dashboard()
    {
        $userCount = $this->model->getUserCount();
        $cacheStats = Routes::getCacheStats();
        $data = [
            'view' => 'dashboard',
            'stats' => [
                'userCount' => $userCount,
                'cache' => $cacheStats
            ]
        ];
        $this->view->render($data);
    }

    private function listUsers()
    {
        $users = $this->model->getAllUsers();
        $cacheStats = Routes::getCacheStats();
        $data = [
            'view' => 'users_list',
            'users' => $users,
            'cache' => $cacheStats
        ];
        $this->view->render($data);
    }

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
            Routes::clearCache();
            $_SESSION['success'] = 'User created successfully (cache cleared)';
        } else {
            $_SESSION['error'] = 'Failed to create user';
        }
        header('Location: ' . BASE_URL . '/admin/users');
        exit;
    }

    private function updateUser(int $userId)
    {
        $userData = [
            'username' => $_POST['username'] ?? '',
            'email' => $_POST['email'] ?? '',
            'fullname' => $_POST['fullname'] ?? ''
        ];
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

    private function deleteUser(int $userId)
    {
        $result = $this->model->deleteUser($userId);
        if ($result) {
            Routes::clearCache();
            $_SESSION['success'] = 'User deleted successfully (cache cleared)';
        } else {
            $_SESSION['error'] = 'Failed to delete user';
        }
        header('Location: ' . BASE_URL . '/admin/users');
        exit;
    }
}











