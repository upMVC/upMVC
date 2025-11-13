<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Etc\Router;

/**
 * Unit tests for Router class
 */
class RouterTest extends TestCase
{
    public function testRouterCanBeInstantiated(): void
    {
        $this->assertTrue(class_exists('App\\Etc\\Router'));
    }
}
