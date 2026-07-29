<?php
/**
 * JwtAuthMiddleware.php - Stateless JWT bearer token verification
 *
 * Verifies the Authorization: Bearer <token> header, decodes the payload,
 * and stores it in $GLOBALS['current_user'] so any controller downstream
 * can read it without touching sessions.
 *
 * Algorithm: HS256 (HMAC-SHA256)
 * Secret:    JWT_SECRET in .env
 *
 * Sets $GLOBALS['current_user']:
 *   ['sub' => userId, 'username' => '...', 'tenant_id' => ..., 'exp' => ...]
 *
 * @package upMVC\Middleware
 */

namespace App\Etc\Middleware;

use App\Etc\Config\Environment;

class JwtAuthMiddleware
{
    /**
     * Verify the bearer token and populate $GLOBALS['current_user'].
     * Returns false if the token is missing or invalid (aborts the request).
     *
     * Called by Router as: $middleware($route, $method)
     *
     * @param string $route
     * @param string $method
     * @return bool
     */
    public function __invoke(string $route, string $method): bool
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (!preg_match('/^Bearer\s+(.+)$/i', $header, $m)) {
            $this->abort(401, 'No bearer token provided');
        }

        $payload = self::verifyToken($m[1]);
        if ($payload === null) {
            $this->abort(401, 'Invalid or expired token');
        }

        $GLOBALS['current_user'] = $payload;
        return true;
    }

    // -----------------------------------------------------------------------
    // JWT helpers
    // -----------------------------------------------------------------------

    /**
     * Verify an HS256 JWT and return its payload, or null on failure.
     *
     * Public and static because tokens do not only arrive in an Authorization
     * header. Anything holding a raw token — an impersonation form post, a
     * queued job, a CLI tool — needs to verify it without going through the
     * request pipeline, and JwtService deliberately issues only.
     *
     * Returns null rather than throwing: an invalid token is an expected
     * condition, not an exceptional one. Callers decide what to do about it.
     *
     * @param string $token Raw JWT (no "Bearer " prefix)
     * @return array<string, mixed>|null Decoded claims, or null if invalid or expired
     */
    public static function verifyToken(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$header, $payload, $sig] = $parts;

        $secret = (string) (Environment::get('JWT_SECRET') ?: getenv('JWT_SECRET') ?: ($_ENV['JWT_SECRET'] ?? ''));
        if ($secret === '') {
            error_log('JwtAuthMiddleware: JWT_SECRET is not set in .env');
            return null;
        }

        // Verify signature — constant-time comparison prevents timing attacks
        $expected = hash_hmac('sha256', "$header.$payload", $secret, true);
        $provided = base64_decode(strtr($sig, '-_', '+/'));

        if (!hash_equals($expected, $provided)) {
            return null;
        }

        $data = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);
        if (!is_array($data)) {
            return null;
        }

        // Check expiry
        if (isset($data['exp']) && $data['exp'] < time()) {
            return null;
        }

        return $data;
    }

    // -----------------------------------------------------------------------
    // Response helpers
    // -----------------------------------------------------------------------

    /**
     * Send a JSON error response and halt execution.
     *
     * @param int    $code HTTP status code
     * @param string $msg  Error message
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
