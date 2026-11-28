<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Etc\Config;
use PHPUnit\Framework\TestCase;

final class ConfigHttpsProxyTest extends TestCase
{
    protected function tearDown(): void
    {
        putenv('TRUSTED_PROXIES');
        unset(
            $_ENV['TRUSTED_PROXIES'],
            $_SERVER['HTTPS'],
            $_SERVER['SERVER_PORT'],
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_X_FORWARDED_PROTO'],
            $_SERVER['HTTP_FRONT_END_HTTPS']
        );
    }

    public function testDirectHttpsStillDetected(): void
    {
        $_SERVER['HTTPS'] = 'on';
        $this->assertTrue(Config::requestLooksHttps());
    }

    public function testForwardedProtoIgnoredWithoutTrustedProxy(): void
    {
        putenv('TRUSTED_PROXIES');
        unset($_ENV['TRUSTED_PROXIES']);
        $_SERVER['REMOTE_ADDR'] = '203.0.113.50';
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
        unset($_SERVER['HTTPS'], $_SERVER['SERVER_PORT']);

        $this->assertFalse(Config::requestLooksHttps());
    }

    public function testForwardedProtoHonouredForTrustedProxy(): void
    {
        putenv('TRUSTED_PROXIES=10.0.0.1');
        $_ENV['TRUSTED_PROXIES'] = '10.0.0.1';
        $_SERVER['REMOTE_ADDR'] = '10.0.0.1';
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
        unset($_SERVER['HTTPS'], $_SERVER['SERVER_PORT']);

        $this->assertTrue(Config::requestLooksHttps());
    }
}
