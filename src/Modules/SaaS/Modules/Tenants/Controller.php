<?php
namespace App\Modules\SaaS\Modules\Tenants;

use App\Common\Bmvc\BaseApiController;

class Controller extends BaseApiController
{
    /**
     * POST /api/tenants/register — public
     * Creates tenant + owner user in one transaction.
     */
    public function register(): never
    {
        $body  = $this->requireFields(['slug', 'name', 'username', 'email', 'password']);
        $model = new Model();

        // Normalise slug
        $slug = strtolower(preg_replace('/[^a-z0-9\-]/i', '-', trim($body['slug'])));

        if ($model->findBySlug($slug)) {
            $this->error("Slug '{$slug}' is already taken", 409);
        }

        $tenantId = $model->create([
            'slug'    => $slug,
            'name'    => $body['name'],
            'plan_id' => (int) ($body['plan_id'] ?? 1),
        ]);

        $userId = $model->createOwnerUser($tenantId, [
            'name'     => $body['name'],
            'username' => $body['username'],
            'email'    => $body['email'],
            'password' => $body['password'],
        ]);

        $this->success(
            ['tenant_id' => $tenantId, 'user_id' => $userId],
            'Registration successful. You can now log in via /api/auth/login.',
            201
        );
    }

    /**
     * GET /api/tenants/{id} — requires jwt
     */
    public function show(): never
    {
        $id = (int) ($_GET['id'] ?? 0);
        $this->assertTenantAccess($id);

        $tenant = (new Model())->findById($id);
        if (!$tenant) {
            $this->error('Tenant not found', 404);
        }

        $tenant['features'] = json_decode($tenant['features'] ?? '{}', true) ?? [];
        $this->success($tenant);
    }

    /**
     * PATCH /api/tenants/{id} — requires jwt, tenant owner or platform_admin
     */
    public function update(): never
    {
        $id = (int) ($_GET['id'] ?? 0);
        $this->assertTenantAccess($id);

        $ok = (new Model())->update($id, $this->body());
        $ok ? $this->success(null, 'Tenant updated') : $this->error('Nothing to update or update failed');
    }

    // -----------------------------------------------------------------------
    // Guard
    // -----------------------------------------------------------------------

    private function assertTenantAccess(int $id): void
    {
        $role         = $this->user['role']      ?? '';
        $userTenantId = (int) ($this->user['tenant_id'] ?? 0);

        if ($role !== 'platform_admin' && $userTenantId !== $id) {
            $this->error('Forbidden', 403);
        }
    }
}
