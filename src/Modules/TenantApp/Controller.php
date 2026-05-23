<?php

namespace App\Modules\TenantApp;

class Controller
{
    private const ALLOWED_ROLES = ['tenant_owner', 'tenant_user'];

    // ---------------------------------------------------------------
    // Auth helpers
    // ---------------------------------------------------------------

    /**
     * Require the user to be a tenant member owning this slug.
     * Returns the loaded tenant row (with plan) to avoid a second query.
     */
    private function requireAdmin(string $slug): array
    {
        if (!isset($_SESSION['logged']) || $_SESSION['logged'] !== true) {
            // Strip SITEPATH prefix so intended_url has no double path
            $path = str_replace(SITEPATH, '', strtok($_SERVER['REQUEST_URI'], '?'));
            $_SESSION['intended_url'] = BASE_URL . $path;
            header('Location: ' . BASE_URL . '/auth');
            exit;
        }

        if (!in_array($_SESSION['role'] ?? '', self::ALLOWED_ROLES, true)) {
            http_response_code(403);
            echo '<h1>403 — Tenant access only.</h1>';
            exit;
        }

        $model  = new Model();
        $tenant = $model->findByIdWithPlan((int) ($_SESSION['tenant_id'] ?? 0));

        if (!$tenant || $tenant['slug'] !== strtolower($slug)) {
            http_response_code(403);
            echo '<h1>403 — You do not have access to this tenant.</h1>';
            exit;
        }

        return $tenant;
    }

    /** Require any tenant login (used for /app redirect). */
    private function requireLogin(): void
    {
        if (!isset($_SESSION['logged']) || $_SESSION['logged'] !== true) {
            header('Location: ' . BASE_URL . '/auth');
            exit;
        }
        if (!in_array($_SESSION['role'] ?? '', self::ALLOWED_ROLES, true)) {
            http_response_code(403);
            echo '<h1>403 — Tenant access only.</h1>';
            exit;
        }
    }

    // ---------------------------------------------------------------
    // Dispatch
    // ---------------------------------------------------------------

    public function display(string $reqRoute, string $reqMet): void
    {
        $slug    = $_GET['slug']    ?? '';
        $page    = $_GET['page']    ?? '';    // 3-segment: /app/{slug}/{page}
        $subpage = $_GET['subpage'] ?? '';    // 4-segment: /app/{slug}/admin/{subpage}

        // /app → redirect to the logged-in user's admin dashboard
        if ($reqRoute === '/app') {
            $this->requireLogin();
            $this->redirectToOwnAdmin();
            return;
        }

        // ---- Admin area (/app/{slug}/admin  or  /app/{slug}/admin/{subpage}) ----
        if ($page === 'admin' || $subpage !== '') {
            $tenant = $this->requireAdmin($slug);
            $model  = new Model();
            $view   = new View();

            if ($subpage !== '') {
                // /app/{slug}/admin/{subpage}
                match ($subpage) {
                    'users'  => $view->renderAdminUsers([
                        'tenant' => $tenant,
                        'users'  => $model->listUsers($tenant['id']),
                    ]),
                    default  => $view->renderAdminDashboard([
                        'tenant'        => $tenant,
                        'user_count'    => $model->countUsers($tenant['id']),
                        'users_preview' => $model->listUsers($tenant['id']),
                    ]),
                };
            } else {
                // /app/{slug}/admin
                $view->renderAdminDashboard([
                    'tenant'        => $tenant,
                    'user_count'    => $model->countUsers($tenant['id']),
                    'users_preview' => $model->listUsers($tenant['id']),
                ]);
            }
            return;
        }

        // ---- Public frontend — no auth required ----
        $model  = new Model();
        $tenant = $model->findBySlug($slug);

        if (!$tenant) {
            http_response_code(404);
            echo '<h1>Tenant not found.</h1>';
            exit;
        }

        $view = new View();
        $view->renderFrontend([
            'tenant'   => $tenant,
            'is_admin' => (
                isset($_SESSION['logged'], $_SESSION['tenant_id']) &&
                $_SESSION['logged'] === true &&
                (int) $_SESSION['tenant_id'] === (int) $tenant['id']
            ),
        ]);
    }

    // ---------------------------------------------------------------

    private function redirectToOwnAdmin(): never
    {
        $tenantId = $_SESSION['tenant_id'] ?? null;

        if (!$tenantId) {
            // User has no tenant — fall back to home
            header('Location: ' . BASE_URL);
            exit;
        }

        $model  = new Model();
        $tenant = $model->findByIdWithPlan((int) $tenantId);

        if (!$tenant) {
            header('Location: ' . BASE_URL);
            exit;
        }

        header('Location: ' . BASE_URL . '/app/' . $tenant['slug'] . '/admin');
        exit;
    }
}
