<?php
/**
 * Admin Module Controller - ROUTER V2 ENHANCED
 * 
 * This version demonstrates Router v2.0 controller integration:
 * - Type-safe params: $_GET['id'] is already int (no casting needed)
 * - Simplified validation: Router rejects invalid IDs before controller
 * - Named route usage: route() global function for URL generation
 * 
 * ROUTER V2 BENEFITS IN CONTROLLER:
 * ✅ No manual type casting: $_GET['id'] is already int
 * ✅ No regex validation: Router validated before reaching here
 * ✅ Cleaner code: Less boilerplate, more business logic
 * ✅ Global helpers: route(), url(), redirect() - clean procedural API
 * 
 * COMPARISON WITH OTHER IMPLEMENTATIONS:
 * - Controllerc.php: Cache-based with regex route matching
 * - Controllerd.php: Basic param with manual validation
 * - Controller.php: THIS FILE - Router V2 enhanced (cleanest)
 * 
 * @see docs/routing/ROUTER_V2_EXAMPLES.md
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
     * Route handler - Router V2 Enhanced
     * 
     * Notice: No regex matching needed!
     * Router v2.0 already validated and type-cast parameters.
     */
    private function handleRoute($reqRoute, $reqMet)
    {
        // Static routes first
        switch ($reqRoute) {
            case '/admin':
                $this->dashboard();
                return;

            case '/admin/users':
                $this->listUsers();
                return;

            case '/admin/users/add':
                if ($reqMet === 'POST') {
                    $this->createUser();
                } else {
                    $this->showUserForm();
                }
                return;
        }

        // ========================================
        // Parameterized Routes - Router V2 Enhanced
        // ========================================
        
        // Edit User Route
        // Router V2 benefits:
        // - $_GET['id'] is already int (no casting)
        // - '\d+' validation already passed (no ctype_digit check)
        // - Invalid IDs rejected before reaching here
        if (strpos($reqRoute, '/admin/users/edit/') === 0) {
            $userId = $_GET['id'] ?? null;  // Already int from Router V2!
            
            // Simplified null check (type validation already done by router)
            if ($userId === null) {
                $this->view->render(['view' => 'error', 'message' => 'Invalid user ID']);
                return;
            }
            
            if ($reqMet === 'POST') {
                $this->updateUser($userId);
            } else {
                $this->showUserForm($userId);
            }
            return;
        }

        // Delete User Route
        // Same Router V2 benefits as edit route
        if (strpos($reqRoute, '/admin/users/delete/') === 0) {
            $userId = $_GET['id'] ?? null;  // Already int from Router V2!
            
            if ($userId === null) {
                $this->view->render(['view' => 'error', 'message' => 'Invalid user ID']);
                return;
            }
            
            $this->deleteUser($userId);
            return;
        }

        // Fallback 404
        $this->view->render(['view' => 'error', 'message' => '404 - Page not found']);
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
                'userCount' => $userCount,
                'routingMode' => 'Router V2 Enhanced'
            ]
        ];
        $this->view->render($data);
    }

    /**
     * List all users
     * 
     * Router V2 enhancement: Uses named routes for URL generation
     */
    private function listUsers()
    {
        $users = $this->model->getAllUsers();
        
        // Note: View can use route() for edit/delete URLs
        // Example: route('admin.user.edit', ['id' => $user['id']])
        
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
                
                // Router V2: Use global url() helper
                header('Location: ' . url('/admin/users'));
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
     * Router V2: No cache invalidation needed (no cache!)
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

        // Router V2: Global url() helper
        header('Location: ' . url('/admin/users'));
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

        // Router V2: Global url() helper
        header('Location: ' . url('/admin/users'));
        exit;
    }

    /**
     * Delete user
     * 
     * Router V2: No cache invalidation needed (no cache!)
     */
    private function deleteUser(int $userId)
    {
        $result = $this->model->deleteUser($userId);
        
        if ($result) {
            $_SESSION['success'] = 'User deleted successfully';
        } else {
            $_SESSION['error'] = 'Failed to delete user';
        }

        // Router V2: Global url() helper
        header('Location: ' . url('/admin/users'));
        exit;
    }
}
