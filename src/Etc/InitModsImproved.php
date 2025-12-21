<?php
/**
 * InitModsImproved.php - Advanced Module Discovery and Route Registration
 * 
 * This class provides intelligent module discovery and route loading for upMVC:
 * - Automatic module discovery from filesystem
 * - Hierarchical module support (primary, sub, deep modules)
 * - Production caching for performance
 * - Comprehensive error handling
 * - Configurable via .env settings
 * 
 * Module Discovery Patterns:
 * - Primary: Modules/STAR/Routes/Routes.php
 * - Submodules: Modules/STAR/Modules/STAR/Routes/Routes.php
 * - Deep: Modules/STAR/Modules/STAR/Modules/STAR/Routes/Routes.php
 * 
 * Configuration (.env):
 * - ROUTE_ERROR_HANDLING: Enable/disable error handling (default: true)
 * - ROUTE_VERBOSE_LOGGING: Enable detailed logging (default: dev only)
 * - ROUTE_DEBUG_OUTPUT: Enable screen debug output (default: false)
 * - ROUTE_SUBMODULE_DISCOVERY: Enable submodule discovery (default: true)
 * 
 * @package upMVC
 * @author BitsHost
 * @copyright 2023 BitsHost
 * @license MIT License
 * @link https://bitshost.biz/
 */

namespace App\Etc;

use App\Etc\Cache\CacheManager;
use App\Etc\Config\Environment;
use App\Etc\Exceptions\RouteNotFoundException;

class InitModsImproved
{
    // ========================================
    // Properties
    // ========================================
    
    /**
     * Path to modules directory
     * 
     * @var string
     */
    private string $modulesPath;
    
    /**
     * Enable production caching for discovered modules
     * 
     * @var bool
     */
    private bool $useCache;
    
    /**
     * Enable comprehensive error handling during route registration
     * 
     * @var bool
     */
    private bool $enableErrorHandling;
    
    /**
     * Enable verbose logging of route registration process
     * 
     * @var bool
     */
    private bool $enableVerboseLogging;
    
    /**
     * Enable discovery of nested submodules
     * 
     * @var bool
     */
    private bool $enableSubmoduleDiscovery;
    
    /**
     * Enable debug output to screen (HTML)
     * 
     * @var bool
     */
    private bool $enableDebugOutput;
    
    // ========================================
    // Initialization
    // ========================================
    
