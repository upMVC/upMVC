<?php
/**
 * JwtAuthMiddleware.php - Stateless JWT bearer token verification
 *
 * Verifies the Authorization: Bearer <token> header, decodes the payload,
 * and stores it in $GLOBALS['current_user'] so any controller downstream
 * can read it without touching sessions.
 *
 * Algorithm: HS256 (HMAC-SHA256) only — other algs (incl. "none") are rejected.
 * Secret:    JWT_SECRET in .env (minimum 32 bytes)
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
    private const MIN_SECRET_LENGTH = 32;

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
        $header = self::authorizationHeader();

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

    /**
     * Authorization header across CGI / Apache / IIS setups.
     */
    public static function authorizationHeader(): string
    {
        if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
            return (string) $_SERVER['HTTP_AUTHORIZATION'];
        }
        if (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            return (string) $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            foreach ($headers as $key => $value) {
                if (strcasecmp((string) $key, 'Authorization') === 0) {
                    return (string) $value;
                }
            }
        }
        return '';
    }

    // -----------------------------------------------------------------------
    // JWT helpers
    // -----------------------------------------------------------------------

    /**
     * Verify an HS256 JWT and return its payload, or null on failure.
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

        [$headerB64, $payloadB64, $sigB64] = $parts;

        $secret = (string) (Environment::get('JWT_SECRET') ?: getenv('JWT_SECRET') ?: ($_ENV['JWT_SECRET'] ?? ''));
        if ($secret === '' || strlen($secret) < self::MIN_SECRET_LENGTH) {
            error_log('JwtAuthMiddleware: JWT_SECRET is missing or shorter than ' . self::MIN_SECRET_LENGTH . ' bytes');
            return null;
        }

        $headerJson = self::b64uDecode($headerB64);
        if ($headerJson === null) {
            return null;
        }
        $header = json_decode($headerJson, true);
        if (!is_array($header) || ($header['alg'] ?? null) !== 'HS256') {
            return null;
        }

        $expected = hash_hmac('sha256', "$headerB64.$payloadB64", $secret, true);
        $provided = self::b64uDecode($sigB64);
        if ($provided === null || !hash_equals($expected, $provided)) {
            return null;
        }

        $payloadJson = self::b64uDecode($payloadB64);
        if ($payloadJson === null) {
            return null;
        }
        $data = json_decode($payloadJson, true);
        if (!is_array($data)) {
            return null;
        }

        if (!isset($data['exp']) || !is_numeric($data['exp'])) {
            return null;
        }
        if ((int) $data['exp'] < time()) {
            return null;
        }
        if (isset($data['nbf']) && (!is_numeric($data['nbf']) || (int) $data['nbf'] > time())) {
            return null;
        }

        return $data;
    }

    private static function b64uDecode(string $data): ?string
    {
        $remainder = strlen($data) % 4;
        if ($remainder > 0) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        $decoded = base64_decode(strtr($data, '-_', '+/'), true);
        return $decoded === false ? null : $decoded;
    }

    // -----------------------------------------------------------------------
    // Response helpers
    // -----------------------------------------------------------------------

    /**
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
