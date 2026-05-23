<?php

namespace App\Modules\TenantApp;

use App\Common\Bmvc\BaseModel;

class Model extends BaseModel
{
    /**
     * Tenant + plan info in one query.
     */
    public function findByIdWithPlan(int $tenantId): array|false
    {
        $stmt = $this->conn->prepare(
            "SELECT t.id, t.slug, t.name, t.status, t.features, t.created_at,
                    p.id   AS plan_id,
                    p.name AS plan_name,
                    p.price AS plan_price,
                    p.features AS plan_features,
                    p.limits   AS plan_limits
             FROM   tenants t
             LEFT JOIN plans p ON p.id = t.plan_id
             WHERE  t.id = :id AND t.deleted_at IS NULL
             LIMIT  1"
        );
        $stmt->execute([':id' => $tenantId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Look up tenant by slug — used to validate the URL slug matches the session.
     */
    public function findBySlug(string $slug): array|false
    {
        $stmt = $this->conn->prepare(
            "SELECT id, slug, name, status FROM tenants
             WHERE  slug = :slug AND deleted_at IS NULL
             LIMIT  1"
        );
        $stmt->execute([':slug' => strtolower($slug)]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * List users belonging to this tenant.
     */
    public function listUsers(int $tenantId): array
    {
        $stmt = $this->conn->prepare(
            "SELECT id, name, username, email, role, state, stamp
             FROM   users
             WHERE  tenant_id = :tenant_id
             ORDER  BY role DESC, name ASC"
        );
        $stmt->execute([':tenant_id' => $tenantId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function countUsers(int $tenantId): int
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM users WHERE tenant_id = :tenant_id"
        );
        $stmt->execute([':tenant_id' => $tenantId]);
        return (int) $stmt->fetchColumn();
    }
}
