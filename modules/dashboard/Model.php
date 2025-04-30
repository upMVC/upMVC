<?php
namespace Dashboard;

use Common\Bmvc\BaseModel;
use upMVC\Database;
use PDO;

class Model extends BaseModel {
    //protected $conn;
    protected $table = 'dashboard_users';
    
    public function __construct() {
        parent::__construct();
        //$this->conn = (new Database())->getConnection();
        //$this->initializeDatabase();
    }

    private function initializeDatabase() {
        try {
            // Drop existing tables
            $this->conn->exec("DROP TABLE IF EXISTS dashboard_users;");
            $this->conn->exec("DROP TABLE IF EXISTS dashboard_settings;");
            
            $schema = file_get_contents(__DIR__ . '/sql/schema.sql');
            $statements = array_filter(array_map('trim', explode(';', $schema)));
            
            foreach ($statements as $statement) {
                if (!empty($statement)) {
                    $this->conn->exec($statement);
                }
            }
        } catch (\PDOException $e) {
            die("Error initializing database: " . $e->getMessage());
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
        return $this->where('role', $role)->get();
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
