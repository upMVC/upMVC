<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Etc\TrustedProxies;
use PHPUnit\Framework\TestCase;

final class TrustedProxiesTest extends TestCase
{
    protected function tearDown(): void
    {
        putenv('TRUSTED_PROXIES');
        putenv('TRUST_CF_HEADER');
        unset($_ENV['TRUSTED_PROXIES'], $_ENV['TRUST_CF_HEADER']);
    }

    public function testExactIpMatch(): void
    {
        putenv('TRUSTED_PROXIES=10.0.0.5');
        $_ENV['TRUSTED_PROXIES'] = '10.0.0.5';
        $this->assertTrue(TrustedProxies::contains('10.0.0.5'));
        $this->assertFalse(TrustedProxies::contains('10.0.0.6'));
    }

    public function testCidrMatch(): void
    {
        putenv('TRUSTED_PROXIES=10.0.0.0/8');
        $_ENV['TRUSTED_PROXIES'] = '10.0.0.0/8';
        $this->assertTrue(TrustedProxies::contains('10.1.2.3'));
        $this->assertFalse(TrustedProxies::contains('11.0.0.1'));
    }

    public function testTrustCfHeaderDefaultsFalse(): void
    {
        putenv('TRUST_CF_HEADER');
        unset($_ENV['TRUST_CF_HEADER']);
        $this->assertFalse(TrustedProxies::trustCfConnectingIp());
    }

    public function testTrustCfHeaderOptIn(): void
    {
        putenv('TRUST_CF_HEADER=true');
        $_ENV['TRUST_CF_HEADER'] = 'true';
        $this->assertTrue(TrustedProxies::trustCfConnectingIp());
    }
}
