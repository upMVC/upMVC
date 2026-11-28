<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Etc\Config;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class CleanUrlSitePathTest extends TestCase
{
    public function testStripsOnlyPrefix(): void
    {
        $ref = new ReflectionClass(Config::class);
        $method = $ref->getMethod('cleanUrlSitePath');
        $method->setAccessible(true);
        $cfg = $ref->newInstanceWithoutConstructor();

        $this->assertSame('/users', $method->invoke($cfg, '/upMVC', '/upMVC/users'));
        // Query string is stripped later by cleanUrlQuestionMark — this method only does prefix
        $this->assertSame('/users?x=1', $method->invoke($cfg, '/upMVC', '/upMVC/users?x=1'));
        // Must not strip SITE_PATH mid-path
        $this->assertSame('/other/upMVC/users', $method->invoke($cfg, '/upMVC', '/other/upMVC/users'));
        $this->assertSame('/', $method->invoke($cfg, '/upMVC', '/upMVC'));
    }
}
