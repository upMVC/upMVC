<?php

declare(strict_types=1);

namespace Tests\Unit\Middleware;

use App\Etc\Middleware\AuthMiddleware;
use PHPUnit\Framework\TestCase;

final class AuthMiddlewareTest extends TestCase
{
    public function testSafeIntendedPathAcceptsRelativePaths(): void
    {
        $this->assertSame('/admin/users', AuthMiddleware::safeIntendedPath('/admin/users'));
        $this->assertSame('/admin', AuthMiddleware::safeIntendedPath('admin'));
    }

    public function testSafeIntendedPathRejectsOpenRedirects(): void
    {
        $this->assertNull(AuthMiddleware::safeIntendedPath('//evil.example'));
        $this->assertNull(AuthMiddleware::safeIntendedPath('https://evil.example/phish'));
        $this->assertNull(AuthMiddleware::safeIntendedPath('javascript:alert(1)'));
        $this->assertNull(AuthMiddleware::safeIntendedPath(''));
        $this->assertNull(AuthMiddleware::safeIntendedPath("/admin\r\n"));
    }

    public function testSafeIntendedPathStripsQuery(): void
    {
        $this->assertSame('/dash', AuthMiddleware::safeIntendedPath('/dash?x=1'));
    }

    public function testWildcardPatternAlsoProtectsExactPrefix(): void
    {
        $mw = new AuthMiddleware(['/admin/*'], '/auth');
        $ref = new \ReflectionClass($mw);
        $method = $ref->getMethod('requiresAuth');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($mw, '/admin'));
        $this->assertTrue($method->invoke($mw, '/admin/users'));
        $this->assertFalse($method->invoke($mw, '/public'));
    }

    public function testWantsJsonOnlyForApiSegmentNotApiInfo(): void
    {
        unset($_SERVER['HTTP_ACCEPT']);
        $this->assertTrue(AuthMiddleware::wantsJsonResponse('/api'));
        $this->assertTrue(AuthMiddleware::wantsJsonResponse('/api/users'));
        $this->assertFalse(AuthMiddleware::wantsJsonResponse('/apiInfo'));
        $this->assertFalse(AuthMiddleware::wantsJsonResponse('/api-docs'));
        $this->assertFalse(AuthMiddleware::wantsJsonResponse('/admin'));
    }
}
