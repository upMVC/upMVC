<?php
/**
 * JwtService.php — JWT access-token + refresh-token factory
 *
 * Issues and signs tokens. Verification of incoming tokens is handled
 * by JwtAuthMiddleware — do not add verify() here.
 *
 * .env keys:
 *   JWT_SECRET        — HMAC-SHA256 signing secret (required)
 *   JWT_ACCESS_TTL    — access token lifetime in seconds  (default: 3600)
 *   JWT_REFRESH_TTL   — refresh token lifetime in seconds (default: 2592000 / 30 days)
 *
 * @package upMVC\Etc
 */

namespace App\Etc;

use App\Etc\Config\Environment;

class JwtService
{
    private string $secret;
    private int    $accessTtl;
    private int    $refreshTtl;

    public function __construct()
    {
        $this->secret     = (string) (Environment::get('JWT_SECRET')     ?: getenv('JWT_SECRET')     ?: ($_ENV['JWT_SECRET']      ?? ''));
        $this->accessTtl  = (int)   (Environment::get('JWT_ACCESS_TTL')  ?: getenv('JWT_ACCESS_TTL')  ?: ($_ENV['JWT_ACCESS_TTL']  ?? 3600));
        $this->refreshTtl = (int)   (Environment::get('JWT_REFRESH_TTL') ?: getenv('JWT_REFRESH_TTL') ?: ($_ENV['JWT_REFRESH_TTL'] ?? 2592000));
    }

    // -----------------------------------------------------------------------
    // Token issuance
    // -----------------------------------------------------------------------

    /**
     * Build a signed HS256 JWT with the given claims.
     * Adds iat and exp automatically.
     *
     * @param array $claims  e.g. ['sub' => 1, 'username' => 'john', 'role' => 'admin']
     * @return string Signed JWT string
     * @throws \RuntimeException if JWT_SECRET is not configured
     */
    public function issueAccessToken(array $claims): string
    {
        if ($this->secret === '') {
            throw new \RuntimeException('JWT_SECRET is not set in .env');
        }

        $now           = time();
        $claims['iat'] = $now;
        $claims['exp'] = $now + $this->accessTtl;

        return $this->encode($claims);
    }

    /**
     * Generate a cryptographically secure opaque refresh token.
     * The caller must hash it (SHA-256) before storing in the DB.
     *
     * @return string 64-char hex string
     */
    public function issueRefreshToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * How long (seconds) refresh tokens are valid for.
     * Used by callers to compute the expires_at timestamp.
     */
    public function getRefreshTtl(): int
    {
        return $this->refreshTtl;
    }

    /**
     * How long (seconds) access tokens are valid for.
     * Returned in the login response as expires_in.
     */
    public function getAccessTtl(): int
    {
        return $this->accessTtl;
    }

    // -----------------------------------------------------------------------
    // Internals
    // -----------------------------------------------------------------------

    private function encode(array $payload): string
    {
        $h = $this->b64u(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $p = $this->b64u(json_encode($payload));
        $s = $this->b64u(hash_hmac('sha256', "$h.$p", $this->secret, true));
        return "$h.$p.$s";
    }

    private function b64u(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
