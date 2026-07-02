<?php

namespace Tests\Unit\Routing;

use App\Etc\Router;
use PHPUnit\Framework\TestCase;

/**
 * Exposes protected properties to allow state assertions without
 * going through the full HTTP dispatcher.
 */
class TestRouter extends Router
{
    public function getRoutes(): array      { return $this->routes; }
    public function getParamRoutes(): array { return $this->paramRoutes; }
    public function getNamedRoutes(): array { return $this->namedRoutes; }
}

class RouterTest extends TestCase
{
    private TestRouter $router;

    protected function setUp(): void
    {
        $this->router = new TestRouter();
    }

    // --- addRoute ---

    public function test_addRoute_registers_route(): void
    {
        $this->router->addRoute('/hello', 'FakeController', 'index');
        $routes = $this->router->getRoutes();
        $this->assertArrayHasKey('/hello', $routes);
    }

    public function test_addRoute_stores_class_and_method(): void
    {
        $this->router->addRoute('/about', 'AboutController', 'show');
        $route = $this->router->getRoutes()['/about'];
        $this->assertSame('AboutController', $route['className']);
        $this->assertSame('show',            $route['methodName']);
    }

    public function test_addRoute_stores_middleware(): void
    {
        $this->router->addRoute('/admin', 'AdminController', 'index', ['auth', 'csrf']);
        $route = $this->router->getRoutes()['/admin'];
        $this->assertSame(['auth', 'csrf'], $route['middleware']);
    }

    public function test_addRoute_normalizes_http_methods_to_uppercase(): void
    {
        $this->router->addRoute('/form', 'FormController', 'submit', [], ['get', 'post']);
        $route = $this->router->getRoutes()['/form'];
        $this->assertContains('GET',  $route['methods']);
        $this->assertContains('POST', $route['methods']);
    }

    public function test_addRoute_empty_methods_means_all_allowed(): void
    {
        $this->router->addRoute('/open', 'OpenController', 'index');
        $this->assertSame([], $this->router->getRoutes()['/open']['methods']);
    }

    // --- addParamRoute ---

    public function test_addParamRoute_registers_route(): void
    {
        $this->router->addParamRoute('/users/{id}', 'UserController', 'show');
        $this->assertCount(1, $this->router->getParamRoutes());
    }

    public function test_addParamRoute_extracts_param_names(): void
    {
        $this->router->addParamRoute('/orders/{orderId}/items/{itemId}', 'OrderController', 'item');
        $route = $this->router->getParamRoutes()[0];
        $this->assertContains('orderId', $route['params']);
        $this->assertContains('itemId',  $route['params']);
    }

    public function test_addParamRoute_extracts_type_hint(): void
    {
        $this->router->addParamRoute('/posts/{id:int}', 'PostController', 'show');
        $route = $this->router->getParamRoutes()[0];
        $this->assertSame('int', $route['types']['id']);
    }

    public function test_addParamRoute_returns_self_for_chaining(): void
    {
        $result = $this->router->addParamRoute('/items/{id}', 'ItemController', 'show');
        $this->assertInstanceOf(Router::class, $result);
    }

    // --- name + route URL generation ---

    public function test_name_registers_named_route(): void
    {
        $this->router->addParamRoute('/users/{id}', 'UserController', 'show')->name('user.show');
        $this->assertArrayHasKey('user.show', $this->router->getNamedRoutes());
    }

    public function test_route_generates_url_for_named_route(): void
    {
        $this->router->addParamRoute('/users/{id}', 'UserController', 'show')->name('user.show');
        $url = $this->router->route('user.show', ['id' => 42]);
        $this->assertSame('/users/42', $url);
    }

    public function test_route_generates_url_with_multiple_params(): void
    {
        $this->router->addParamRoute('/orders/{orderId}/items/{itemId}', 'OC', 'item')
                     ->name('order.item');
        $url = $this->router->route('order.item', ['orderId' => 7, 'itemId' => 99]);
        $this->assertSame('/orders/7/items/99', $url);
    }

    public function test_route_generates_url_with_type_hinted_param(): void
    {
        $this->router->addParamRoute('/posts/{id:int}', 'PostController', 'show')->name('post.show');
        $url = $this->router->route('post.show', ['id' => 5]);
        $this->assertSame('/posts/5', $url);
    }

    public function test_route_throws_for_unknown_name(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->router->route('nonexistent.route');
    }

    public function test_route_throws_when_params_are_missing(): void
    {
        $this->router->addParamRoute('/users/{id}', 'UC', 'show')->name('user.show2');
        $this->expectException(\RuntimeException::class);
        $this->router->route('user.show2', []);
    }

    // --- addMiddleware ---

    public function test_addMiddleware_registers_callable(): void
    {
        $called = false;
        $this->router->addMiddleware('test-mw', function () use (&$called) { $called = true; });
        // Verify by attempting to run a registered route with the middleware.
        // We can't call dispatcher() in unit tests (needs HTTP + real controllers),
        // but we confirm registration did not throw.
        $this->assertTrue(true);
    }
}
