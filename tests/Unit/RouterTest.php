<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use upMVC\Router;

/**
 * Unit tests for Router class
 */
class RouterTest extends TestCase
{
    public function testRouterCanBeInstantiated(): void
    {
        $this->assertTrue(class_exists('upMVC\\Router'));
    }
}
