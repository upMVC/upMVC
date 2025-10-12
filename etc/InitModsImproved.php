<?php

namespace upMVC;

class InitModsImproved
{
    private string $modulesPath;
    
    public function __construct(string $modulesPath = __DIR__ . '/../modules')
    {
        $this->modulesPath = $modulesPath;
    }

    public function addRoutes(Router $router): void
    {
        $this->autoDiscoverModules($router);
        $this->registerCustomRoutes($router);
    }

    private function autoDiscoverModules(Router $router): void
    {
        $modules = glob($this->modulesPath . '/*/routes/Routes.php');
        
        foreach ($modules as $routeFile) {
            $moduleName = basename(dirname(dirname($routeFile)));
            $className = ucfirst($moduleName) . '\\Routes\\Routes';
            
            if (class_exists($className)) {
                (new $className())->Routes($router);
            }
        }
    }

    private function registerCustomRoutes(Router $router): void
    {
        $customRoutes = $this->loadCustomRoutes();
        
        foreach ($customRoutes as $route) {
            $router->addRoute($route['path'], $route['controller'], $route['method']);
        }
    }

    private function loadCustomRoutes(): array
    {
        $configFile = __DIR__ . '/custom-routes.php';
        return file_exists($configFile) ? include $configFile : [];
    }
}