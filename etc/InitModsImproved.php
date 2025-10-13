<?php

namespace upMVC;

use upMVC\Cache\CacheManager;
use upMVC\Config\Environment;
use upMVC\Exceptions\RouteNotFoundException;

class InitModsImproved
{
    private string $modulesPath;
    private bool $useCache;
    private bool $enableErrorHandling;
    private bool $enableVerboseLogging;
    private bool $enableSubmoduleDiscovery;
    private bool $enableDebugOutput;
    
    public function __construct(string $modulesPath = __DIR__ . '/../modules')
    {
        $this->modulesPath = $modulesPath;
        
        // Force Environment to load if not already loaded
        if (class_exists('upMVC\\Config\\Environment')) {
            Environment::load();
        }
        
        $this->useCache = Environment::isProduction(); // Only cache in production
        
        // Debug mode - only enabled with special flag
        $this->enableDebugOutput = filter_var(
            Environment::get('ROUTE_DEBUG_OUTPUT', 'false'), 
            FILTER_VALIDATE_BOOLEAN
        );
        
        // Error handling configuration (can be controlled via .env)
        $this->enableErrorHandling = filter_var(
            Environment::get('ROUTE_ERROR_HANDLING', 'true'), 
            FILTER_VALIDATE_BOOLEAN
        );
        $this->enableVerboseLogging = filter_var(
            Environment::get('ROUTE_VERBOSE_LOGGING', Environment::isDevelopment() ? 'true' : 'false'), 
            FILTER_VALIDATE_BOOLEAN
        );
        $this->enableSubmoduleDiscovery = filter_var(
            Environment::get('ROUTE_SUBMODULE_DISCOVERY', 'true'), 
            FILTER_VALIDATE_BOOLEAN
        );
    }

    public function addRoutes(Router $router): void
    {
        // Debug output only if explicitly enabled
        if ($this->enableDebugOutput) {
            echo "<div style='background: #f0f0f0; padding: 10px; margin: 5px; border: 1px solid #ccc;'>";
            echo "<strong>[InitModsImproved Debug]</strong><br>";
            echo "Environment: " . Environment::current() . "<br>";
            echo "Use Cache: " . ($this->useCache ? 'YES' : 'NO') . "<br>";
            echo "Submodule Discovery: " . ($this->enableSubmoduleDiscovery ? 'ENABLED' : 'DISABLED') . "<br>";
            echo "Debug Output: " . ($this->enableDebugOutput ? 'ENABLED' : 'DISABLED') . "<br>";
            echo "Modules Path: " . $this->modulesPath . "<br>";
            echo "</div>";
        }
        
        if ($this->useCache) {
            $this->addRoutesCached($router);
        } else {
            $this->addRoutesDirect($router);
        }
    }

    private function addRoutesCached(Router $router): void
    {
        // Create cache key that includes configuration state
        $cacheKey = 'discovered_modules_' . md5(json_encode([
            'submodule_discovery' => $this->enableSubmoduleDiscovery,
            'modules_path' => $this->modulesPath
        ]));
        
        $cachedModules = CacheManager::get($cacheKey);
        
        if ($cachedModules === null) {
            // Cache miss - discover modules and cache the result
            $cachedModules = $this->discoverModules();
            CacheManager::put($cacheKey, $cachedModules, 3600); // Cache for 1 hour
            
            // Clear old cache entries with different configurations
            CacheManager::forget('discovered_modules'); // Old simple key
        }
        
        // Register the cached modules with error handling
        foreach ($cachedModules as $moduleData) {
            $this->registerModuleRoutes($router, $moduleData);
        }
        
        $this->registerCustomRoutes($router);
    }

    private function addRoutesDirect(Router $router): void
    {
        // Development mode - always scan fresh
        if ($this->enableDebugOutput) {
            echo "<div style='color: blue; padding: 3px;'>[InitModsImproved] Direct mode - scanning modules fresh...</div>";
        }
        
        $this->autoDiscoverModules($router);
        $this->registerCustomRoutes($router);
    }

