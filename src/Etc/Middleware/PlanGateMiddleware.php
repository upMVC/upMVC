<?php
/**
 * PlanGateMiddleware.php - Feature flag gate per tenant plan
 *
 * Blocks a route if the current tenant's plan does not include a given feature.
 * Expects TenantMiddleware to have run first (sets $GLOBALS['current_tenant']).
 *
 * Usage via named router middleware (colon syntax parsed by Router):
 *   ->middleware(['cors', 'jwt', 'tenant', 'feature:efactura'])
 *
 * The Router will instantiate this class with the feature name extracted
 * from the colon segment and call __invoke().
 *
 * The tenant's `features` JSON column must contain the feature as a truthy key:
 *   {"invoices": true, "efactura": true, "advanced_reports": false}
 *
 * @package upMVC\Middleware
 */

namespace App\Etc\Middleware;

class PlanGateMiddleware
{
    public function __construct(private string $feature) {}

    /**
     * Check that the current tenant has the required feature.
     * Called by Router as: (new PlanGateMiddleware($feature))($route, $method)
     *
     * @param string $route
     * @param string $method
     * @return bool
     */
    public function __invoke(string $route, string $method): bool
    {
        $tenant = $GLOBALS['current_tenant'] ?? null;

        if (!$tenant) {
            $this->abort(401, 'Tenant context not available — run tenant middleware first');
        }

        $features = $tenant['features'] ?? [];
        if (is_string($features)) {
            $features = json_decode($features, true) ?? [];
        }

        if (empty($features[$this->feature])) {
            $this->abort(
                403,
                "Feature '{$this->feature}' is not available on your current plan. Please upgrade."
            );
        }

        return true;
    }

    // -----------------------------------------------------------------------
    // Response helpers
    // -----------------------------------------------------------------------

    /**
     * @param int    $code
     * @param string $msg
     * @return never
     */
    private function abort(int $code, string $msg): never
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => $msg]);
        exit;
    }
}
