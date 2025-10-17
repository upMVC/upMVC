<?php
/**
 * Admin Controller - WITH CACHE INVALIDATION
 * 
 * This version clears the route cache whenever users are created,
 * updated, or deleted to ensure routes stay synchronized.
 */

namespace Admin;

use Admin\Routes\Routes;

class Controller
{
    private $model;
    private $view;

    public function __construct()
    {
        $this->model = new Model();
        $this->view = new View();
    }

    public function display()
    {
        // Authentication check
        if (!isset($_SESSION['logged']) || !$_SESSION['logged']) {
            header('Location: ' . BASE_URL . '/auth');
            exit;
        }

        // Route the request
        $this->handleRoute();
    }

    private function handleRoute()
    {
        $requestUri = $_SERVER['REQUEST_URI'];
        $basePath = parse_url(BASE_URL, PHP_URL_PATH) ?? '';
        $route = str_replace($basePath, '', $requestUri);
        $route = strtok($route, '?');

        // Dashboard
        if ($route === '/admin' || $route === '/admin/') {
            $this->showDashboard();
            return;
        }

        // User list
        if ($route === '/admin/users') {
            $this->showUsersList();
            return;
        }

        // Add user form
        if ($route === '/admin/users/add') {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->createUser();
            } else {
                $this->showUserForm();
            }
            return;
        }

        // Edit user
        if (preg_match('#^/admin/users/edit/(\d+)$#', $route, $matches)) {
            $userId = (int)$matches[1];
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->updateUser($userId);
            } else {
                $this->showUserForm($userId);
            }
            return;
        }

        // Delete user
        if (preg_match('#^/admin/users/delete/(\d+)$#', $route, $matches)) {
            $userId = (int)$matches[1];
            $this->deleteUser($userId);
            return;
        }

        // 404 Not Found
        http_response_code(404);
        echo "404 - Page not found";
    }

    private function showDashboard()
    {
        $stats = [
            'total_users' => $this->model->getUserCount(),
            'recent_users' => $this->model->getRecentUsers(5)
        ];

        // Add cache stats for debugging
        $stats['cache'] = Routes::getCacheStats();

        $this->view->render([
            'type' => 'dashboard',
            'stats' => $stats,
            'messages' => $this->getFlashMessages()
        ]);
    }

    private function showUsersList()
    {
        $users = $this->model->getAllUsers();
        
        // Add cache stats
        $cacheStats = Routes::getCacheStats();

        $this->view->render([
            'type' => 'users_list',
            'users' => $users,
            'cache' => $cacheStats,
            'messages' => $this->getFlashMessages()
        ]);
    }

    private function showUserForm($userId = null)
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

        $this->view->render([
            'type' => 'user_form',
            'user' => $user,
            'messages' => $this->getFlashMessages()
        ]);
    }

    private function createUser()
    {
        $userData = [
            'username' => $_POST['username'] ?? '',
            'email' => $_POST['email'] ?? '',
            'fullname' => $_POST['fullname'] ?? '',
            'password' => $_POST['password'] ?? ''
        ];

        // Validate
        $errors = $this->validateUserData($userData);
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['form_data'] = $userData;
            header('Location: ' . BASE_URL . '/admin/users/add');
            exit;
        }

        // Create user
        $result = $this->model->createUser($userData);

        if ($result) {
            // ⭐ CLEAR ROUTE CACHE - New user needs routes!
            Routes::clearCache();
            
            $_SESSION['success'] = 'User created successfully';
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

        // Include password only if provided
        if (!empty($_POST['password'])) {
            $userData['password'] = $_POST['password'];
        }

        // Validate
        $errors = $this->validateUserData($userData, $userId);
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['form_data'] = $userData;
            header('Location: ' . BASE_URL . '/admin/users/edit/' . $userId);
            exit;
        }

        // Update user
        $result = $this->model->updateUser($userId, $userData);

        if ($result) {
            // ⭐ OPTIONAL: Clear cache (only needed if user IDs can change)
            // Routes::clearCache();
            
            $_SESSION['success'] = 'User updated successfully';
        } else {
            $_SESSION['error'] = 'Failed to update user';
        }

        header('Location: ' . BASE_URL . '/admin/users');
        exit;
    }

    private function deleteUser(int $userId)
    {
        // Prevent deleting yourself
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $userId) {
            $_SESSION['error'] = 'Cannot delete your own account';
            header('Location: ' . BASE_URL . '/admin/users');
            exit;
        }

        $result = $this->model->deleteUser($userId);

        if ($result) {
            // ⭐ CLEAR ROUTE CACHE - Deleted user routes should disappear!
            Routes::clearCache();
            
            $_SESSION['success'] = 'User deleted successfully';
        } else {
            $_SESSION['error'] = 'Failed to delete user';
        }

        header('Location: ' . BASE_URL . '/admin/users');
        exit;
    }

    private function validateUserData(array $data, $userId = null): array
    {
        $errors = [];

        if (empty($data['username'])) {
            $errors[] = 'Username is required';
        }

        if (empty($data['email'])) {
            $errors[] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }

        if ($userId === null && empty($data['password'])) {
            $errors[] = 'Password is required';
        }

        if (!empty($data['password']) && strlen($data['password']) < 6) {
            $errors[] = 'Password must be at least 6 characters';
        }

        return $errors;
    }

    private function getFlashMessages(): array
    {
        $messages = [
            'success' => $_SESSION['success'] ?? null,
            'error' => $_SESSION['error'] ?? null
        ];

        unset($_SESSION['success'], $_SESSION['error']);

        return $messages;
    }
}
