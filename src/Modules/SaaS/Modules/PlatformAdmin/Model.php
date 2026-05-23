<?php
namespace App\Modules\SaaS\Modules\PlatformAdmin;

use App\Common\Bmvc\BaseModel;

class Model extends BaseModel
{
    public function listTenants(int $limit, int $offset): array
    {
        $stmt = $this->conn->prepare(
            "SELECT t.id, t.slug, t.name, t.status, t.plan_id,
                    p.name AS plan_name, t.created_at
             FROM   tenants t
             LEFT JOIN plans p ON p.id = t.plan_id
             WHERE  t.deleted_at IS NULL
             ORDER  BY t.created_at DESC
             LIMIT  :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit',  $limit,  \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function countTenants(): int
    {
        return (int) $this->conn->query(
            "SELECT COUNT(*) FROM tenants WHERE deleted_at IS NULL"
        )->fetchColumn();
    }

    public function updateStatus(int $tenantId, string $status): bool
    {
        $allowed = ['active', 'suspended', 'trial'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }
        $stmt = $this->conn->prepare(
            "UPDATE tenants SET status = :status WHERE id = :id"
        );
        return $stmt->execute([':status' => $status, ':id' => $tenantId]);
    }

    public function updatePlan(int $tenantId, int $planId): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE tenants SET plan_id = :plan_id WHERE id = :id"
        );
        return $stmt->execute([':plan_id' => $planId, ':id' => $tenantId]);
    }
}
