<?php
/*
 * Admin Module - Controller WITH CACHE INVALIDATION
 * 
 * This version clears the route cache whenever users are created or deleted
 * to ensure routes stay synchronized with the database.
 * 
 * HOW TO USE:
 * 1. First install Routes_WITH_CACHE.php (rename to Routes.php)
 * 2. Then rename this file to Controller.php (backup original first!)
 * 3. Cache will be automatically cleared on CRUD operations
 * 
 * WHAT'S DIFFERENT:
 * - Calls Routes::clearCache() after createUser()
 * - Calls Routes::clearCache() after deleteUser()
 * - Shows cache stats in dashboard (optional)
 */

namespace Admin;

use Admin\View;
use Admin\Model;
use Admin\Routes\Routes;

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
     * 
     * NOW INCLUDES CACHE STATISTICS!
     */
    private function dashboard()
    {
        $userCount = $this->model->getUserCount();
        
        // Get cache statistics for monitoring
        $cacheStats = Routes::getCacheStats();
        
        $data = [
            'view' => 'dashboard',
            'stats' => [
                'userCount' => $userCount,
                'cache' => $cacheStats  // Add cache info to dashboard
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
        
        // Optional: Show cache stats in user list too
        $cacheStats = Routes::getCacheStats();
        
        $data = [
            'view' => 'users_list',
            'users' => $users,
            'cache' => $cacheStats
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
     * 
     * ⭐ CLEARS CACHE after creating user
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
            // ⭐ CLEAR ROUTE CACHE - New user needs routes!
            Routes::clearCache();
            
            $_SESSION['success'] = 'User created successfully (cache cleared)';
        } else {
            $_SESSION['error'] = 'Failed to create user';
        }

        header('Location: ' . BASE_URL . '/admin/users');
        exit;
    }

    /**
     * Update existing user
     * 
     * Note: We don't clear cache on update because user ID doesn't change.
     * If your app can change user IDs, uncomment the clearCache() call.
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
            // Optional: Clear cache if user IDs can change
            // Routes::clearCache();
            
            $_SESSION['success'] = 'User updated successfully';
        } else {
            $_SESSION['error'] = 'Failed to update user';
        }

        header('Location: ' . BASE_URL . '/admin/users');
        exit;
    }

    /**
     * Delete user
     * 
     * ⭐ CLEARS CACHE after deleting user
     */
    private function deleteUser(int $userId)
    {
        $result = $this->model->deleteUser($userId);
        
        if ($result) {
            // ⭐ CLEAR ROUTE CACHE - Deleted user routes should disappear!
            Routes::clearCache();
            
            $_SESSION['success'] = 'User deleted successfully (cache cleared)';
        } else {
            $_SESSION['error'] = 'Failed to delete user';
        }

        header('Location: ' . BASE_URL . '/admin/users');
        exit;
    }
}
