<?php

namespace App\Etc;

/**
 * Runtime application registry for library-mode integrations.
 *
 * The kernel can live either in the project root or under vendor/. This class
 * keeps app-owned paths and package hooks separate from kernel internals.
 */
class Application
{
    private static ?self $instance = null;

    private string $appRoot;
    private array $modulePaths = [];
    private array $migrationPaths = [];
    private array $protectedRoutes = [];
    private array $providers = [];
    private bool $providersRegistered = false;
    private bool $providersBooted = false;

    private function __construct(?string $appRoot = null)
    {
        $this->appRoot = $this->normalizePath(
            $appRoot
            ?? (defined('UPMVC_APP_ROOT') ? (string) UPMVC_APP_ROOT : dirname(__DIR__, 2))
        );

        $this->addModulePath($this->path('src/Modules'));
    }

    public static function getInstance(?string $appRoot = null): self
    {
        if (self::$instance === null) {
            self::$instance = new self($appRoot);
        }

        return self::$instance;
    }

    public function getAppRoot(): string
    {
        return $this->appRoot;
    }

    public function path(string $relativePath = ''): string
    {
        $relativePath = trim(str_replace('\\', '/', $relativePath), '/');
        return $relativePath === '' ? $this->appRoot : $this->appRoot . '/' . $relativePath;
    }

    public function addModulePath(string $path, bool $prepend = false): void
    {
        $path = $this->normalizePath($path);
        if (in_array($path, $this->modulePaths, true)) {
            return;
        }

        if ($prepend) {
            array_unshift($this->modulePaths, $path);
            return;
        }

        $this->modulePaths[] = $path;
    }

    public function getModulePaths(): array
    {
        return $this->modulePaths;
    }

    /**
     * Return module paths in route-registration order.
     *
     * The application module path is registered first by default, but it should
     * be scanned last so app routes can override package routes.
     *
     * @return array
     */
    public function getModulePathsForRoutes(): array
    {
        if (count($this->modulePaths) <= 1) {
            return $this->modulePaths;
        }

        $paths = $this->modulePaths;
        $appPath = array_shift($paths);
        $paths[] = $appPath;

        return $paths;
    }

    public function addMigrationPath(string $path): void
    {
        $path = $this->normalizePath($path);
        if (!in_array($path, $this->migrationPaths, true)) {
            $this->migrationPaths[] = $path;
        }
    }

    public function getMigrationPaths(): array
    {
        return $this->migrationPaths;
    }

    public function addProtectedRoute(string $route): void
    {
        if (!in_array($route, $this->protectedRoutes, true)) {
            $this->protectedRoutes[] = $route;
        }
    }

    public function addProtectedRoutes(array $routes): void
    {
        foreach ($routes as $route) {
            $this->addProtectedRoute((string) $route);
        }
    }

    public function getProtectedRoutes(): array
    {
        return $this->protectedRoutes;
    }

    public function registerProviders(): void
    {
        if ($this->providersRegistered) {
            return;
        }

        foreach ($this->loadProviderClasses() as $providerClass) {
            if (!class_exists($providerClass)) {
                error_log("[upMVC] Package provider not found: {$providerClass}");
                continue;
            }

            $provider = new $providerClass();
            $this->providers[] = $provider;

            if (method_exists($provider, 'register')) {
                $provider->register($this);
            }
        }

        $this->providersRegistered = true;
    }

    public function bootProviders(Router $router): void
    {
        if ($this->providersBooted) {
            return;
        }

        $this->registerProviders();

        foreach ($this->providers as $provider) {
            if (method_exists($provider, 'boot')) {
                $provider->boot($this, $router);
            }
        }

        $this->providersBooted = true;
    }

    private function loadProviderClasses(): array
    {
        $packagesFile = $this->path('src/Etc/packages.php');
        if (!file_exists($packagesFile)) {
            return [];
        }

        $providers = include $packagesFile;
        return is_array($providers) ? array_values($providers) : [];
    }

    private function normalizePath(string $path): string
    {
        return rtrim(str_replace('\\', '/', $path), '/');
    }
}
