<?php
namespace Dashusers;

use Common\Bmvc\BaseController;

class Controller extends BaseController {
    protected $model;
    protected $view;

    public function __construct() {
        try {
            $this->model = new Model();
            $this->view = new View();
        } catch (\Exception $e) {
            error_log("Dashusers Controller Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Users listing page
     */
    public function index() {
        if (!$this->isAuthenticated()) {
            header("Location: " . $this->getBaseUrl() . "/dashboard/login");
            exit;
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $search = $_GET['search'] ?? '';
        $role = $_GET['role'] ?? '';
        $status = $_GET['status'] ?? '';
        $limit = 15;
        
        try {
            $users = $this->model->getUsers($page, $limit, $search, $role, $status);
            $totalUsers = $this->model->getUsersCount($search, $role, $status);
            $statistics = $this->model->getUserStatistics();
            
            $pagination = [
                'current_page' => $page,
                'total_pages' => ceil($totalUsers / $limit),
                'total_count' => $totalUsers,
                'per_page' => $limit
            ];

            // Log activity
            $this->logActivity('view_users_list', 'dashusers', null, "Viewed users list - page {$page}");

            $this->view->render('index', [
                'title' => 'Users Management',
                'users' => $users,
                'statistics' => $statistics,
                'pagination' => $pagination,
                'total_count' => $totalUsers
            ]);
        } catch (\Exception $e) {
            error_log("Users index error: " . $e->getMessage());
            $this->view->render('index', [
                'title' => 'Users Management',
                'error' => 'Error loading users list',
                'users' => [],
                'statistics' => [],
                'pagination' => ['current_page' => 1, 'total_pages' => 0],
                'total_count' => 0
            ]);
        }
    }

    /**
     * Create new user form
     */
    public function create() {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            header("Location: " . $this->getBaseUrl() . "/dashboard/login");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->validateUserData($_POST);
            
            if (empty($errors)) {
                $result = $this->model->createUser($_POST);
                
                if ($result['success']) {
                    // Log activity
                    $this->logActivity('create_user', 'dashusers', $result['user_id'], 'Created new user: ' . $_POST['username']);
                    
                    header("Location: " . $this->getBaseUrl() . "/dashusers?success=User created successfully");
                    exit;
                }
                
                $this->view->render('create', [
                    'title' => 'Create New User',
                    'error' => $result['message'],
                    'data' => $_POST
                ]);
                return;
            }
            
            $this->view->render('create', [
                'title' => 'Create New User',
                'errors' => $errors,
                'data' => $_POST
            ]);
            return;
        }

        $this->view->render('create', [
            'title' => 'Create New User'
        ]);
    }

    /**
     * Edit user form
     */
    public function edit() {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            header("Location: " . $this->getBaseUrl() . "/dashboard/login");
            exit;
        }

        // Get ID from URL path
        $pathInfo = $_SERVER['PATH_INFO'] ?? $_SERVER['REQUEST_URI'] ?? '';
        $pathParts = explode('/', trim($pathInfo, '/'));
        $id = null;
        
        // Find ID in path (after /dashusers/edit/)
        foreach ($pathParts as $index => $part) {
            if ($part === 'edit' && isset($pathParts[$index + 1])) {
                $id = $pathParts[$index + 1];
                break;
            }
        }
        
        if (!$id) {
            header("Location: " . $this->getBaseUrl() . "/dashusers?error=User not found");
            exit;
        }

        $user = $this->model->getUserById($id);
        if (!$user) {
            header("Location: " . $this->getBaseUrl() . "/dashusers?error=User not found");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle status change or form update
            if (isset($_POST['action']) && $_POST['action'] === 'change_status') {
                $result = $this->model->updateUser($id, ['status' => $_POST['status']]);
                $message = $result['success'] ? 'User status updated successfully' : $result['message'];
                $param = $result['success'] ? 'success' : 'error';
                header("Location: " . $this->getBaseUrl() . "/dashusers/view/{$id}?{$param}=" . urlencode($message));
                exit;
            }

            $errors = $this->validateUserData($_POST, true, $id);
            
            if (empty($errors)) {
                $result = $this->model->updateUser($id, $_POST);
                
                if ($result['success']) {
                    // Log activity
                    $this->logActivity('update_user', 'dashusers', $id, 'Updated user: ' . $user['username']);
                    
                    header("Location: " . $this->getBaseUrl() . "/dashusers?success=User updated successfully");
                    exit;
                }
                
                $this->view->render('edit', [
                    'title' => 'Edit User',
                    'error' => $result['message'],
                    'user' => array_merge($user, $_POST),
                    'recent_activity' => $this->model->getUserActivity($id, 10)
                ]);
                return;
            }
            
            $this->view->render('edit', [
                'title' => 'Edit User',
                'errors' => $errors,
                'user' => array_merge($user, $_POST),
                'recent_activity' => $this->model->getUserActivity($id, 10)
            ]);
            return;
        }

        $this->view->render('edit', [
            'title' => 'Edit User',
            'user' => $user,
            'recent_activity' => $this->model->getUserActivity($id, 10)
        ]);
    }

    /**
     * Delete user
     */
    public function delete() {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            header("Location: " . $this->getBaseUrl() . "/dashboard/login");
            exit;
        }

        // Get ID from URL path
        $pathInfo = $_SERVER['PATH_INFO'] ?? $_SERVER['REQUEST_URI'] ?? '';
        $pathParts = explode('/', trim($pathInfo, '/'));
        $id = null;
        
        foreach ($pathParts as $index => $part) {
            if ($part === 'delete' && isset($pathParts[$index + 1])) {
                $id = $pathParts[$index + 1];
                break;
            }
        }

        if (!$id) {
            header("Location: " . $this->getBaseUrl() . "/dashusers?error=User not found");
            exit;
        }

        $user = $this->model->getUserById($id);
        if (!$user) {
            header("Location: " . $this->getBaseUrl() . "/dashusers?error=User not found");
            exit;
        }

        $result = $this->model->deleteUser($id);
        
        if ($result['success']) {
            // Log activity
            $this->logActivity('delete_user', 'dashusers', $id, 'Deleted user: ' . $user['username']);
            
            header("Location: " . $this->getBaseUrl() . "/dashusers?success=User deleted successfully");
        } else {
            header("Location: " . $this->getBaseUrl() . "/dashusers?error=" . urlencode($result['message']));
        }
        exit;
    }

    /**
     * View user details
     */
    public function view() {
        if (!$this->isAuthenticated()) {
            header("Location: " . $this->getBaseUrl() . "/dashboard/login");
            exit;
        }

        // Get ID from URL path
        $pathInfo = $_SERVER['PATH_INFO'] ?? $_SERVER['REQUEST_URI'] ?? '';
        $pathParts = explode('/', trim($pathInfo, '/'));
        $id = null;
        
        foreach ($pathParts as $index => $part) {
            if ($part === 'view' && isset($pathParts[$index + 1])) {
                $id = $pathParts[$index + 1];
                break;
            }
        }

        if (!$id) {
            header("Location: " . $this->getBaseUrl() . "/dashusers?error=User not found");
            exit;
        }

        $user = $this->model->getUserById($id);
        if (!$user) {
            header("Location: " . $this->getBaseUrl() . "/dashusers?error=User not found");
            exit;
        }

        $recent_activity = $this->model->getUserActivity($id, 20);

        // Log activity
        $this->logActivity('view_user', 'dashusers', $id, 'Viewed user profile: ' . $user['username']);

        $this->view->render('view', [
            'title' => 'User Profile - ' . $user['username'],
            'user' => $user,
            'recent_activity' => $recent_activity
        ]);
    }

    /**
     * Bulk actions on users
     */
    public function bulk() {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $action = $_POST['action'] ?? '';
        $userIds = $_POST['user_ids'] ?? [];

        if (empty($action) || empty($userIds)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }

        $results = [];
        foreach ($userIds as $userId) {
            switch ($action) {
                case 'activate':
                    $results[] = $this->model->updateUser($userId, ['status' => 'active']);
                    break;
                case 'suspend':
                    $results[] = $this->model->updateUser($userId, ['status' => 'suspended']);
                    break;
                case 'delete':
                    $results[] = $this->model->deleteUser($userId);
                    break;
            }
        }

        $success = array_filter($results, fn($r) => $r['success']);
        $this->logActivity('bulk_action', 'dashusers', null, "Bulk {$action} on " . count($userIds) . ' users');

        header("Location: " . $this->getBaseUrl() . "/dashusers?success=" . count($success) . " users processed");
        exit;
    }

    /**
     * Export users
     */
    public function export() {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            header("Location: " . $this->getBaseUrl() . "/dashboard/login");
            exit;
        }

        $format = $_GET['format'] ?? 'csv';
        $users = $this->model->getUsers(1, 10000); // Get all users

        if ($format === 'csv') {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="users_' . date('Y-m-d') . '.csv"');
            
            $output = fopen('php://output', 'w');
            fputcsv($output, ['ID', 'Username', 'Email', 'First Name', 'Last Name', 'Role', 'Status', 'Created']);
            
            foreach ($users as $user) {
                fputcsv($output, [
                    $user['id'],
                    $user['username'],
                    $user['email'],
                    $user['first_name'],
                    $user['last_name'],
                    $user['role'],
                    $user['status'],
                    $user['created_at']
                ]);
            }
            
            fclose($output);
            exit;
        }
    }

