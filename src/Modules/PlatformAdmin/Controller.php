<?php

namespace App\Modules\PlatformAdmin;

use App\Modules\SaaS\Modules\PlatformAdmin\Model as TenantModel;
use App\Modules\SaaS\Modules\Plans\Model as PlansModel;
use App\Etc\Security;

class Controller
{
    private function guard(): void
    {
        if (!isset($_SESSION['logged']) || $_SESSION['logged'] !== true) {
            $_SESSION['intended_url'] = BASE_URL . '/platform-admin';
            header('Location: ' . BASE_URL . '/auth');
            exit;
        }

        if (($_SESSION['role'] ?? '') !== 'platform_admin') {
            http_response_code(403);
            echo '<h1>403 — Platform admin access required.</h1>';
            exit;
        }
    }

    public function display(string $reqRoute, string $reqMet): void
    {
        $this->guard();

        // Dispatch by route pattern
        if ($reqRoute === '/platform-admin' && $reqMet === 'GET') {
            $this->listTenants();
            return;
        }

        // POST /platform-admin/tenants/{id}/status
        if (str_ends_with($reqRoute, '/status') && $reqMet === 'POST') {
            $this->updateStatus();
            return;
        }

        // POST /platform-admin/tenants/{id}/plan
        if (str_ends_with($reqRoute, '/plan') && $reqMet === 'POST') {
            $this->updatePlan();
            return;
        }

        // Fallback — show tenant list
        $this->listTenants();
    }

    // ---------------------------------------------------------------

    private function listTenants(): void
    {
        $limit  = max(1, min(100, (int) ($_GET['limit']  ?? 50)));
        $offset = max(0, (int) ($_GET['offset'] ?? 0));

        $tenantModel = new TenantModel();
        $plansModel  = new PlansModel();

        $view = new View();
        $view->renderList([
            'tenants' => $tenantModel->listTenants($limit, $offset),
            'total'   => $tenantModel->countTenants(),
            'plans'   => $plansModel->listAll(),
            'limit'   => $limit,
            'offset'  => $offset,
        ]);
    }

    private function updateStatus(): void
    {
        if (!$this->verifyCsrf()) {
            $this->flashAndRedirect('error', 'Invalid CSRF token.');
        }

        $id     = (int) ($_GET['id'] ?? 0);
        $status = trim($_POST['status'] ?? '');

        $ok = (new TenantModel())->updateStatus($id, $status);
        $ok
            ? $this->flashAndRedirect('success', 'Status updated.')
            : $this->flashAndRedirect('error', 'Invalid status value.');
    }

    private function updatePlan(): void
    {
        if (!$this->verifyCsrf()) {
            $this->flashAndRedirect('error', 'Invalid CSRF token.');
        }

        $id     = (int) ($_GET['id'] ?? 0);
        $planId = (int) ($_POST['plan_id'] ?? 0);

        $ok = (new TenantModel())->updatePlan($id, $planId);
        $ok
            ? $this->flashAndRedirect('success', 'Plan updated.')
            : $this->flashAndRedirect('error', 'Plan update failed.');
    }

    // ---------------------------------------------------------------

    private function verifyCsrf(): bool
    {
        return Security::validateCsrf($_POST['csrf_token'] ?? '');
    }

    private function flashAndRedirect(string $type, string $msg): never
    {
        $_SESSION['flash_type'] = $type;
        $_SESSION['flash_msg']  = $msg;
        header('Location: ' . BASE_URL . '/platform-admin');
        exit;
    }
}
