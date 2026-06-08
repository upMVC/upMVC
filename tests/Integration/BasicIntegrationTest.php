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
        // v2.0+ canonical entry point is public/index.php
        $this->assertFileExists(__DIR__ . '/../../public/index.php');
    }
}
