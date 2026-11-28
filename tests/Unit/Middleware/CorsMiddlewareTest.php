<?php

declare(strict_types=1);

namespace Tests\Unit\Middleware;

use App\Etc\Middleware\CorsMiddleware;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class CorsMiddlewareTest extends TestCase
{
    public function testConstructorTrimsOrigins(): void
    {
        $cors = new CorsMiddleware([
            'origins' => ' https://a.example , https://b.example ',
            'credentials' => false,
        ]);

        $ref = new ReflectionClass($cors);
        $prop = $ref->getProperty('allowedOrigins');
        $prop->setAccessible(true);

        $this->assertSame(['https://a.example', 'https://b.example'], $prop->getValue($cors));
    }

    public function testWildcardWithCredentialsDoesNotReflectOrigin(): void
    {
        $cors = new CorsMiddleware([
            'origins' => ['*'],
            'credentials' => true,
        ]);

        $ref = new ReflectionClass($cors);
        $allowed = $ref->getMethod('isOriginAllowed');
        $allowed->setAccessible(true);

        // '*' is not an explicit whitelist entry for a concrete Origin
        $this->assertFalse($allowed->invoke($cors, 'https://evil.example'));

        $creds = $ref->getProperty('allowCredentials');
        $creds->setAccessible(true);
        $this->assertTrue($creds->getValue($cors));
    }
}
