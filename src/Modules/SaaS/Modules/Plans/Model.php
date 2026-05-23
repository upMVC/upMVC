<?php
namespace App\Modules\SaaS\Modules\Plans;

use App\Common\Bmvc\BaseModel;

class Model extends BaseModel
{
    public function listAll(): array
    {
        $stmt = $this->conn->query(
            "SELECT id, name, price, features, limits
             FROM plans
             ORDER BY price ASC"
        );
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->conn->prepare(
            "SELECT id, name, price, features, limits
             FROM plans
             WHERE id = :id
             LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}
