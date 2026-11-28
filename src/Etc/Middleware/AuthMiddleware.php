<?php
/**
 * AuthMiddleware.php - Authentication Middleware
 *
 * Session-based route protection for upMVC:
 * - Pattern-based route matching (wildcards via fnmatch)
 * - Stores a same-origin intended path for post-login redirect
 * - Supports $_SESSION['authenticated'] and legacy $_SESSION['logged']
 *
 * @package upMVC\Middleware
 */

namespace App\Etc\Middleware;

class AuthMiddleware implements MiddlewareInterface
{
    /** @var list<string> */
    private array $protectedRoutes;

    private string $redirectTo;

    /**
     * @param list<string> $protectedRoutes
     */
    public function __construct(array $protectedRoutes = [], string $redirectTo = '/auth')
    {
        $this->protectedRoutes = $protectedRoutes;
        $this->redirectTo = $redirectTo;
    }

    public function handle(array $request, callable $next)
    {
        $route = $request['route'] ?? '';

        if ($this->requiresAuth($route)) {
            if (!$this->isAuthenticated()) {
                if (!isset($_SESSION['intended_url'])) {
                    $candidate = (string) ($request['route'] ?? $request['uri'] ?? '');
                    $safe = self::safeIntendedPath($candidate);
                    if ($safe !== null) {
                        $_SESSION['intended_url'] = $safe;
                    }
                }

                $wantsJson = str_contains((string) ($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json')
                    || str_starts_with((string) $route, '/api');

                if ($wantsJson) {
                    http_response_code(401);
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['error' => 'Unauthenticated']);
                    exit;
                }

                $baseUrl = defined('BASE_URL') ? BASE_URL : '';
                header('Location: ' . $baseUrl . $this->redirectTo);
                exit;
            }
        }

        return $next($request);
    }

    /**
     * Same-origin relative path only — reject open redirects.
     *
     * Accepts "/admin/users" or "admin/users"; rejects "//evil", "http:", "javascript:", etc.
     */
    public static function safeIntendedPath(string $url): ?string
    {
        // Reject control chars on raw input before trim (trim would hide \r\n)
        if (preg_match('/[\x00-\x1f\x7f]/', $url)) {
            return null;
        }

        $url = trim($url);
        if ($url === '') {
            return null;
        }

        // Reject schemes / protocol-relative URLs on the raw input
        // (parse_url would otherwise leave only the path, e.g. https://evil/x → /x).
        if (preg_match('#^[a-z][a-z0-9+.-]*:#i', $url)) {
            return null;
        }
        if (str_starts_with($url, '//')) {
            return null;
        }
        if (str_contains($url, '\\')) {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH);
        if ($path === null || $path === false) {
            $path = strtok($url, '?#');
        }
        if (!is_string($path) || $path === '') {
            return null;
        }
        if (str_starts_with($path, '//')) {
            return null;
        }
        if (preg_match('/[\x00-\x1f\x7f]/', $path)) {
            return null;
        }

        if ($path[0] !== '/') {
            $path = '/' . $path;
        }

        return $path;
    }

    private function requiresAuth(string $route): bool
    {
        foreach ($this->protectedRoutes as $pattern) {
            $pattern = (string) $pattern;
            if (fnmatch($pattern, $route)) {
                return true;
            }
            // `/admin/*` must also cover the exact prefix `/admin`
            if (str_ends_with($pattern, '/*')) {
                $prefix = substr($pattern, 0, -2);
                if ($prefix !== '' && $route === $prefix) {
                    return true;
                }
            }
        }
        return false;
    }

    private function isAuthenticated(): bool
    {
        $newAuth = isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
        $legacyAuth = isset($_SESSION['logged']) && $_SESSION['logged'] === true;
        return $newAuth || $legacyAuth;
    }
}
