<?php
namespace App\Modules\SaaS\Modules\Plans;

use App\Common\Bmvc\BaseApiController;

class Controller extends BaseApiController
{
    /** GET /api/plans — public, list all plans */
    public function index(): never
    {
        $plans = (new Model())->listAll();
        foreach ($plans as &$p) {
            $p['features'] = json_decode($p['features'] ?? '{}', true) ?? [];
            $p['limits']   = json_decode($p['limits']   ?? '{}', true) ?? [];
        }
        $this->success($plans);
    }

    /** GET /api/plans/{id} */
    public function show(): never
    {
        $id   = (int) ($_GET['id'] ?? 0);
        $plan = (new Model())->findById($id);

        if (!$plan) {
            $this->error('Plan not found', 404);
        }

        $plan['features'] = json_decode($plan['features'] ?? '{}', true) ?? [];
        $plan['limits']   = json_decode($plan['limits']   ?? '{}', true) ?? [];

        $this->success($plan);
    }
}
