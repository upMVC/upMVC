<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

/**
 * Example test case to verify PHPUnit setup
 */
class ExampleTest extends TestCase
{
    public function testBasicAssertion(): void
    {
        $this->assertTrue(true);
        $this->assertEquals(4, 2 + 2);
    }

    public function testPhpVersion(): void
    {
        $this->assertGreaterThanOrEqual(8.1, (float)PHP_VERSION);
    }

    public function testAutoloaderWorks(): void
    {
        $this->assertTrue(class_exists('App\\Etc\\Start'));
    }
}
