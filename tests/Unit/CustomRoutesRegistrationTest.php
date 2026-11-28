<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Etc\InitModsImproved;
use App\Etc\Routes;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class CustomRoutesRegistrationTest extends TestCase
{
    public function testInitModsImprovedNoLongerRegistersCustomRoutes(): void
    {
        $ref = new ReflectionClass(InitModsImproved::class);
        $this->assertFalse(
            $ref->hasMethod('registerCustomRoutes'),
            'custom-routes must not live inside InitModsImproved (breaks pack→app order)'
        );
        $this->assertFalse($ref->hasProperty('customRoutesRegistered'));
    }

    public function testRoutesRegistersCustomRoutesAfterModuleLoop(): void
    {
        $ref = new ReflectionClass(Routes::class);
        $this->assertTrue($ref->hasMethod('registerCustomRoutes'));

        $src = file_get_contents((string) $ref->getFileName());
        $this->assertNotFalse($src);
        $foreachPos = strpos($src, 'getModulePathsForRoutes');
        $customPos = strpos($src, '$this->registerCustomRoutes()');
        $this->assertNotFalse($foreachPos);
        $this->assertNotFalse($customPos);
        $this->assertGreaterThan(
            $foreachPos,
            $customPos,
            'registerCustomRoutes() must run after the module-path foreach'
        );
    }
}
