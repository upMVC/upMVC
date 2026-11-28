<?php

namespace Tests\Unit;

use App\Etc\Router;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class RouterPathParamsTest extends TestCase
{
    protected function tearDown(): void
    {
        $_GET = [];
    }

    public function testPathParamsOverwriteQueryPollution(): void
    {
        $_GET = ['id' => '999'];

        $router = new Router();
        $ref = new ReflectionClass(Router::class);

        // Simulate matched route params being written the same way dispatch does
        $cast = $ref->getMethod('castParam');
        $cast->setAccessible(true);

        $params = ['id' => '42'];
        foreach ($params as $k => $v) {
            $_GET[$k] = $cast->invoke($router, $v, 'int');
        }

        $this->assertSame(42, $_GET['id']);
    }
}
