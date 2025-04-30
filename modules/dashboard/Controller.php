<?php
namespace Dashboard;

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
            error_log("Controller constructor - Initial settings: " . print_r($settings, true));
            
            if (empty($settings) || !isset($settings['theme'])) {
                error_log("No settings found in database, initializing defaults");
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
                error_log("Settings after initialization: " . print_r($settings, true));
            }
            
            $this->view->addGlobal('settings', $settings);
            error_log("Settings added to view globals: " . print_r($settings, true));
            
        } catch (\Exception $e) {
            error_log("Error in Controller constructor: " . $e->getMessage());
            throw $e;
        }
    }

    public function index() {
        if (!$this->isAuthenticated()) {
            header('Location: '.BASE_URL.'/dashboard/login');
            exit;
        }
        $this->view->render('dashboard', [
            'title' => 'Dashboard',
            'user' => $_SESSION['user'] ?? null
        ]);
    }

    public function login() {
        if ($this->isAuthenticated()) {
            header('Location: '.BASE_URL.'/dashboard');
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
                header('Location: '.BASE_URL.'/dashboard');
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
        header('Location: '.BASE_URL.'/dashboard/login');
        exit;
    }

    public function users() {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            header('Location: '.BASE_URL.'/dashboard/login');
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
            header('Location: '.BASE_URL.'/dashboard/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->model->createUser($_POST);
            if ($result === true) {
                header('Location: '.BASE_URL.'/dashboard/users');
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
            header('Location: '.BASE_URL.'/dashboard/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            unset($_POST['id']); // Remove id from data to be updated
            
            $result = $this->model->updateUser($id, $_POST);
            if ($result === true) {
                header('Location: '.BASE_URL.'/dashboard/users');
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
            header('Location: '.BASE_URL.'/dashboard/login');
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
        
        header('Location: '.BASE_URL.'/dashboard/users');
        exit;
    }

    private function isAuthenticated() {
        return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
    }

    private function isAdmin() {
        return isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin';
    }

    public function settings() {
        if (!$this->isAuthenticated() || !$this->isAdmin()) {
            header('Location: '.BASE_URL.'/dashboard/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->model->updateSettings($_POST);
            if ($result === true) {
                header('Location: '.BASE_URL.'/dashboard/settings');
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
}
