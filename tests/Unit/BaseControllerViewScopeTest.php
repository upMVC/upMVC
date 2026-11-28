<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Common\Bmvc\BaseControllerOrm;
use PHPUnit\Framework\TestCase;

final class BaseControllerViewScopeTest extends TestCase
{
    public function testRejectsParentDirectoryTraversal(): void
    {
        if (!defined('THIS_DIR')) {
            define('THIS_DIR', dirname(__DIR__, 2) . '/src');
        }

        $ctrl = new class extends BaseControllerOrm {
            public function expose(string $path, array $data): void
            {
                $this->view($path, $data);
            }
        };

        $this->expectException(\InvalidArgumentException::class);
        $ctrl->expose('../Etc/Config', ['base' => 'https://example.com']);
    }
}