    private function discoverModules(): array
    {
        $moduleData = [];
        
        // Primary modules: modules/*/routes/Routes.php
        $primaryModules = glob($this->modulesPath . '/*/routes/Routes.php');
        foreach ($primaryModules as $routeFile) {
            $moduleData[] = $this->createModuleData($routeFile, 'primary');
        }
        
        // Debug output for submodule discovery
        if ($this->enableDebugOutput) {
            echo "<div style='color: purple; padding: 3px;'>[InitModsImproved] Primary modules found: " . count($primaryModules) . "</div>";
        }
        
        // Submodules discovery (if enabled)
        if ($this->enableSubmoduleDiscovery) {
            // Submodules: modules/*/modules/*/routes/Routes.php (like moda/modules/suba)
            $subModules = glob($this->modulesPath . '/*/modules/*/routes/Routes.php');
            foreach ($subModules as $routeFile) {
                $moduleData[] = $this->createModuleData($routeFile, 'sub');
            }
            
            // Deep submodules: modules/*/modules/*/modules/*/routes/Routes.php (if needed)
            $deepSubModules = glob($this->modulesPath . '/*/modules/*/modules/*/routes/Routes.php');
            foreach ($deepSubModules as $routeFile) {
                $moduleData[] = $this->createModuleData($routeFile, 'deep');
            }
            
            // Debug output for submodules
            if ($this->enableDebugOutput) {
                echo "<div style='color: orange; padding: 3px;'>[InitModsImproved] Submodules found: " . count($subModules) . "</div>";
                echo "<div style='color: red; padding: 3px;'>[InitModsImproved] Deep submodules found: " . count($deepSubModules) . "</div>";
                echo "<div style='color: green; padding: 3px;'>[InitModsImproved] Total modules discovered: " . count($moduleData) . "</div>";
            }
        } else {
            if ($this->enableDebugOutput) {
                echo "<div style='color: red; padding: 3px;'>[InitModsImproved] Submodule discovery is DISABLED</div>";
            }
        }
        
        return $moduleData;
    }

    /**
     * Create module data array from route file path
     */
    private function createModuleData(string $routeFile, string $type): array
    {
        $relativePath = str_replace($this->modulesPath . '/', '', dirname(dirname($routeFile)));
        
        // Extract module name and build class name based on type
        switch ($type) {
            case 'primary':
                // modules/admin/routes/Routes.php -> Admin\Routes\Routes
                $moduleName = basename(dirname(dirname($routeFile)));
                $className = ucfirst($moduleName) . '\\Routes\\Routes';
                break;
                
            case 'sub':
                // modules/moda/modules/suba/routes/Routes.php -> Suba\Routes\Routes
                $parts = explode('/', $relativePath);
                $subModuleName = end($parts); // Get last part (suba)
                $className = ucfirst($subModuleName) . '\\Routes\\Routes';
                break;
                
            case 'deep':
                // modules/parent/modules/child/modules/grandchild/routes/Routes.php
                $parts = explode('/', $relativePath);
                $deepModuleName = end($parts);
                $className = ucfirst($deepModuleName) . '\\Routes\\Routes';
                break;
                
            default:
                $moduleName = basename(dirname(dirname($routeFile)));
                $className = ucfirst($moduleName) . '\\Routes\\Routes';
        }
        
        return [
            'name' => $moduleName ?? $subModuleName ?? $deepModuleName ?? 'unknown',
            'className' => $className,
            'file' => $routeFile,
            'type' => $type,
            'path' => $relativePath,
            'modified' => filemtime($routeFile)
        ];
    }

    private function autoDiscoverModules(Router $router): void
    {
        $modules = $this->discoverModules();
        
        foreach ($modules as $moduleData) {
            $this->registerModuleRoutes($router, $moduleData);
        }
    }

