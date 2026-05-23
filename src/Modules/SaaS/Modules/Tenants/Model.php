<?php
namespace App\Modules\SaaS\Modules\Tenants;

use App\Common\Bmvc\BaseModel;

class Model extends BaseModel
{
    /**
     * Create a new tenant row.
     * Returns the new tenant ID.
     */
    public function create(array $data): int
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO tenants (slug, name, plan_id, status, features)
             VALUES (:slug, :name, :plan_id, 'trial', :features)"
        );
        $stmt->execute([
            ':slug'     => strtolower(trim(strip_tags($data['slug']))),
            ':name'     => trim(strip_tags($data['name'])),
            ':plan_id'  => (int) ($data['plan_id'] ?? 1),
            ':features' => json_encode($data['features'] ?? []),
        ]);
        return (int) $this->conn->lastInsertId();
    }

    /**
     * Create the first admin user for a newly registered tenant.
     * Password is hashed here — caller passes plaintext.
     * Returns the new user ID.
     */
    public function createOwnerUser(int $tenantId, array $u): int
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO users (tenant_id, name, username, email, password, token, state, role)
             VALUES (:tenant_id, :name, :username, :email, :password, :token, 1, 'tenant_owner')"
        );
        $stmt->execute([
            ':tenant_id' => $tenantId,
            ':name'      => trim(strip_tags($u['name'])),
            ':username'  => trim(strip_tags($u['username'])),
            ':email'     => trim(strip_tags($u['email'])),
            ':password'  => password_hash($u['password'], PASSWORD_BCRYPT),
            ':token'     => bin2hex(random_bytes(16)),
        ]);
        return (int) $this->conn->lastInsertId();
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM tenants WHERE id = :id AND deleted_at IS NULL LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function findBySlug(string $slug): array|false
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM tenants WHERE slug = :slug AND deleted_at IS NULL LIMIT 1"
        );
        $stmt->execute([':slug' => strtolower($slug)]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Partial update — only touches columns present in $data.
     * Allowed: name, plan_id, status, features
     */
    public function update(int $id, array $data): bool
    {
        $allowed = ['name', 'plan_id', 'status', 'features'];
        $sets    = [];
        $params  = [':id' => $id];

        foreach ($allowed as $col) {
            if (array_key_exists($col, $data)) {
                $sets[]        = "$col = :$col";
                $params[":$col"] = ($col === 'features')
                    ? json_encode($data[$col])
                    : $data[$col];
            }
        }

        if (empty($sets)) {
            return false;
        }

        $stmt = $this->conn->prepare(
            "UPDATE tenants SET " . implode(', ', $sets) . " WHERE id = :id"
        );
        return $stmt->execute($params);
    }

    public function softDelete(int $id): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE tenants SET deleted_at = NOW() WHERE id = :id"
        );
        return $stmt->execute([':id' => $id]);
    }
}
