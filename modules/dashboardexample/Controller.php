<?php
namespace Dashboardexample;

use Common\Bmvc\BaseController;

class Controller extends BaseController {
    protected $model;
    protected $view;

    public function __construct() {
        try {
            $this->model = new Model();
            $this->view = new View();
            
            // Get all settings for views
            $settings = $this->model->getSettings();
            
            if (empty($settings) || !isset($settings['theme'])) {
                // Initialize default settings in database
                $defaults = [
                    'theme' => 'light',
                    'site_name' => 'Dashboard',
                    'items_per_page' => '10',
                    'maintenance_mode' => 'false'
                ];
                
                foreach ($defaults as $key => $value) {
                    $stmt = $this->model->db->prepare("INSERT OR REPLACE INTO dashboard_settings (setting_key, setting_value) VALUES (?, ?)");
                    $stmt->execute([$key, $value]);
                }
                
                // Reload settings after initialization
                $settings = $this->model->getSettings();
            }
            
            $this->view->addGlobal('settings', $settings);
            
        } catch (\Exception $e) {
            error_log("CRITICAL ERROR in Dashboard Controller constructor: " . $e->getMessage());
            throw $e;
        }
    }

    public function index() {
        if (!$this->isAuthenticated()) {
            header('Location: ' . $this->getBaseUrl() . '/dashboardexample/login');
            exit;
        }

        // Get dashboard statistics
        $stats = $this->getDashboardStats();
        
        // Get recent activity
        $recentActivity = $this->getRecentActivity();
        
        // Get dashboard widgets configuration
        $widgets = $this->getActiveWidgets();

        $this->view->render('dashboard', [
            'title' => 'Dashboard - Control Panel',
            'user' => $_SESSION['user'] ?? null,
            'stats' => $stats,
            'recent_activity' => $recentActivity,
            'widgets' => $widgets,
            'modules' => [
                'users' => [
                    'title' => 'User Management',
                    'description' => 'Manage users, roles, and permissions',
                    'url' => $this->getBaseUrl() . '/dashusers',
                    'icon' => 'users',
                    'color' => 'primary',
                    'count' => $stats['users']['total_users'] ?? 0
                ],
                'blog' => [
                    'title' => 'Blog & News',
                    'description' => 'Create and manage blog posts, categories',
                    'url' => $this->getBaseUrl() . '/dashblog',
                    'icon' => 'edit',
                    'color' => 'success',
                    'count' => $stats['blog']['total_posts'] ?? 0
                ],
                'pages' => [
                    'title' => 'Pages Management',
                    'description' => 'Manage static pages and content',
                    'url' => $this->getBaseUrl() . '/dashpages',
                    'icon' => 'file-text',
                    'color' => 'info',
                    'count' => $stats['pages']['total_pages'] ?? 0
                ],
                'media' => [
                    'title' => 'Media Library',
                    'description' => 'Upload and manage files and images',
                    'url' => $this->getBaseUrl() . '/dashmedia',
                    'icon' => 'image',
                    'color' => 'warning',
                    'count' => $stats['media']['total_files'] ?? 0
                ]
            ]
        ]);
    }

    public function login() {
        if ($this->isAuthenticated()) {
            header('Location: ' . $this->getBaseUrl() . '/dashboardexample');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            $result = $this->model->validateLogin($email, $password);
            
            // If result is array, it's a valid user
            if (is_array($result)) {
                $_SESSION['user'] = $result;
                $_SESSION['authenticated'] = true;
                header('Location: ' . $this->getBaseUrl() . '/dashboardexample');
                exit;
            }
            
            // If result is string, it's an error message
            $this->view->render('login', [
                'error' => $result, // Show the specific error message
                'email' => $email
            ]);
            return;
        }
        
        $this->view->render('login', [
            'title' => 'Login'
        ]);
    }

    public function logout() {
        session_destroy();
        header('Location: '.\BASE_URL.'/dashboardexample/login');
        exit;
    }

    public function users() {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            header('Location: '.\BASE_URL.'/dashboardexample/login');
            exit;
        }
        