    /**
     * Register routes for a specific module with comprehensive error handling
     */
    private function registerModuleRoutes(Router $router, array $moduleData): void
    {
        $className = $moduleData['className'];
        $moduleName = $moduleData['name'];
        $routeFile = $moduleData['file'];
        
        // Simple mode - just try to load without extensive error handling
        if (!$this->enableErrorHandling) {
            if (class_exists($className)) {
                try {
                    (new $className())->Routes($router);
                } catch (\Exception $e) {
                    // Basic error logging only
                    error_log("Route loading failed for module: {$moduleName} - " . $e->getMessage());
                }
            }
            return;
        }
        
        // Full error handling mode
        try {
            // Check if class exists
            if (!class_exists($className)) {
                $this->logError("Route class not found: {$className} for module: {$moduleName}");
                return;
            }
            
            // Check if Routes method exists
            if (!method_exists($className, 'Routes')) {
                $this->logError("Routes method not found in class: {$className} for module: {$moduleName}");
                return;
            }
            
            // Instantiate and call Routes method
            $routeInstance = new $className();
            
            // Validate that Routes method accepts router parameter
            $reflection = new \ReflectionMethod($className, 'Routes');
            if ($reflection->getNumberOfRequiredParameters() > 1) {
                $this->logError("Routes method in {$className} has incorrect signature (too many required parameters)");
                return;
            }
            
            // Call the Routes method with error catching
            $routeInstance->Routes($router);
            
            // Log successful registration in development
            if ($this->enableVerboseLogging) {
                $this->logInfo("Successfully registered routes for module: {$moduleName}");
            }
            
        } catch (\TypeError $e) {
            $this->logError("Type error in module {$moduleName}: " . $e->getMessage());
        } catch (\ArgumentCountError $e) {
            $this->logError("Argument error in module {$moduleName}: " . $e->getMessage());
        } catch (\ParseError $e) {
            $this->logError("Parse error in route file {$routeFile}: " . $e->getMessage());
        } catch (\Error $e) {
            $this->logError("Fatal error in module {$moduleName}: " . $e->getMessage());
        } catch (\Exception $e) {
            $this->logError("Exception in module {$moduleName}: " . $e->getMessage());
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

    /**
     * Clear the module discovery cache (useful for deployments)
     */
    public function clearCache(): void
    {
        // Clear all possible cache variations
        CacheManager::forget('discovered_modules'); // Old key
        
        // Clear current configuration cache
        $cacheKey = 'discovered_modules_' . md5(json_encode([
            'submodule_discovery' => $this->enableSubmoduleDiscovery,
            'modules_path' => $this->modulesPath
        ]));
        CacheManager::forget($cacheKey);
        
        // Clear common configuration variations
        $configs = [
            ['submodule_discovery' => true, 'modules_path' => $this->modulesPath],
            ['submodule_discovery' => false, 'modules_path' => $this->modulesPath]
        ];
        
        foreach ($configs as $config) {
            $key = 'discovered_modules_' . md5(json_encode($config));
            CacheManager::forget($key);
        }
    }

    /**
     * Log error messages for route discovery issues
     */
    private function logError(string $message): void
    {
        $logMessage = "[InitModsImproved] ERROR: {$message}";
        error_log($logMessage);
        
        // Screen output only if enabled
        if ($this->enableDebugOutput) {
            echo "<div style='color: red; padding: 5px; border: 1px solid red; margin: 5px;'>{$logMessage}</div>";
        }
    }

    /**
     * Log info messages for route discovery
     */
    private function logInfo(string $message): void
    {
        $logMessage = "[InitModsImproved] INFO: {$message}";
        error_log($logMessage);
        
        // Screen output only if enabled
        if ($this->enableDebugOutput) {
            echo "<div style='color: green; padding: 3px; margin: 2px; font-size: 12px;'>{$logMessage}</div>";
        }
    }

    /**
     * Get error statistics for monitoring
     */
    public function getStats(): array
    {
        return [
            'cache_enabled' => $this->useCache,
            'error_handling_enabled' => $this->enableErrorHandling,
            'verbose_logging_enabled' => $this->enableVerboseLogging,
            'screen_output_enabled' => $this->enableDebugOutput,
            'modules_path' => $this->modulesPath,
            'environment' => Environment::current(),
            'cache_key' => 'discovered_modules'
        ];
    }

    /**
     * Enable/disable error handling at runtime
     */
    public function setErrorHandling(bool $enabled): self
    {
        $this->enableErrorHandling = $enabled;
        return $this;
    }

    /**
     * Enable/disable verbose logging at runtime
     */
    public function setVerboseLogging(bool $enabled): self
    {
        $this->enableVerboseLogging = $enabled;
        return $this;
    }

    /**
     * Enable/disable debug output at runtime
     */
    public function setDebugOutput(bool $enabled): self
    {
        $this->enableDebugOutput = $enabled;
        return $this;
    }

    /**
     * Enable/disable submodule discovery at runtime
     */
    public function setSubmoduleDiscovery(bool $enabled): self
    {
        $this->enableSubmoduleDiscovery = $enabled;
        return $this;
    }
}