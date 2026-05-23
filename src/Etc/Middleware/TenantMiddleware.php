<?php
/**
 * TenantMiddleware.php - Resolve and validate the current tenant
 *
 * Reads the tenant slug from the request (path-based or subdomain-based),
 * loads the tenant row from the DB, and stores it in $GLOBALS['current_tenant'].
 *
 * Slug resolution order:
 *   1. Path segment: /app/{slug}/anything  → slug = {slug}
 *   2. Subdomain:    {slug}.yourdomain.com → slug = {slug}
 *
 * $GLOBALS['current_tenant'] will be the full tenants row (array).
 *
 * @package upMVC\Middleware
 */

namespace App\Etc\Middleware;

use App\Etc\Database;

class TenantMiddleware
{
    /**
     * Resolve and validate the tenant, then store in globals.
     * Called by Router as: $middleware($route, $method)
     *
     * @param string $route
     * @param string $method
     * @return bool
     */
    public function __invoke(string $route, string $method): bool
    {
        $slug = $this->resolveSlug();

        $db   = (new Database())->getConnection();
        $stmt = $db->prepare(
            "SELECT id, slug, name, plan_id, status, features
             FROM tenants
             WHERE slug = :slug AND deleted_at IS NULL
             LIMIT 1"
        );
        $stmt->execute([':slug' => $slug]);
        $tenant = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$tenant) {
            $this->abort(404, 'Tenant not found');
        }

        if ($tenant['status'] !== 'active') {
            $this->abort(403, 'Account suspended or not yet active');
        }

        // Decode JSON features column to array for easy downstream use
        if (is_string($tenant['features'])) {
            $tenant['features'] = json_decode($tenant['features'], true) ?? [];
        }

        $GLOBALS['current_tenant'] = $tenant;
        return true;
    }

    // -----------------------------------------------------------------------
    // Slug resolution
    // -----------------------------------------------------------------------

    /**
     * Determine the tenant slug from the current request.
     * Aborts with 400 if it cannot be resolved.
     */
    private function resolveSlug(): string
    {
        $uri  = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
        $host = $_SERVER['HTTP_HOST'] ?? '';

        // 1. Path-based: /app/{slug}/...
        if (preg_match('#^/app/([a-z0-9\-_]+)(/|$)#i', $uri, $m)) {
            return strtolower($m[1]);
        }

        // 2. Subdomain-based: {slug}.yourdomain.com
        $baseHost = parse_url($_ENV['BASE_URL'] ?? '', PHP_URL_HOST) ?? '';
        if ($baseHost !== '' && str_ends_with($host, '.' . $baseHost)) {
            return strtolower(substr($host, 0, -strlen('.' . $baseHost)));
        }

        $this->abort(400, 'Tenant could not be resolved from the request');
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