        $users = $this->model->getUsers();
        $this->view->render('users', [
            'title' => 'User Management',
            'users' => $users
        ]);
    }

    public function addUser() {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            header('Location: '.\BASE_URL.'/dashboardexample/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->model->createUser($_POST);
            if ($result === true) {
                header('Location: '.\BASE_URL.'/dashboardexample/users');
                exit;
            }
            // If there was an error, show it
            $this->view->render('users', [
                'title' => 'User Management',
                'error' => $result,
                'users' => $this->model->getUsers()
            ]);
            return;
        }
    }

    public function editUser() {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            header('Location: '.\BASE_URL.'/dashboardexample/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            unset($_POST['id']); // Remove id from data to be updated
            
            $result = $this->model->updateUser($id, $_POST);
            if ($result === true) {
                header('Location: '.\BASE_URL.'/dashboardexample/users');
                exit;
            }
            // If there was an error, show it
            $this->view->render('users', [
                'title' => 'User Management',
                'error' => $result,
                'users' => $this->model->getUsers()
            ]);
            return;
        }
    }

    public function deleteUser($params) {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            header('Location: '.\BASE_URL.'/dashboardexample/login');
            exit;
        }


        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $id = $_GET['userid'] ?? null;
        ///echo $id;
        //unset($_GET['userid']); // Remove id from data to be updated
        //$id = $params['userid'] ?? null;
        if ($id) {
            $result = $this->model->deleteUser($id);
            if ($result !== true) {
                // If there was an error, show it
                $this->view->render('users', [
                    'title' => 'User Management',
                    'error' => $result,
                    'users' => $this->model->getUsers()
                ]);
                return;
            }
        }
      }
        
        header('Location: '.\BASE_URL.'/dashboardexample/users');
        exit;
    }

    private function isAuthenticated() {
        return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
    }

    private function isAdmin() {
        return isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin';
    }

    private function getBaseUrl() {
        return defined('BASE_URL') ? BASE_URL : 'http://localhost/upMVC';
    }

    public function settings() {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            header('Location: '.\BASE_URL.'/dashboardexample/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->model->updateSettings($_POST);
            if ($result === true) {
                header('Location: '.\BASE_URL.'/dashboardexample/settings');
                exit;
            }
            // If there was an error, show it
            $this->view->render('settings', [
                'title' => 'Settings',
                'error' => $result,
                'settings' => $this->model->getSettings()
            ]);
            return;
        }

        $this->view->render('settings', [
            'title' => 'Settings',
            'settings' => $this->model->getSettings()
        ]);
    }

    /**
     * Get comprehensive dashboard statistics
     */
    private function getDashboardStats() {
        try {
            $stats = [];
            
            // Check if tables exist first
            $db = $this->model->getConnection();
            
            // User statistics
            try {
                $stmt = $db->prepare("SELECT 
                    COUNT(*) as total_users,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_users,
                    SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as new_today
                    FROM dash_users");
                $stmt->execute();
                $stats['users'] = $stmt->fetch(\PDO::FETCH_ASSOC);
            } catch (\Exception $e) {
                $stats['users'] = ['total_users' => 0, 'active_users' => 0, 'new_today' => 0];
            }
            
            // Blog statistics
            try {
                $stmt = $db->prepare("SELECT 
                    COUNT(*) as total_posts,
                    SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published_posts,
                    SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as new_today
                    FROM dash_blog_posts");
                $stmt->execute();
                $stats['blog'] = $stmt->fetch(\PDO::FETCH_ASSOC);
            } catch (\Exception $e) {
                $stats['blog'] = ['total_posts' => 0, 'published_posts' => 0, 'new_today' => 0];
            }
            
            // Pages statistics
            try {
                $stmt = $db->prepare("SELECT 
                    COUNT(*) as total_pages,
                    SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published_pages,
                    SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as new_today
                    FROM dash_pages");
                $stmt->execute();
                $stats['pages'] = $stmt->fetch(\PDO::FETCH_ASSOC);
            } catch (\Exception $e) {
                $stats['pages'] = ['total_pages' => 0, 'published_pages' => 0, 'new_today' => 0];
            }
            
            // Comments statistics
            try {
                $stmt = $db->prepare("SELECT 
                    COUNT(*) as total_comments,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_comments
                    FROM dash_blog_comments");
                $stmt->execute();
                $stats['comments'] = $stmt->fetch(\PDO::FETCH_ASSOC);
            } catch (\Exception $e) {
                $stats['comments'] = ['total_comments' => 0, 'pending_comments' => 0];
            }

            // Media statistics
            try {
                $stmt = $db->prepare("SELECT 
                    COUNT(*) as total_files,
                    SUM(file_size) as total_size,
                    SUM(CASE WHEN file_type = 'image' THEN 1 ELSE 0 END) as images
                    FROM dash_media");
                $stmt->execute();
                $stats['media'] = $stmt->fetch(\PDO::FETCH_ASSOC);
            } catch (\Exception $e) {
                $stats['media'] = ['total_files' => 0, 'total_size' => 0, 'images' => 0];
            }
            
            return $stats;
            
        } catch (\Exception $e) {
            error_log("Dashboard stats error: " . $e->getMessage());
            return [
                'users' => ['total_users' => 0, 'active_users' => 0, 'new_today' => 0],
                'blog' => ['total_posts' => 0, 'published_posts' => 0, 'new_today' => 0],
                'pages' => ['total_pages' => 0, 'published_pages' => 0, 'new_today' => 0],
                'comments' => ['total_comments' => 0, 'pending_comments' => 0],
                'media' => ['total_files' => 0, 'total_size' => 0, 'images' => 0]
            ];
        }
    }

    /**
     * Get recent activity log
     */
    private function getRecentActivity($limit = 10) {
        try {
            $db = $this->model->getConnection();
            $stmt = $db->prepare("SELECT 
                al.*, 
                u.username, 
                u.first_name, 
                u.last_name
                FROM dash_activity_log al
                LEFT JOIN dash_users u ON al.user_id = u.id
                ORDER BY al.created_at DESC 
                LIMIT :limit");
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\Exception $e) {
            error_log("Recent activity error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get active dashboard widgets
     */
    private function getActiveWidgets() {
        try {
            $db = $this->model->getConnection();
            $stmt = $db->prepare("SELECT * FROM dash_widgets 
                WHERE is_active = 1 
                ORDER BY position, sort_order");
            $stmt->execute();
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\Exception $e) {
            error_log("Widgets error: " . $e->getMessage());
            return [];
        }
    }
}
