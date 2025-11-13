<?php
namespace App\Modules\dashboardexample;

use App\Common\Bmvc\BaseModel;
use App\Etc\Database;
use PDO;

class Model extends BaseModel {
    //protected $conn;
    protected $table = 'dashboard_users';
    
    // Expose db property for Controller access
    public $db;
    
    public function __construct() {
        parent::__construct();
        // Expose the connection as db property for Controller access
        $this->db = $this->conn;
        // Only initialize database if tables don't exist
        $this->ensureTablesExist();
    }

    private function ensureTablesExist() {
        try {
            // Check if dashboard_users table exists (MySQL compatible)
            $stmt = $this->conn->prepare("SHOW TABLES LIKE 'dashboard_users'");
            $stmt->execute();
            
            if (!$stmt->fetch()) {
                error_log("Dashboard tables not found, creating them...");
                
                // Create dashboard_users table (MySQL compatible)
                $this->conn->exec("
                    CREATE TABLE IF NOT EXISTS dashboard_users (
                        id INT PRIMARY KEY AUTO_INCREMENT,
                        name VARCHAR(255) NOT NULL,
                        email VARCHAR(255) NOT NULL UNIQUE,
                        password VARCHAR(255) NOT NULL,
                        role ENUM('admin', 'user') DEFAULT 'user',
                        last_login DATETIME,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
                
                // Create dashboard_settings table (MySQL compatible)
                $this->conn->exec("
                    CREATE TABLE IF NOT EXISTS dashboard_settings (
                        id INT PRIMARY KEY AUTO_INCREMENT,
                        setting_key VARCHAR(255) NOT NULL UNIQUE,
                        setting_value TEXT,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
                
                // Create default admin user (password: admin123)
                $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
                $stmt = $this->conn->prepare("INSERT IGNORE INTO dashboard_users (name, email, password, role) VALUES (?, ?, ?, ?)");
                $stmt->execute(['Admin', 'admin@example.com', $hashedPassword, 'admin']);
                
                // Insert default settings
                $defaultSettings = [
                    'site_name' => 'Dashboard',
                    'theme' => 'light',
                    'items_per_page' => '10',
                    'maintenance_mode' => 'false'
                ];
                
                foreach ($defaultSettings as $key => $value) {
                    $stmt = $this->conn->prepare("INSERT IGNORE INTO dashboard_settings (setting_key, setting_value) VALUES (?, ?)");
                    $stmt->execute([$key, $value]);
                }
                
                error_log("Dashboard tables created successfully");
            } else {
                error_log("Dashboard tables already exist");
            }
        } catch (\PDOException $e) {
            error_log("Error initializing dashboard database: " . $e->getMessage());
            // Don't die, just log the error - let the application continue
        }
    }

   /**
     * Create a new user
     * 
     * @param array $data User data
     * @return bool|string True on success, error message on failure
     */
    public function createUser($data) {
        try {
            if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
                return "Name, email and password are required";
            }

            // Check if email already exists
            $stmt = $this->conn->prepare("SELECT id FROM {$this->table} WHERE email = ?");
            $stmt->execute([$data['email']]);
            if ($stmt->fetch()) {
                return "Email already exists";
            }

            // Hash password
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Set default role if not provided
            if (!isset($data['role'])) {
                $data['role'] = 'user';
            }

            $columns = implode(', ', array_keys($data));
            $values = implode(', ', array_fill(0, count($data), '?'));
            
            $sql = "INSERT INTO {$this->table} ($columns) VALUES ($values)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(array_values($data));
            
            return true;
        } catch (\PDOException $e) {
            error_log("Error creating user: " . $e->getMessage());
            return "Database error occurred";
        }
    }

    /**
     * Validate user login credentials with detailed error reporting
     * 
     * @param string $email User's email
     * @param string $password Plain text password to verify
     * @return array|string User data if valid, error message string if invalid
     */
    public function validateLogin($email, $password) {
        if (empty($email)) {
            return "Email is required";
        }
        if (empty($password)) {
            return "Password is required";
        }

        try {
            $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return "User not found";
            }

            if (!password_verify($password, $user['password'])) {
                return "Invalid password";
            }

            // Login successful, update last login time
            $updateStmt = $this->conn->prepare("UPDATE {$this->table} SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
            $updateStmt->execute([$user['id']]);
            
            return $user;
        } catch (\PDOException $e) {
            error_log("Database error in validateLogin: " . $e->getMessage());
            return "Database error occurred";
        }
    }

     /**
     * Get all users with optional filtering and sorting
     * 
     * @param array $filters Optional filters
     * @param string $orderBy Column to order by
     * @param string $order Order direction (ASC/DESC)
     * @return array
     */
    public function getUsers($filters = [], $orderBy = 'created_at', $order = 'DESC') {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];

        if (!empty($filters)) {
            $whereClauses = [];
            foreach ($filters as $key => $value) {
                $whereClauses[] = "$key = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        $sql .= " ORDER BY $orderBy $order";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get user by role
     * 
     * @param string $role Role to filter by
     * @return array
     */
    public function getUserByRole($role) {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE role = ?");
        $stmt->execute([$role]);
        return $stmt->fetchAll();
    }

    /**
     * Get user by ID
     * 
     * @param int $id User ID
     * @return array|false
     */
    public function getUserById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Update user
     * 
     * @param int $id User ID
     * @param array $data User data to update
     * @return bool|string True on success, error message on failure
     */
    public function updateUser($id, $data) {
        try {
            // Remove password if it's empty (no password change)
            if (isset($data['password']) && empty($data['password'])) {
                unset($data['password']);
            } elseif (isset($data['password'])) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }

            // Check if email exists for another user
            if (isset($data['email'])) {
                $stmt = $this->conn->prepare("SELECT id FROM {$this->table} WHERE email = ? AND id != ?");
                $stmt->execute([$data['email'], $id]);
                if ($stmt->fetch()) {
                    return "Email already exists";
                }
            }

            $updates = [];
            foreach ($data as $key => $value) {
                $updates[] = "$key = ?";
            }
            
            $sql = "UPDATE {$this->table} SET " . implode(', ', $updates) . " WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            
            $values = array_values($data);
            $values[] = $id;
            
            $stmt->execute($values);
            return true;
        } catch (\PDOException $e) {
            error_log("Error updating user: " . $e->getMessage());
            return "Database error occurred";
        }
    }

    /**
     * Delete user
     * 
     * @param int $id User ID
     * @return bool|string True on success, error message on failure
     */
    public function deleteUser($id) {
        try {
            // Check if user exists
            $stmt = $this->conn->prepare("SELECT role FROM {$this->table} WHERE id = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return "User not found";
            }

            // Prevent deleting the last admin
            if ($user['role'] === 'admin') {
                $stmt = $this->conn->prepare("SELECT COUNT(*) FROM {$this->table} WHERE role = 'admin'");
                $stmt->execute();
                if ($stmt->fetchColumn() <= 1) {
                    return "Cannot delete the last admin user";
                }
            }

            $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = ?");
            $stmt->execute([$id]);
            return true;
        } catch (\PDOException $e) {
            error_log("Error deleting user: " . $e->getMessage());
            return "Database error occurred";
        }
    }


    public function getAllUsers()
    {
        return $this->readAll($this->table);
    }

     /**
     * Get all settings
     * 
     * @return array
     */
    public function getSettings() {
        try {
            $stmt = $this->conn->prepare("SELECT setting_key, setting_value FROM dashboard_settings");
            $stmt->execute();
            $settings = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
            return $settings;
        } catch (\PDOException $e) {
            error_log("Error getting settings: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get a specific setting value
     * 
     * @param string $key Setting key
     * @return string|null
     */
    public function getSetting($key) {
        try {
            $stmt = $this->conn->prepare("SELECT setting_value FROM dashboard_settings WHERE setting_key = ?");
            $stmt->execute([$key]);
            return $stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log("Error getting setting: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update settings
     * 
     * @param array $settings Array of key-value pairs
     * @return bool|string True on success, error message on failure
     */
    public function updateSettings($settings) {
        try {
            $this->conn->beginTransaction();

            foreach ($settings as $key => $value) {
                $stmt = $this->conn->prepare("UPDATE dashboard_settings SET setting_value = ? WHERE setting_key = ?");
                $stmt->execute([$value, $key]);
            }

            $this->conn->commit();
            return true;
        } catch (\PDOException $e) {
            $this->conn->rollBack();
            error_log("Error updating settings: " . $e->getMessage());
            return "Database error occurred";
        }
    }
}











