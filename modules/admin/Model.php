<?php
/*
 * Admin Module - Model
 * Handles user CRUD operations
 */

namespace Admin;

use Common\Bmvc\BaseModel;

class Model extends BaseModel
{
    private string $table = 'usernou';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get all users
     */
    public function getAllUsers(): array
    {
        return $this->readAll($this->table);
    }

    /**
     * Get user by ID
     */
    public function getUserById(int $id): ?array
    {
        $user = $this->read($id, $this->table);
        return $user ?: null;
    }

    /**
     * Create new user
     */
    public function createUser(array $data): int|false
    {
        // Hash password if provided
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        return $this->create($data, $this->table);
    }

    /**
     * Update user
     */
    public function updateUser(int $id, array $data): bool
    {
        // Hash password only if provided and not empty
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']);
        }
        
        return $this->update($id, $data, $this->table);
    }

    /**
     * Delete user
     */
    public function deleteUser(int $id): bool
    {
        return $this->delete($id, $this->table);
    }

    /**
     * Get user count for dashboard stats
     */
    public function getUserCount(): int
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM {$this->table}");
        $stmt->execute();
        $result = $stmt->fetch();
        return (int) $result['count'];
    }
}
