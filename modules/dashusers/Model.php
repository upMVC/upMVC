<?php
namespace Dashusers;

use Common\Bmvc\BaseModel;
use PDO;

class Model extends BaseModel {
    protected $table = 'dash_users';
    
    public function __construct() {
        parent::__construct();
    }

    /**
     * Get users with pagination and filters
     */
    public function getUsers($page = 1, $limit = 15, $search = '', $role = '', $status = '') {
        $offset = ($page - 1) * $limit;
        $where = [];
        $params = [];

        if (!empty($search)) {
            $where[] = "(username LIKE ? OR email LIKE ? OR first_name LIKE ? OR last_name LIKE ?)";
            $searchTerm = "%$search%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }

        if (!empty($role)) {
            $where[] = "role = ?";
            $params[] = $role;
        }

        if (!empty($status)) {
            $where[] = "status = ?";
            $params[] = $status;
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $sql = "SELECT * FROM {$this->table} {$whereClause} ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Get users error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get total users count with filters
     */
    public function getUsersCount($search = '', $role = '', $status = '') {
        $where = [];
        $params = [];

        if (!empty($search)) {
            $where[] = "(username LIKE ? OR email LIKE ? OR first_name LIKE ? OR last_name LIKE ?)";
            $searchTerm = "%$search%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }

        if (!empty($role)) {
            $where[] = "role = ?";
            $params[] = $role;
        }

        if (!empty($status)) {
            $where[] = "status = ?";
            $params[] = $status;
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $sql = "SELECT COUNT(*) FROM {$this->table} {$whereClause}";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn();
        } catch (\Exception $e) {
            error_log("Get users count error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Create new user
     */
    public function createUser($data) {
        try {
            // Validate required fields
            if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
                return ['success' => false, 'message' => 'Username, email and password are required'];
            }

            // Check if username or email already exists
            $stmt = $this->conn->prepare("SELECT id FROM {$this->table} WHERE username = ? OR email = ?");
            $stmt->execute([$data['username'], $data['email']]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Username or email already exists'];
            }

            // Hash password
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Set defaults
            $data['role'] = $data['role'] ?? 'user';
            $data['status'] = $data['status'] ?? 'active';
            $data['first_name'] = $data['first_name'] ?? '';
            $data['last_name'] = $data['last_name'] ?? '';

            $columns = ['username', 'email', 'password', 'first_name', 'last_name', 'role', 'status'];
            $placeholders = array_fill(0, count($columns), '?');
            $values = array_map(fn($col) => $data[$col], $columns);
            
            $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($values);
            
            return ['success' => true, 'user_id' => $this->conn->lastInsertId()];
        } catch (\Exception $e) {
            error_log("Create user error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred'];
        }
    }

    /**
     * Get user by ID
     */
    public function getUserById($id) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Get user by ID error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update user
     */
    public function updateUser($id, $data) {
        try {
            // Remove empty password
            if (isset($data['password']) && empty($data['password'])) {
                unset($data['password']);
            } elseif (isset($data['password'])) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }

            // Check if username or email exists for another user
            if (isset($data['username']) || isset($data['email'])) {
                $checkSql = "SELECT id FROM {$this->table} WHERE (username = ? OR email = ?) AND id != ?";
                $stmt = $this->conn->prepare($checkSql);
                $stmt->execute([$data['username'] ?? '', $data['email'] ?? '', $id]);
                if ($stmt->fetch()) {
                    return ['success' => false, 'message' => 'Username or email already exists'];
                }
            }

            $updates = [];
            $values = [];
            foreach ($data as $key => $value) {
                if (in_array($key, ['username', 'email', 'password', 'first_name', 'last_name', 'role', 'status', 'bio', 'phone'])) {
                    $updates[] = "$key = ?";
                    $values[] = $value;
                }
            }
            
            if (empty($updates)) {
                return ['success' => false, 'message' => 'No valid fields to update'];
            }

            $values[] = $id;
            $sql = "UPDATE {$this->table} SET " . implode(', ', $updates) . " WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($values);
            
            return ['success' => true];
        } catch (\Exception $e) {
            error_log("Update user error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred'];
        }
    }

    /**
     * Delete user
     */
    public function deleteUser($id) {
        try {
            // Check if user exists
            $stmt = $this->conn->prepare("SELECT role FROM {$this->table} WHERE id = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return ['success' => false, 'message' => 'User not found'];
            }

            // Prevent deleting the last super admin
            if ($user['role'] === 'super_admin') {
                $stmt = $this->conn->prepare("SELECT COUNT(*) FROM {$this->table} WHERE role = 'super_admin'");
                $stmt->execute();
                if ($stmt->fetchColumn() <= 1) {
                    return ['success' => false, 'message' => 'Cannot delete the last super admin'];
                }
            }

            $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = ?");
            $stmt->execute([$id]);
            
            return ['success' => true];
        } catch (\Exception $e) {
            error_log("Delete user error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred'];
        }
    }

    /**
     * Get user activity
     */
    public function getUserActivity($userId, $limit = 20) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM dash_activity_log WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
            $stmt->execute([$userId, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Get user activity error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Log activity
     */
    public function logActivity($userId, $action, $module, $recordId = null, $description = null) {
        try {
            $stmt = $this->conn->prepare("INSERT INTO dash_activity_log (user_id, action, module, record_id, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $userId, 
                $action, 
                $module, 
                $recordId, 
                $description,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
        } catch (\Exception $e) {
            // Activity log table might not exist yet, ignore error
            error_log("Activity log error: " . $e->getMessage());
        }
    }

    /**
     * Get user statistics
     */
    public function getUserStatistics() {
        try {
            $stats = [];
            
            // Total users by role
            $stmt = $this->conn->prepare("SELECT role, COUNT(*) as count FROM {$this->table} GROUP BY role");
            $stmt->execute();
            $stats['by_role'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            // Total users by status
            $stmt = $this->conn->prepare("SELECT status, COUNT(*) as count FROM {$this->table} GROUP BY status");
            $stmt->execute();
            $stats['by_status'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            // Recent registrations (last 30 days)
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM {$this->table} WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
            $stmt->execute();
            $stats['recent_registrations'] = $stmt->fetchColumn();
            
            return $stats;
        } catch (\Exception $e) {
            error_log("Get user statistics error: " . $e->getMessage());
            return [];
        }
    }
}