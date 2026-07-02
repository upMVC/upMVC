<?php

namespace Tests\Unit;

use App\Etc\Application;
use PHPUnit\Framework\TestCase;

class ApplicationTest extends TestCase
{
    public function test_getInstance_returns_same_instance(): void
    {
        $a = Application::getInstance();
        $b = Application::getInstance();
        $this->assertSame($a, $b);
    }

    public function test_getAppRoot_is_a_real_directory(): void
    {
        $root = Application::getInstance()->getAppRoot();
        $this->assertDirectoryExists($root);
    }

    public function test_getAppRoot_contains_composer_json(): void
    {
        $root = Application::getInstance()->getAppRoot();
        $this->assertFileExists($root . '/composer.json');
    }

    public function test_path_with_no_argument_returns_app_root(): void
    {
        $app = Application::getInstance();
        $this->assertSame($app->getAppRoot(), $app->path());
    }

    public function test_path_constructs_absolute_path(): void
    {
        $app  = Application::getInstance();
        $path = $app->path('src/Etc');
        $this->assertStringStartsWith($app->getAppRoot(), $path);
        $this->assertStringEndsWith('src/Etc', $path);
    }

    public function test_path_to_src_etc_exists(): void
    {
        $this->assertDirectoryExists(Application::getInstance()->path('src/Etc'));
    }

    public function test_path_to_src_modules_exists(): void
    {
        $this->assertDirectoryExists(Application::getInstance()->path('src/Modules'));
    }

    public function test_addProtectedRoutes_stores_routes(): void
    {
        $app = Application::getInstance();
        $app->addProtectedRoutes(['/test-protected-route']);
        $this->assertContains('/test-protected-route', $app->getProtectedRoutes());
    }

    public function test_addModulePath_stores_path(): void
    {
        $app  = Application::getInstance();
        $path = $app->getAppRoot() . '/src/Modules';
        $this->assertContains($path, $app->getModulePaths());
    }
}