    // Helper methods
    private function isAuthenticated() {
        return isset($_SESSION['dashboard_authenticated']) && $_SESSION['dashboard_authenticated'] === true;
    }

    private function isAdmin() {
        return $this->isAuthenticated() && 
               isset($_SESSION['dashboard_user']['role']) && 
               in_array($_SESSION['dashboard_user']['role'], ['admin', 'super_admin']);
    }

    private function getCurrentUser() {
        return $_SESSION['dashboard_user'] ?? null;
    }

    private function getBaseUrl() {
        return \upMVC\Config::DOMAIN_NAME . \upMVC\Config::SITE_PATH;
    }

    private function logActivity($action, $module, $recordId = null, $description = null) {
        if ($this->isAuthenticated() && isset($_SESSION['dashboard_user']['id'])) {
            $this->model->logActivity($_SESSION['dashboard_user']['id'], $action, $module, $recordId, $description);
        }
    }

    private function validateUserData($data, $isEdit = false, $currentUserId = null) {
        $errors = [];

        // Username validation
        if (empty($data['username'])) {
            $errors['username'] = 'Username is required';
        } elseif (strlen($data['username']) < 3) {
            $errors['username'] = 'Username must be at least 3 characters';
        }

        // Email validation
        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }

        // Password validation (required for new users, optional for edits)
        if (!$isEdit && empty($data['password'])) {
            $errors['password'] = 'Password is required';
        } elseif (!empty($data['password']) && strlen($data['password']) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        } elseif (!empty($data['password']) && !empty($data['confirm_password']) && $data['password'] !== $data['confirm_password']) {
            $errors['confirm_password'] = 'Passwords do not match';
        }

        return $errors;
    }
}