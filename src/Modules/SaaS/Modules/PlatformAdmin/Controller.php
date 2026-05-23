<?php
namespace App\Modules\SaaS\Modules\PlatformAdmin;

use App\Common\Bmvc\BaseApiController;

/**
 * PlatformAdmin Controller — platform-owner operations
 *
 * Every method requires role = 'platform_admin' in the JWT.
 * The guard runs in __construct so no individual method needs to repeat it.
 */
class Controller extends BaseApiController
{
    public function __construct()
    {
        parent::__construct();

        if (($this->user['role'] ?? '') !== 'platform_admin') {
            $this->error('Platform admin access required', 403);
        }
    }

    /** GET /api/admin/tenants?limit=50&offset=0 */
    public function listTenants(): never
    {
        $limit  = max(1, min(100, (int) ($_GET['limit']  ?? 50)));
        $offset = max(0, (int) ($_GET['offset'] ?? 0));

        $model = new Model();
        $this->success([
            'tenants' => $model->listTenants($limit, $offset),
            'total'   => $model->countTenants(),
        ]);
    }

    /** PATCH /api/admin/tenants/{id}/status   body: {"status":"active"|"suspended"|"trial"} */
    public function updateStatus(): never
    {
        $id   = (int) ($_GET['id'] ?? 0);
        $body = $this->requireFields(['status']);

        $ok = (new Model())->updateStatus($id, $body['status']);
        $ok
            ? $this->success(null, 'Status updated')
            : $this->error('Invalid status value or tenant not found', 400);
    }

    /** PATCH /api/admin/tenants/{id}/plan   body: {"plan_id":2} */
    public function updatePlan(): never
    {
        $id   = (int) ($_GET['id'] ?? 0);
        $body = $this->requireFields(['plan_id']);

        $ok = (new Model())->updatePlan($id, (int) $body['plan_id']);
        $ok
            ? $this->success(null, 'Plan updated')
            : $this->error('Update failed', 400);
    }
}
