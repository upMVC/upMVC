<?php

namespace Tests\Unit\Routing;

use App\Etc\Start;
use PHPUnit\Framework\TestCase;

class ProtectedRoutesResolveTest extends TestCase
{
    public function test_empty_env_and_empty_defaults_yields_only_application_routes(): void
    {
        $list = Start::resolveProtectedRouteList('', [], ['/platform-admin', '/app']);
        $this->assertSame(['/platform-admin', '/app'], $list);
    }

    public function test_empty_env_keeps_defaults_then_unions_application(): void
    {
        $list = Start::resolveProtectedRouteList(
            '',
            ['/legacy-default'],
            ['/from-pack']
        );
        $this->assertSame(['/legacy-default', '/from-pack'], $list);
    }

    public function test_non_empty_env_replaces_defaults_and_unions_application(): void
    {
        $list = Start::resolveProtectedRouteList(
            ' /admin/*, /account/* ',
            ['/should-not-appear'],
            ['/from-pack']
        );
        $this->assertSame(['/admin/*', '/account/*', '/from-pack'], $list);
        $this->assertNotContains('/should-not-appear', $list);
    }

    public function test_empty_env_and_no_application_is_empty_list(): void
    {
        $this->assertSame([], Start::resolveProtectedRouteList('', [], []));
    }

    public function test_thin_core_stock_defaults_constant_is_empty(): void
    {
        $ref = new \ReflectionClass(Start::class);
        $prop = $ref->getProperty('defaultProtectedRoutes');
        $prop->setAccessible(true);
        $this->assertSame([], $prop->getValue());
    }
}
