<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;

/**
 * Integration tests
 */
class BasicIntegrationTest extends TestCase
{
    public function testComposerAutoloadExists(): void
    {
        $this->assertFileExists(__DIR__ . '/../../vendor/autoload.php');
    }

    public function testIndexPhpExists(): void
    {
        $this->assertFileExists(__DIR__ . '/../../index.php');
    }
}