    /**
     * Constructor - Initialize module discovery system
     * 
     * Loads configuration from .env and sets up discovery parameters.
     * In production, caching is automatically enabled for performance.
     * 
     * @param string $modulesPath Path to modules directory (default: ../modules)
     */
    public function __construct(string $modulesPath = __DIR__ . '/../Modules')
    {
        $this->modulesPath = $modulesPath;
        
        // Ensure Environment is loaded
        if (class_exists('App\\Etc\\Config\\Environment')) {
            Environment::load();
        }
        
        // Cache only in production for performance
        $this->useCache = Environment::isProduction();
        
        // Load configuration from .env with sensible defaults
        $this->enableDebugOutput = filter_var(
            Environment::get('ROUTE_DEBUG_OUTPUT', 'false'), 
            FILTER_VALIDATE_BOOLEAN
        );
        
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

    // ========================================
    // Route Registration
    // ========================================

    /**
     * Add all discovered module routes to router
     * 
     * Main entry point called from Routes.php.
     * Uses cache in production, direct scan in development.
     * 
     * @param Router $router The router instance to register routes with
     * @return void
     */
    public function addRoutes(Router $router): void
    {
        // Optional debug output (only if explicitly enabled in .env)
        if ($this->enableDebugOutput) {
            echo "<div style='background: #f0f0f0; padding: 10px; margin: 5px; border: 1px solid #ccc;'>";
            echo "<strong>[InitModsImproved Debug]</strong><br>";
            echo "Environment: " . Environment::get('APP_ENV', 'production') . "<br>";
            echo "Use Cache: " . ($this->useCache ? 'YES' : 'NO') . "<br>";
            echo "Submodule Discovery: " . ($this->enableSubmoduleDiscovery ? 'ENABLED' : 'DISABLED') . "<br>";
            echo "Debug Output: ENABLED<br>";
            echo "Modules Path: " . $this->modulesPath . "<br>";
            echo "</div>";
        }
        
        if ($this->useCache) {
            $this->addRoutesCached($router);
        } else {
            $this->addRoutesDirect($router);
        }
    }

    // ========================================
    // Cache Management
    // ========================================

    /**
     * Add routes using cached module discovery (production mode)
     * 
     * Caches discovered modules for 1 hour to improve performance.
     * Cache key includes configuration to handle different setups.
     * 
     * @param Router $router The router instance
     * @return void
     */
    private function addRoutesCached(Router $router): void
    {
        // Create cache key including configuration state
        $cacheKey = 'discovered_modules_' . md5(json_encode([
            'submodule_discovery' => $this->enableSubmoduleDiscovery,
            'modules_path' => $this->modulesPath
        ]));
        
        $cachedModules = CacheManager::get($cacheKey);
        
        if ($cachedModules === null) {
            // Cache miss - discover and cache
            $cachedModules = $this->discoverModules();
            CacheManager::put($cacheKey, $cachedModules, 3600); // 1 hour TTL
            
            // Clean up old cache entries
            CacheManager::forget('discovered_modules');
        }
        
        // Register cached modules
        foreach ($cachedModules as $moduleData) {
            $this->registerModuleRoutes($router, $moduleData);
        }
        
        $this->registerCustomRoutes($router);
    }

    /**
     * Add routes with direct module discovery (development mode)
     * 
     * Always scans filesystem for fresh module list.
     * No caching for instant updates during development.
     * 
     * @param Router $router The router instance
     * @return void
     */
    private function addRoutesDirect(Router $router): void
    {
        if ($this->enableDebugOutput) {
            echo "<div style='color: blue; padding: 3px;'>[InitModsImproved] Direct mode - scanning modules fresh...</div>";
        }
        
        $this->autoDiscoverModules($router);
        $this->registerCustomRoutes($router);
    }

    /**
     * Clear all module discovery caches
     * 
     * Useful for deployments or when module structure changes.
     * Clears all cache variations to ensure clean state.
     * 
     * @return void
     */
    public function clearCache(): void
    {
        // Clear legacy cache key
        CacheManager::forget('discovered_modules');
        
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

    // ========================================
    // Module Discovery
    // ========================================

    /**
     * Discover all modules in the modules directory
     * 
     * Scans filesystem for module route files in hierarchical structure:
     * - Primary modules (Modules/name/Routes/Routes.php)
     * - Submodules (Modules/parent/Modules/child/Routes/Routes.php)
     * - Deep modules (3+ levels deep)
     * 
     * @return array Array of module data structures
     */
    private function discoverModules(): array
    {
        $moduleData = [];
        
        // Discover primary modules
        $primaryModules = glob($this->modulesPath . '/*/Routes/Routes.php');
        foreach ($primaryModules as $routeFile) {
            $moduleData[] = $this->createModuleData($routeFile, 'primary');
        }
        
        if ($this->enableDebugOutput) {
            echo "<div style='color: purple; padding: 3px;'>[InitModsImproved] Primary modules found: " . count($primaryModules) . "</div>";
        }
        
        // Discover submodules (if enabled)
        if ($this->enableSubmoduleDiscovery) {
            $subModules = glob($this->modulesPath . '/*/Modules/*/Routes/Routes.php');
            foreach ($subModules as $routeFile) {
                $moduleData[] = $this->createModuleData($routeFile, 'sub');
            }
            
            $deepSubModules = glob($this->modulesPath . '/*/Modules/*/Modules/*/Routes/Routes.php');
            foreach ($deepSubModules as $routeFile) {
                $moduleData[] = $this->createModuleData($routeFile, 'deep');
            }
            
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
     * Create module data structure from route file path
     * 
     * Extracts module information and builds proper namespace/class name
     * based on module type (primary, sub, or deep).
     * 
     * @param string $routeFile Full path to Routes.php file
     * @param string $type Module type ('primary', 'sub', or 'deep')
     * @return array Module data array with name, class, file, type, path, modified
     */
    private function createModuleData(string $routeFile, string $type): array
    {
        $relativePath = str_replace($this->modulesPath . '/', '', dirname(dirname($routeFile)));
        
        // Build class name based on module type
        switch ($type) {
            case 'primary':
                // Example: Modules/Admin/Routes/Routes.php -> App\Modules\Admin\Routes\Routes
                $moduleName = basename(dirname(dirname($routeFile)));
                $className = 'App\\Modules\\' . ucfirst($moduleName) . '\\Routes\\Routes';
                break;
                
            case 'sub':
                // Example: Modules/Moda/Modules/Suba/Routes/Routes.php -> App\Modules\Moda\Modules\Suba\Routes\Routes
                $parts = explode('/', $relativePath);
                $subModuleName = end($parts);
                $parentName = $parts[0] ?? '';
                $className = 'App\\Modules\\' . ucfirst($parentName) . '\\Modules\\' . ucfirst($subModuleName) . '\\Routes\\Routes';
                break;
                
            case 'deep':
                // Example: Modules/Parent/Modules/Child/Modules/Grandchild/Routes/Routes.php
                $parts = explode('/', $relativePath);
                $deepModuleName = end($parts);
                // Build full namespace path
                $namespaceParts = ['App', 'Modules'];
                for ($i = 0; $i < count($parts); $i++) {
                    if ($parts[$i] !== 'modules') {
                        $namespaceParts[] = ucfirst($parts[$i]);
                    } else {
                        $namespaceParts[] = 'Modules';
                    }
                }
                $namespaceParts[] = 'Routes';
                $namespaceParts[] = 'Routes';
                $className = implode('\\', $namespaceParts);
                break;
                
            default:
                $moduleName = basename(dirname(dirname($routeFile)));
                $className = 'App\\Modules\\' . ucfirst($moduleName) . '\\Routes\\Routes';
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

    /**
     * Discover and register all modules directly
     * 
     * Used in development mode for fresh scanning on each request.
     * 
     * @param Router $router The router instance
     * @return void
     */
    private function autoDiscoverModules(Router $router): void
    {
        $modules = $this->discoverModules();
        
        foreach ($modules as $moduleData) {
            $this->registerModuleRoutes($router, $moduleData);
        }
    }

    // ========================================
    // Route Registration
    // ========================================

    /**
     * Register routes for a specific module with error handling
     * 
     * Attempts to load and execute the Routes() method from module's Routes class.
     * Provides comprehensive error handling and validation.
     * 
     * Error handling includes:
     * - Class existence check
     * - Method existence check
     * - Method signature validation
     * - Exception catching (TypeError, ArgumentCountError, ParseError, Error, Exception)
     * 
     * @param Router $router The router instance
     * @param array $moduleData Module data structure
     * @return void
     */
    private function registerModuleRoutes(Router $router, array $moduleData): void
    {
        $className = $moduleData['className'];
        $moduleName = $moduleData['name'];
        $routeFile = $moduleData['file'];
        
        // Simple mode - minimal error handling
        if (!$this->enableErrorHandling) {
            if (class_exists($className)) {
                try {
                    (new $className())->Routes($router);
                } catch (\Exception $e) {
                    error_log("Route loading failed for module: {$moduleName} - " . $e->getMessage());
                }
            }
            return;
        }
        
        // Full error handling mode
        try {
            // Validate class exists
            if (!class_exists($className)) {
                $this->logError("Route class not found: {$className} for module: {$moduleName}");
                return;
            }

            // Determine method name in a case-insensitive, backward-compatible way
            // Many modules define `routes($router)`, while docs may reference `Routes($router)`.
            // PHP method names are case-insensitive, but we select explicitly for clarity.
            $methodName = null;
            if (method_exists($className, 'Routes')) {
                $methodName = 'Routes';
            } elseif (method_exists($className, 'routes')) {
                $methodName = 'routes';
            } else {
                $this->logError("Routes method not found in class: {$className} for module: {$moduleName} (expected 'Routes' or 'routes')");
                return;
            }
            
            // Instantiate route class
            $routeInstance = new $className();
            
            // Validate method signature
            $reflection = new \ReflectionMethod($className, $methodName);
            if ($reflection->getNumberOfRequiredParameters() > 1) {
                $this->logError("{$methodName} method in {$className} has incorrect signature (too many required parameters)");
                return;
            }
            
            // Execute routes method
            $routeInstance->$methodName($router);
            
            // Log success if verbose logging enabled
            if ($this->enableVerboseLogging) {
                $this->logInfo("Successfully registered routes for module: {$moduleName}");
            }
            
        } catch (\TypeError $e) {
            $this->logError("Type error in module {$moduleName}: " . $e->getMessage());
        } catch (\ParseError $e) {
            $this->logError("Parse error in route file {$routeFile}: " . $e->getMessage());
        } catch (\Error $e) {
            $this->logError("Fatal error in module {$moduleName}: " . $e->getMessage());
        } catch (\Exception $e) {
            $this->logError("Exception in module {$moduleName}: " . $e->getMessage());
        }
    }

    /**
     * Register custom routes from configuration file
     * 
     * Loads routes from custom-routes.php if file exists.
     * 
     * @param Router $router The router instance
     * @return void
     */
    private function registerCustomRoutes(Router $router): void
    {
        $customRoutes = $this->loadCustomRoutes();
        
        foreach ($customRoutes as $route) {
            $router->addRoute($route['path'], $route['controller'], $route['method']);
        }
    }

    /**
     * Load custom routes from configuration file
     * 
     * @return array Array of custom routes or empty array
     */
    private function loadCustomRoutes(): array
    {
        $configFile = __DIR__ . '/custom-routes.php';
        return file_exists($configFile) ? include $configFile : [];
    }

    // ========================================
    // Logging
    // ========================================

    /**
     * Log error messages for route discovery issues
     * 
     * Logs to error_log and optionally outputs to screen if debug enabled.
     * 
     * @param string $message Error message
     * @return void
     */
    private function logError(string $message): void
    {
        $logMessage = "[InitModsImproved] ERROR: {$message}";
        error_log($logMessage);
        
        if ($this->enableDebugOutput) {
            echo "<div style='color: red; padding: 5px; border: 1px solid red; margin: 5px;'>{$logMessage}</div>";
        }
    }

    /**
     * Log info messages for route discovery
     * 
     * Logs to error_log and optionally outputs to screen if debug enabled.
     * 
     * @param string $message Info message
     * @return void
     */
    private function logInfo(string $message): void
    {
        $logMessage = "[InitModsImproved] INFO: {$message}";
        error_log($logMessage);
        
        if ($this->enableDebugOutput) {
            echo "<div style='color: green; padding: 3px; margin: 2px; font-size: 12px;'>{$logMessage}</div>";
        }
    }

    // ========================================
    // Configuration & Statistics
    // ========================================

    /**
     * Get current configuration statistics
     * 
     * Returns array with all configuration settings for monitoring/debugging.
     * 
     * @return array Configuration statistics
     */
    public function getStats(): array
    {
        return [
            'cache_enabled' => $this->useCache,
            'error_handling_enabled' => $this->enableErrorHandling,
            'verbose_logging_enabled' => $this->enableVerboseLogging,
            'screen_output_enabled' => $this->enableDebugOutput,
            'modules_path' => $this->modulesPath,
            'environment' => Environment::get('APP_ENV', 'production'),
            'cache_key' => 'discovered_modules'
        ];
    }

    /**
     * Enable or disable error handling at runtime
     * 
     * @param bool $enabled Enable error handling
     * @return self Fluent interface
     */
    public function setErrorHandling(bool $enabled): self
    {
        $this->enableErrorHandling = $enabled;
        return $this;
    }

    /**
     * Enable or disable verbose logging at runtime
     * 
     * @param bool $enabled Enable verbose logging
     * @return self Fluent interface
     */
    public function setVerboseLogging(bool $enabled): self
    {
        $this->enableVerboseLogging = $enabled;
        return $this;
    }

    /**
     * Enable or disable debug output at runtime
     * 
     * @param bool $enabled Enable debug output
     * @return self Fluent interface
     */
    public function setDebugOutput(bool $enabled): self
    {
        $this->enableDebugOutput = $enabled;
        return $this;
    }

    /**
     * Enable or disable submodule discovery at runtime
     * 
     * @param bool $enabled Enable submodule discovery
     * @return self Fluent interface
     */
    public function setSubmoduleDiscovery(bool $enabled): self
    {
        $this->enableSubmoduleDiscovery = $enabled;
        return $this;
    }
}





