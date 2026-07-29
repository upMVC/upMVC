<?php

declare(strict_types=1);

namespace Tests\Unit\Middleware;

use App\Etc\JwtService;
use App\Etc\Middleware\JwtAuthMiddleware;
use PHPUnit\Framework\TestCase;

/**
 * verifyToken() is public API: tokens do not only arrive in an Authorization
 * header, so anything holding a raw token verifies through here. It is also
 * the only signature check in the framework, which makes its rejection paths
 * worth pinning down — a verifier that accepts a forged token fails silently
 * and catastrophically.
 *
 * __invoke() is not covered: it aborts with exit() on failure.
 */
final class JwtAuthMiddlewareTest extends TestCase
{
    private const SECRET = 'test-secret-at-least-32-characters-long-xx';

    private ?string $previousSecret = null;

    protected function setUp(): void
    {
        $this->previousSecret = $_ENV['JWT_SECRET'] ?? null;
        $_ENV['JWT_SECRET'] = self::SECRET;
    }

    protected function tearDown(): void
    {
        if ($this->previousSecret === null) {
            unset($_ENV['JWT_SECRET']);
        } else {
            $_ENV['JWT_SECRET'] = $this->previousSecret;
        }
    }

    private function b64u(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /** Rebuild a token with altered claims, signed with the given secret. */
    private function resign(array $claims, string $secret): string
    {
        $h = $this->b64u((string) json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $p = $this->b64u((string) json_encode($claims));

        return "$h.$p." . $this->b64u(hash_hmac('sha256', "$h.$p", $secret, true));
    }

    public function testVerifiesATokenItJustIssued(): void
    {
        $token   = (new JwtService())->issueAccessToken(['sub' => 7, 'username' => 'admin']);
        $payload = JwtAuthMiddleware::verifyToken($token);

        $this->assertIsArray($payload);
        $this->assertSame(7, $payload['sub']);
        $this->assertSame('admin', $payload['username']);
        $this->assertArrayHasKey('iat', $payload);
        $this->assertArrayHasKey('exp', $payload);
    }

    public function testRejectsATamperedSignature(): void
    {
        $token = (new JwtService())->issueAccessToken(['sub' => 7]);

        $this->assertNull(JwtAuthMiddleware::verifyToken($token . 'x'));
    }

    public function testRejectsAPayloadResignedWithTheWrongSecret(): void
    {
        // The attack that matters: valid structure, valid-looking claims,
        // signed by someone who does not hold JWT_SECRET.
        $forged = $this->resign(['sub' => 1, 'role' => 'platform_admin', 'exp' => time() + 3600], 'wrong-secret');

        $this->assertNull(JwtAuthMiddleware::verifyToken($forged));
    }

    public function testRejectsAnExpiredToken(): void
    {
        $expired = $this->resign(['sub' => 7, 'exp' => time() - 60], self::SECRET);

        $this->assertNull(JwtAuthMiddleware::verifyToken($expired));
    }

    public function testAcceptsATokenWithNoExpiryClaim(): void
    {
        $noExp = $this->resign(['sub' => 7], self::SECRET);

        $this->assertIsArray(JwtAuthMiddleware::verifyToken($noExp));
    }

    public function testRejectsMalformedInput(): void
    {
        foreach (['', 'not.a.jwt', 'onlyonepart', 'a.b', 'a.b.c.d'] as $bad) {
            $this->assertNull(
                JwtAuthMiddleware::verifyToken($bad),
                sprintf('Expected %s to be rejected', var_export($bad, true))
            );
        }
    }

    public function testRejectsWhenNoSecretIsConfigured(): void
    {
        $token = (new JwtService())->issueAccessToken(['sub' => 7]);

        unset($_ENV['JWT_SECRET']);
        putenv('JWT_SECRET=');

        $this->assertNull(JwtAuthMiddleware::verifyToken($token));
    }
}
