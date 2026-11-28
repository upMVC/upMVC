<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Etc\Config;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class CleanUrlSitePathTest extends TestCase
{
    private function invoke(string $sitePath, string $reqUrl): string
    {
        $ref = new ReflectionClass(Config::class);
        $method = $ref->getMethod('cleanUrlSitePath');
        $method->setAccessible(true);
        return $method->invoke($ref->newInstanceWithoutConstructor(), $sitePath, $reqUrl);
    }

    public function testStripsOnlyAtSegmentBoundary(): void
    {
        $this->assertSame('/users', $this->invoke('/upMVC', '/upMVC/users'));
        $this->assertSame('/users?x=1', $this->invoke('/upMVC', '/upMVC/users?x=1'));
        $this->assertSame('/', $this->invoke('/upMVC', '/upMVC'));
        $this->assertSame('/', $this->invoke('/upMVC/', '/upMVC'));
        $this->assertSame('/other/upMVC/users', $this->invoke('/upMVC', '/other/upMVC/users'));
    }

    public function testDoesNotEatSharedPrefixes(): void
    {
        $this->assertSame('/apple', $this->invoke('/app', '/apple'));
        $this->assertSame('/app-admin', $this->invoke('/app', '/app-admin'));
        $this->assertSame('/upMVC2/users', $this->invoke('/upMVC', '/upMVC2/users'));
    }
}
