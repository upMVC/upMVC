<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Common\Helpers\RateLimiter;

class RateLimiterTest extends TestCase
{
    private string $tmpDir;

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'rl_upmvc_' . uniqid() . DIRECTORY_SEPARATOR;
        mkdir($this->tmpDir, 0755, true);

        $ref = new \ReflectionProperty(RateLimiter::class, 'storageDir');
        $ref->setAccessible(true);
        $ref->setValue(null, $this->tmpDir);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->tmpDir . '*') ?: [] as $f) {
            @unlink($f);
        }
        @rmdir($this->tmpDir);

        $ref = new \ReflectionProperty(RateLimiter::class, 'storageDir');
        $ref->setAccessible(true);
        $ref->setValue(null, '');
    }

    public function testFirstAttemptIsAllowed(): void
    {
        $r = RateLimiter::check('1.1.1.1', 'login', 5, 900);
        $this->assertTrue($r['allowed']);
        $this->assertSame(5, $r['remaining']);
    }

    public function testBlockedAfterMaxAttempts(): void
    {
        for ($i = 0; $i < 5; $i++) {
            RateLimiter::recordFailure('2.2.2.2', 'login');
        }
        $r = RateLimiter::check('2.2.2.2', 'login', 5, 900);
        $this->assertFalse($r['allowed']);
        $this->assertStringContainsString('Too many', $r['message']);
    }

    public function testClearResetsLimit(): void
    {
        for ($i = 0; $i < 5; $i++) {
            RateLimiter::recordFailure('3.3.3.3', 'login');
        }
        RateLimiter::clearAttempts('3.3.3.3', 'login');
        $r = RateLimiter::check('3.3.3.3', 'login', 5, 900);
        $this->assertTrue($r['allowed']);
    }

    public function testDifferentIdentifiersDontInterfere(): void
    {
        for ($i = 0; $i < 5; $i++) {
            RateLimiter::recordFailure('4.4.4.4', 'login');
        }
        $r = RateLimiter::check('5.5.5.5', 'login', 5, 900);
        $this->assertTrue($r['allowed']);
    }

    public function testCheckActionUsesDefaults(): void
    {
        // Unset env overrides so we test the class defaults (5/900), not whatever
        // the .env file or a previous test may have injected via putenv().
        putenv('RATE_LIMIT_LOGIN_MAX');
        putenv('RATE_LIMIT_LOGIN_WINDOW');

        $r = RateLimiter::checkAction('9.9.9.9', 'login');
        $this->assertTrue($r['allowed']);
        $this->assertSame(5, $r['remaining']);
    }

    public function testGetClientIpFallback(): void
    {
        $ip = RateLimiter::getClientIp();
        $this->assertMatchesRegularExpression('/^\d{1,3}(\.\d{1,3}){3}$/', $ip);
    }

    public function testGetClientIpIgnoresSpoofedHeadersByDefault(): void
    {
        putenv('TRUSTED_PROXIES');
        $_SERVER['REMOTE_ADDR'] = '10.0.0.5';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '1.2.3.4';
        $_SERVER['HTTP_CF_CONNECTING_IP'] = '8.8.8.8';

        $this->assertSame('10.0.0.5', RateLimiter::getClientIp());

        unset($_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_X_FORWARDED_FOR'], $_SERVER['HTTP_CF_CONNECTING_IP']);
    }

    public function testGetClientIpHonoursForwardedWhenProxyTrusted(): void
    {
        putenv('TRUSTED_PROXIES=10.0.0.5');
        $_SERVER['REMOTE_ADDR'] = '10.0.0.5';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '203.0.113.9, 10.0.0.5';

        $this->assertSame('203.0.113.9', RateLimiter::getClientIp());

        putenv('TRUSTED_PROXIES');
        unset($_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_X_FORWARDED_FOR']);
    }
}
