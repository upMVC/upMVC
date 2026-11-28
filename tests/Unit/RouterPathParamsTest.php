<?php

namespace Tests\Unit;

use App\Etc\Router;
use PHPUnit\Framework\TestCase;

/**
 * Stub controller: captures $_GET['id'] after Router dispatch.
 */
final class RouterPathParamsProbeController
{
    public static mixed $seenId = null;

    public function show(string $route, string $method): void
    {
        self::$seenId = $_GET['id'] ?? null;
    }
}

class RouterPathParamsTest extends TestCase
{
    protected function tearDown(): void
    {
        $_GET = [];
        RouterPathParamsProbeController::$seenId = null;
    }

    public function testDispatcherPathParamsOverwriteQueryPollution(): void
    {
        $_GET = ['id' => '999'];

        $router = new Router();
        $router->addParamRoute(
            '/item/{id:int}',
            RouterPathParamsProbeController::class,
            'show',
            [],
            ['id' => '\d+']
        );

        $router->dispatcher('/item/42', 'GET', '/item/42?id=999');

        $this->assertSame(42, $_GET['id']);
        $this->assertSame(42, RouterPathParamsProbeController::$seenId);
    }
}
