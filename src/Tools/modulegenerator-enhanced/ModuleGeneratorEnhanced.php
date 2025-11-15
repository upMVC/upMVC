<?php
namespace App\Tools\ModuleGeneratorEnhanced;

use PDO;
use PDOException;
use Exception;
use App\Etc\Config\Environment;

/**
 * Enhanced Module Generator for upMVC v2.0
 * 
 * Generates complete module structures that integrate with InitModsImproved.php
 * for automatic route discovery, submodule support, and modern architecture.
 */
class ModuleGeneratorEnhanced
{
    private array $config;
    private string $modulePath;
    private string $namespace;
    private array $validTypes = ['basic', 'crud', 'api', 'auth', 'dashboard', 'submodule'];
    private bool $useEnhancedFeatures = true;

    public function __construct(array $config)
    {
        $this->config = $this->validateConfig($config);
        
        // PSR-4: All modules must use App\Modules\ namespace
        $moduleName = $this->config['name'];
        $this->namespace = 'App\\Modules\\' . $moduleName;
        
        // PSR-4 convention: src/Modules/ModuleName/
        $directoryName = $moduleName;
        
        // Handle submodule paths
        if ($this->config['type'] === 'submodule' && !empty($this->config['parent_module'])) {
            $this->modulePath = __DIR__ . '/../../Modules/' . $this->config['parent_module'] . '/Modules/' . $directoryName;
        } else {
            $this->modulePath = __DIR__ . '/../../Modules/' . $directoryName;
        }
        
        // Load environment for enhanced features
        if (class_exists('upMVC\\Config\\Environment')) {
            try {
                Environment::load();
            } catch (Exception $e) {
                // Environment load failed, continue without it
                echo "ℹ️  Environment load skipped: {$e->getMessage()}\n";
            }
        }
    }

    public function generate(): bool
    {
        try {
            echo "🚀 Generating enhanced module '{$this->namespace}'...\n";
            
            // Pre-generation checks
            $this->performPreChecks();
            
            // Create directory structure
            $this->createDirectoryStructure();
            
            // Generate core files with enhanced features
            $this->generateController();
            $this->generateModel();
            $this->generateView();
            $this->generateRoutes();
            
            // Generate additional files based on type
            $this->generateAdditionalFiles();
            
            // NO NEED to update framework files - InitModsImproved auto-discovers!
            // This is the key difference from legacy generator
            
            // Create database table if needed
            if ($this->config['type'] === 'crud' && ($this->config['create_table'] ?? false)) {
                $this->createDatabaseTable();
            }
            
            // Generate submodules if requested
            if ($this->config['create_submodules'] ?? false) {
                $this->generateSubmoduleStructure();
            }
            
            // NO NEED to update composer.json - PSR-4 "App\\": "src/" handles everything!
            // Just run: composer dump-autoload
            
            echo "✅ Enhanced module generation completed successfully!\n";
            echo "🔍 Module will be auto-discovered by InitModsImproved.php\n";
            echo "📦 Run 'composer dump-autoload' to update autoloader\n";
            
            return true;
            
        } catch (Exception $e) {
            echo "❌ Error generating module: " . $e->getMessage() . "\n";
            return false;
        }
    }

    private function performPreChecks(): void
    {
        // Check if InitModsImproved exists
        $initModsImproved = __DIR__ . '/../../etc/InitModsImproved.php';
        if (!file_exists($initModsImproved)) {
            throw new Exception("InitModsImproved.php not found. This generator requires the enhanced upMVC system.");
        }

        // Check environment configuration (if available)
        if ($this->useEnhancedFeatures && class_exists('upMVC\\Config\\Environment')) {
            try {
                $submoduleDiscovery = Environment::get('ROUTE_SUBMODULE_DISCOVERY', 'true');
                if ($this->config['type'] === 'submodule' && !filter_var($submoduleDiscovery, FILTER_VALIDATE_BOOLEAN)) {
                    echo "⚠️  Warning: ROUTE_SUBMODULE_DISCOVERY is disabled. Submodules may not be discovered.\n";
                }
            } catch (Exception $e) {
                // Environment check failed, continue without it
                echo "ℹ️  Environment check skipped: {$e->getMessage()}\n";
            }
        }

        // Check if module already exists
        if (is_dir($this->modulePath)) {
            throw new Exception("Module directory already exists: {$this->modulePath}");
        }
    }

    /**
     * Get the module name from namespace (e.g., App\Modules\Products → Products)
     */
    private function getModuleName(): string
    {
        return $this->config['name'];
    }

    /**
     * Get the web-accessible path for assets (e.g., src/Modules/Products)
     */
    private function getModuleWebPath(): string
    {
        return 'src/Modules/' . $this->getModuleName();
    }

    private function validateConfig(array $config): array
    {
        $required = ['name', 'type'];
        foreach ($required as $key) {
            if (empty($config[$key])) {
                throw new Exception("Configuration key '{$key}' is required.");
            }
        }

        if (!in_array($config['type'], $this->validTypes)) {
            throw new Exception("Invalid module type: {$config['type']}. Valid types: " . implode(', ', $this->validTypes));
        }

        // PSR-4: Module name becomes the folder and class name (PascalCase)
        // Namespace is always: App\Modules\{ModuleName}
        $config['name'] = ucfirst($config['name']); // Ensure PascalCase
        $config['table_name'] = $config['table_name'] ?? strtolower($config['name']) . 's';
        $config['route_name'] = $config['route_name'] ?? strtolower($config['name']);
        $config['fields'] = $config['fields'] ?? [];
        $config['use_middleware'] = $config['use_middleware'] ?? true;
        $config['enable_caching'] = $config['enable_caching'] ?? true;
        $config['create_submodules'] = $config['create_submodules'] ?? false;

        return $config;
    }

    private function createDirectoryStructure(): void
    {
        $directories = [
            $this->modulePath,
            $this->modulePath . '/Routes',  // PSR-4: capital R for auto-discovery
            $this->modulePath . '/views',
            $this->modulePath . '/views/layouts',
            $this->modulePath . '/etc',
            $this->modulePath . '/assets',
            $this->modulePath . '/assets/css',
            $this->modulePath . '/assets/js',
        ];

        // Add submodule directories if enabled
        if ($this->config['create_submodules'] ?? false) {
            $directories[] = $this->modulePath . '/Modules';  // PSR-4: capital M for submodules
        }

        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
                echo "📁 Created directory: " . str_replace(__DIR__ . '/../../', '', $dir) . "\n";
            }
        }
    }

    private function generateController(): void
    {
        $template = match($this->config['type']) {
            'crud' => $this->getEnhancedCrudControllerTemplate(),
            'api' => $this->getEnhancedApiControllerTemplate(),
            'auth' => $this->getEnhancedAuthControllerTemplate(),
            'dashboard' => $this->getEnhancedDashboardControllerTemplate(),
            'submodule' => $this->getEnhancedSubmoduleControllerTemplate(),
            default => $this->getEnhancedBasicControllerTemplate()
        };

        $this->writeFile('/Controller.php', $template);
    }

    private function generateModel(): void
    {
        $template = match($this->config['type']) {
            'crud' => $this->getEnhancedCrudModelTemplate(),
            'auth' => $this->getEnhancedAuthModelTemplate(),
            default => $this->getEnhancedBasicModelTemplate()
        };

        $this->writeFile('/Model.php', $template);
    }

    private function generateView(): void
    {
        $template = match($this->config['type']) {
            'crud' => $this->getEnhancedCrudViewTemplate(),
            'dashboard' => $this->getEnhancedDashboardViewTemplate(),
            default => $this->getEnhancedBasicViewTemplate()
        };

        $this->writeFile('/View.php', $template);
        
        // Generate view templates
        $this->generateViewTemplates();
    }

    private function generateRoutes(): void
    {
        // This is the KEY difference - routes are auto-discovered by InitModsImproved!
        $template = $this->getEnhancedRoutesTemplate();
        $this->writeFile('/Routes/Routes.php', $template);
        
        echo "🔄 Routes will be auto-discovered by InitModsImproved.php (no manual registration needed)\n";
    }

    private function generateSubmoduleStructure(): void
    {
        if (!($this->config['create_submodules'] ?? false)) {
            return;
        }

        echo "📦 Creating submodule structure...\n";
        
        // Create example submodule (PSR-4: capital M)
        $exampleSubmodule = $this->modulePath . '/Modules/Example';
        mkdir($exampleSubmodule, 0755, true);
        mkdir($exampleSubmodule . '/Routes', 0755, true);
        
        // Create example submodule routes
        $submoduleRoutes = <<<PHP
<?php
namespace {$this->namespace}\\Modules\\Example\\Routes;

use {$this->namespace}\\Modules\\Example\\Controller;

/**
 * Example Submodule Routes
 * 
 * This submodule will be auto-discovered by InitModsImproved.php
 * Location: src/Modules/{$this->getModuleName()}/Modules/Example/Routes/Routes.php
 */
class Routes
{
    /**
     * Register submodule routes with the router
     */
    public function Routes(\$router): void
    {
        \$router->addRoute('/{$this->config['route_name']}/example', Controller::class, 'display');
        \$router->addRoute('/{$this->config['route_name']}/example/test', Controller::class, 'test');
    }
}
PHP;
        
        file_put_contents($exampleSubmodule . '/Routes/Routes.php', $submoduleRoutes);        // Create example submodule controller
        $submoduleController = <<<PHP
<?php
namespace {$this->namespace}\\Modules\\Example;

use App\Common\\Bmvc\\BaseController;

/**
 * Example Submodule Controller
 * 
 * Demonstrates nested module functionality with auto-discovery
 */
class Controller extends BaseController
{
    public function display(\$reqRoute, \$reqMet): void
    {
        echo "<h1>Example Submodule</h1>";
        echo "<p>This is a submodule within {$this->namespace} module.</p>";
        echo "<p>Route: /{$this->config['route_name']}/example</p>";
        echo "<p>Auto-discovered by InitModsImproved.php!</p>";
    }

    public function test(\$reqRoute, \$reqMet): void
    {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Submodule test endpoint working!',
            'parent_module' => '{$this->namespace}',
            'submodule' => 'Example',
            'route' => \$reqRoute
        ]);
    }
}
PHP;

        file_put_contents($exampleSubmodule . '/Controller.php', $submoduleController);
        
        echo "✅ Created example submodule with auto-discovery support\n";
    }

    // Enhanced template methods with modern features
    private function getEnhancedBasicControllerTemplate(): string
    {
        $middlewareCode = $this->config['use_middleware'] ? $this->getMiddlewareIntegrationCode() : '';
        
        return <<<PHP
<?php
namespace {$this->namespace};

use App\Common\\Bmvc\\BaseController;

/**
 * Enhanced {$this->namespace} Controller
 * 
 * Auto-discovered by InitModsImproved.php
 * Generated with enhanced upMVC features
 */
class Controller extends BaseController
{
    private \$model;
    private \$view;

    public function __construct()
    {
        parent::__construct();
        \$this->model = new Model();
        \$this->view = new View();
        
        {$middlewareCode}
    }

    /**
     * Main display method - auto-routed
     */
    public function display(\$reqRoute, \$reqMet): void
    {
        \$data = [
            'title' => '{$this->namespace} Module',
            'message' => 'Welcome to the enhanced {$this->namespace} module!',
            'features' => [
                'Auto-discovery by InitModsImproved.php',
                'Environment-aware configuration',
                'Caching support',
                'Middleware integration ready',
                'Modern PHP 8.1+ features'
            ],
            'route_info' => [
                'current_route' => \$reqRoute,
                'method' => \$reqMet,
                'module' => '{$this->namespace}'
            ]
        ];
        
        \$this->view->render('index', \$data);
    }

    /**
     * About page with module information
     */
    public function about(\$reqRoute, \$reqMet): void
    {
        \$data = [
            'title' => 'About {$this->namespace}',
            'content' => 'This module was generated with the enhanced upMVC module generator.',
            'tech_info' => [
                'auto_discovery' => 'Enabled via InitModsImproved.php',
                'environment' => \\upMVC\\Config\\Environment::current(),
                'caching' => \$this->isCachingEnabled() ? 'Enabled' : 'Disabled',
                'generated_at' => date('Y-m-d H:i:s')
            ]
        ];
        
        \$this->view->render('about', \$data);
    }

    /**
     * API endpoint for module information
     */
    public function api(\$reqRoute, \$reqMet): void
    {
        header('Content-Type: application/json');
        
        \$response = [
            'success' => true,
            'module' => '{$this->namespace}',
            'version' => '2.0-enhanced',
            'features' => [
                'auto_discovery' => true,
                'caching' => \$this->isCachingEnabled(),
                'middleware_ready' => true,
                'submodule_support' => true
            ],
            'request' => [
                'route' => \$reqRoute,
                'method' => \$reqMet,
                'timestamp' => date('c')
            ]
        ];
        
        echo json_encode(\$response, JSON_PRETTY_PRINT);
    }

    /**
     * Check if caching is enabled for this module
     */
    private function isCachingEnabled(): bool
    {
        return \\upMVC\\Config\\Environment::get('ROUTE_USE_CACHE', 'true') === 'true';
    }
}
PHP;
    }

    private function getEnhancedRoutesTemplate(): string
    {
        $routes = [];
        $middlewareRoutes = [];
        
        switch ($this->config['type']) {
            case 'crud':
                $routes[] = "        // Main CRUD routes";
                $routes[] = "        \$router->addRoute('/{$this->config['route_name']}', Controller::class, 'display');";
                $routes[] = "        \$router->addRoute('/{$this->config['route_name']}/api', Controller::class, 'api');";
                $routes[] = "        \$router->addRoute('/{$this->config['route_name']}/search', Controller::class, 'search');";
                break;
            case 'api':
                $routes[] = "        // RESTful API routes";
                $routes[] = "        \$router->addRoute('/api/{$this->config['route_name']}', Controller::class, 'api');";
                $routes[] = "        \$router->addRoute('/api/{$this->config['route_name']}/v1', Controller::class, 'apiV1');";
                break;
            case 'auth':
                $routes[] = "        // Authentication routes (middleware-ready)";
                $routes[] = "        \$router->addRoute('/{$this->config['route_name']}/profile', Controller::class, 'profile');";
                $routes[] = "        \$router->addRoute('/{$this->config['route_name']}/settings', Controller::class, 'settings');";
                break;
            case 'submodule':
                $routes[] = "        // Submodule routes (auto-discovered by parent)";
                $routes[] = "        \$router->addRoute('/{$this->config['route_name']}', Controller::class, 'display');";
                break;
            default:
                $routes[] = "        // Basic module routes";
                $routes[] = "        \$router->addRoute('/{$this->config['route_name']}', Controller::class, 'display');";
                $routes[] = "        \$router->addRoute('/{$this->config['route_name']}/about', Controller::class, 'about');";
                $routes[] = "        \$router->addRoute('/{$this->config['route_name']}/api', Controller::class, 'api');";
        }

        // Convert array to string for template
        $routesString = implode("\n", $routes);

        return <<<PHP
<?php
namespace {$this->namespace}\\Routes;

use {$this->namespace}\\Controller;

/**
 * Enhanced {$this->namespace} Routes
 * 
 * Auto-discovered by InitModsImproved.php
 * No manual registration required!
 * 
 * Features:
 * - Automatic route discovery
 * - Submodule support
 * - Caching integration
 * - Environment awareness
 */
class Routes
{
    /**
     * Register routes with the router
     * 
     * This method is automatically called by InitModsImproved.php
     * when the module is discovered.
     */
    public function Routes(\$router): void
    {
{$routesString}
        
        // Enhanced: Environment-specific routes
        if (\\upMVC\\Config\\Environment::isDevelopment()) {
            \$router->addRoute('/{$this->config['route_name']}/debug', Controller::class, 'debug');
        }
    }
}
PHP;
    }

    private function getMiddlewareIntegrationCode(): string
    {
        return <<<PHP
        // Enhanced: Middleware integration ready
        // Uncomment and configure as needed:
        // \$this->addMiddleware('auth'); // Requires authentication
        // \$this->addMiddleware('admin'); // Requires admin role
        // \$this->addMiddleware('cors'); // Enable CORS
PHP;
    }

    // Additional enhanced templates
    private function getEnhancedCrudControllerTemplate(): string
    {
        // Implementation for enhanced CRUD controller with auto-discovery
        return $this->getEnhancedBasicControllerTemplate() . "\n// Enhanced CRUD methods would be added here";
    }

    private function getEnhancedApiControllerTemplate(): string
    {
        // Implementation for enhanced API controller
        return $this->getEnhancedBasicControllerTemplate() . "\n// Enhanced API methods would be added here";
    }

    private function getEnhancedAuthControllerTemplate(): string
    {
        // Implementation for enhanced Auth controller with middleware
        return $this->getEnhancedBasicControllerTemplate() . "\n// Enhanced Auth methods would be added here";
    }

    private function getEnhancedDashboardControllerTemplate(): string
    {
        // Implementation for enhanced Dashboard controller
        return $this->getEnhancedBasicControllerTemplate() . "\n// Enhanced Dashboard methods would be added here";
    }

    private function getEnhancedSubmoduleControllerTemplate(): string
    {
        return <<<PHP
<?php
namespace {$this->namespace};

use App\Common\\Bmvc\\BaseController;

/**
 * Enhanced {$this->namespace} Submodule Controller
 * 
 * Auto-discovered by InitModsImproved.php as a submodule
 * Parent module: {$this->config['parent_module']}
 */
class Controller extends BaseController
{
    public function display(\$reqRoute, \$reqMet): void
    {
        \$data = [
            'title' => '{$this->namespace} Submodule',
            'parent_module' => '{$this->config['parent_module']}',
            'message' => 'This is a submodule auto-discovered by InitModsImproved.php!'
        ];
        
        echo "<h1>{\$data['title']}</h1>";
        echo "<p>{\$data['message']}</p>";
        echo "<p>Parent: {\$data['parent_module']}</p>";
        echo "<p>Route: {\$reqRoute}</p>";
    }
}
PHP;
    }

    private function getEnhancedBasicModelTemplate(): string
    {
        return <<<PHP
<?php
namespace {$this->namespace};

use App\Common\\Bmvc\\BaseModel;

/**
 * Enhanced {$this->namespace} Model
 * 
 * Features caching and enhanced error handling
 */
class Model extends BaseModel
{
    protected \$table = '{$this->config['table_name']}';
    protected \$enableCaching = true;

    public function __construct()
    {
        parent::__construct();
        
        // Enhanced: Respect environment caching settings
        \$this->enableCaching = \\upMVC\\Config\\Environment::get('ROUTE_USE_CACHE', 'true') === 'true';
    }

    /**
     * Enhanced data retrieval with caching
     */
    public function getEnhancedData(\$id = null): array
    {
        \$cacheKey = "{\$this->table}_data_" . (\$id ?: 'all');
        
        if (\$this->enableCaching && \$cached = \$this->getFromCache(\$cacheKey)) {
            return \$cached;
        }
        
        \$data = \$id ? \$this->read(\$id, \$this->table) : \$this->readAll(\$this->table);
        
        if (\$this->enableCaching) {
            \$this->putInCache(\$cacheKey, \$data, 3600);
        }
        
        return \$data;
    }

    private function getFromCache(string \$key): mixed
    {
        // Implementation depends on your cache system
        return null;
    }

    private function putInCache(string \$key, mixed \$data, int \$ttl): void
    {
        // Implementation depends on your cache system
    }
}
PHP;
    }

    private function getEnhancedCrudModelTemplate(): string
    {
        // Enhanced CRUD model implementation
        return $this->getEnhancedBasicModelTemplate() . "\n// Enhanced CRUD methods would be added here";
    }

    private function getEnhancedAuthModelTemplate(): string
    {
        // Enhanced Auth model implementation
        return $this->getEnhancedBasicModelTemplate() . "\n// Enhanced Auth methods would be added here";
    }

    private function getEnhancedBasicViewTemplate(): string
    {
        return <<<PHP
<?php
namespace {$this->namespace};

use App\Common\\Bmvc\\BaseView;

/**
 * Enhanced {$this->namespace} View
 * 
 * Supports modern templating and environment awareness
 */
class View extends BaseView
{
    private \$layoutPath;
    
    public function __construct()
    {
        \$this->layoutPath = __DIR__ . '/views/layouts/';
    }

    /**
     * Enhanced render method with layout support
     */
    public function render(string \$template, array \$data = []): void
    {
        // Enhanced: Add environment data
        \$data['app_env'] = \\upMVC\\Config\\Environment::current();
        \$data['debug_mode'] = \\upMVC\\Config\\Environment::isDevelopment();
        \$data['module_name'] = '{$this->namespace}';
        
        // Extract data for template
        extract(\$data);
        
        // Include header
        include \$this->layoutPath . 'header.php';
        
        // Include main template
        \$templateFile = __DIR__ . "/views/{\$template}.php";
        if (file_exists(\$templateFile)) {
            include \$templateFile;
        } else {
            \$this->renderError("Template {\$template} not found", \$data);
        }
        
        // Include footer
        include \$this->layoutPath . 'footer.php';
    }

    /**
     * Enhanced error rendering
     */
    private function renderError(string \$message, array \$data): void
    {
        echo "<div class='alert alert-danger'>";
        echo "<h4>Template Error</h4>";
        echo "<p>" . htmlspecialchars(\$message) . "</p>";
        
        if (\$data['debug_mode'] ?? false) {
            echo "<details><summary>Debug Info</summary>";
            echo "<pre>" . print_r(\$data, true) . "</pre>";
            echo "</details>";
        }
        
        echo "</div>";
    }
}
PHP;
    }

    private function getEnhancedCrudViewTemplate(): string
    {
        // Enhanced CRUD view implementation
        return $this->getEnhancedBasicViewTemplate() . "\n// Enhanced CRUD view methods would be added here";
    }

    private function getEnhancedDashboardViewTemplate(): string
    {
        // Enhanced Dashboard view implementation
        return $this->getEnhancedBasicViewTemplate() . "\n// Enhanced Dashboard view methods would be added here";
    }

    // Enhanced template generation methods
    private function generateViewTemplates(): void
    {
        // Generate enhanced layout files
        $this->writeFile('/views/layouts/header.php', $this->getEnhancedHeaderTemplate());
        $this->writeFile('/views/layouts/footer.php', $this->getEnhancedFooterTemplate());
        
        // Generate enhanced view files based on type
        switch ($this->config['type']) {
            case 'crud':
                $this->writeFile('/views/index.php', $this->getEnhancedBasicIndexViewTemplate());
                break;
            case 'dashboard':
                $this->writeFile('/views/dashboard.php', $this->getEnhancedBasicIndexViewTemplate());
                break;
            default:
                $this->writeFile('/views/index.php', $this->getEnhancedBasicIndexViewTemplate());
                $this->writeFile('/views/about.php', $this->getEnhancedAboutViewTemplate());
        }
    }

    private function getEnhancedHeaderTemplate(): string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo \$title ?? '{$this->namespace}'; ?> - Enhanced upMVC</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Enhanced Module CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL ?? ''; ?>/{$this->getModuleWebPath()}/assets/css/style.css">
    
    <?php if (\$debug_mode ?? false): ?>
    <!-- Debug Mode Indicator -->
    <style>
        .debug-indicator {
            position: fixed; top: 0; right: 0; z-index: 9999;
            background: #ff6b6b; color: white; padding: 5px 10px;
            font-size: 12px; font-weight: bold;
        }
    </style>
    <?php endif; ?>
</head>
<body>
    <?php if (\$debug_mode ?? false): ?>
    <div class="debug-indicator">
        <i class="fas fa-bug"></i> DEBUG MODE - <?php echo \$app_env; ?>
    </div>
    <?php endif; ?>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL ?? ''; ?>">
                <i class="fas fa-rocket"></i> Enhanced upMVC
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="<?php echo BASE_URL ?? ''; ?>/{$this->config['route_name']}">
                    <i class="fas fa-home"></i> {$this->namespace}
                </a>
                <a class="nav-link" href="<?php echo BASE_URL ?? ''; ?>/{$this->config['route_name']}/about">
                    <i class="fas fa-info-circle"></i> About
                </a>
                <?php if (\$debug_mode ?? false): ?>
                <a class="nav-link" href="<?php echo BASE_URL ?? ''; ?>/{$this->config['route_name']}/api">
                    <i class="fas fa-code"></i> API
                </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (method_exists(\$this, 'renderFlashMessages')) \$this->renderFlashMessages(); ?>
HTML;
    }

    private function getEnhancedFooterTemplate(): string
    {
        return <<<HTML
    </div>

    <footer class="bg-light text-center text-lg-start mt-5">
        <div class="text-center p-3">
            <small class="text-muted">
                © <?php echo date('Y'); ?> {$this->namespace} Module 
                - Enhanced upMVC v2.0
                <?php if (\$debug_mode ?? false): ?>
                - <span class="badge bg-warning text-dark">Debug Mode</span>
                <?php endif; ?>
            </small>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Enhanced Module JS -->
    <script src="<?php echo BASE_URL ?? ''; ?>/{$this->getModuleWebPath()}/assets/js/script.js"></script>
    
    <?php if (\$debug_mode ?? false): ?>
    <script>
        console.log('Enhanced {$this->namespace} Module Debug Mode Active');
        console.log('Environment:', '<?php echo \$app_env; ?>');
        console.log('Auto-discovery: Enabled via InitModsImproved.php');
    </script>
    <?php endif; ?>
</body>
</html>
HTML;
    }

    private function getEnhancedBasicIndexViewTemplate(): string
    {
        return <<<HTML
<div class="row">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">
                    <i class="fas fa-rocket"></i> <?php echo \$title; ?>
                </h3>
            </div>
            <div class="card-body">
                <div class="alert alert-success border-0">
                    <h5><i class="fas fa-check-circle"></i> Enhanced Module Active!</h5>
                    <p class="mb-0"><?php echo \$message ?? 'Enhanced module loaded successfully!'; ?></p>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card bg-light border-0">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-magic text-primary"></i> Enhanced Features
                                </h5>
                                <ul class="list-unstyled">
                                    <?php foreach (\$features ?? [] as \$feature): ?>
                                    <li><i class="fas fa-check text-success"></i> <?php echo \$feature; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light border-0">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-info text-info"></i> Route Information
                                </h5>
                                <table class="table table-sm">
                                    <?php foreach (\$route_info ?? [] as \$key => \$value): ?>
                                    <tr>
                                        <td><strong><?php echo ucfirst(str_replace('_', ' ', \$key)); ?></strong></td>
                                        <td><code><?php echo htmlspecialchars(\$value); ?></code></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (\$debug_mode ?? false): ?>
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card border-warning">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0"><i class="fas fa-bug"></i> Debug Information</h6>
                            </div>
                            <div class="card-body">
                                <p><strong>Environment:</strong> <span class="badge bg-secondary"><?php echo \$app_env; ?></span></p>
                                <p><strong>Auto-Discovery:</strong> <span class="badge bg-success">Enabled via InitModsImproved.php</span></p>
                                <p><strong>Module Path:</strong> <code>{$this->getModuleWebPath()}/</code></p>
                                <a href="<?php echo BASE_URL ?? ''; ?>/{$this->config['route_name']}/api" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-code"></i> Test API Endpoint
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
HTML;
    }

    private function getEnhancedAboutViewTemplate(): string
    {
        return <<<HTML
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-info text-white">
                <h3 class="mb-0">
                    <i class="fas fa-info-circle"></i> <?php echo \$title; ?>
                </h3>
            </div>
            <div class="card-body">
                <p class="lead"><?php echo \$content ?? 'About this enhanced module'; ?></p>
                
                <h5 class="mt-4"><i class="fas fa-cog"></i> Technical Information</h5>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <?php foreach (\$tech_info ?? [] as \$key => \$value): ?>
                        <tr>
                            <td><strong><?php echo ucfirst(str_replace('_', ' ', \$key)); ?></strong></td>
                            <td><?php echo is_bool(\$value) ? (\$value ? 'Yes' : 'No') : htmlspecialchars(\$value); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                
                <div class="mt-4">
                    <a href="<?php echo BASE_URL ?? ''; ?>/{$this->config['route_name']}" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Back to Module
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
HTML;
    }

    private function generateAdditionalFiles(): void
    {
        // Generate enhanced CSS
        $this->writeFile('/assets/css/style.css', $this->getEnhancedCssTemplate());
        
        // Generate enhanced JavaScript
        $this->writeFile('/assets/js/script.js', $this->getEnhancedJsTemplate());
        
        // Generate module configuration
        $this->writeFile('/etc/config.php', $this->getModuleConfigTemplate());
        
        // Generate API documentation
        if (in_array($this->config['type'], ['api', 'crud']) || ($this->config['include_api'] ?? false)) {
            $this->writeFile('/etc/api-docs.md', $this->getEnhancedApiDocsTemplate());
        }
    }

    private function getEnhancedCssTemplate(): string
    {
        return <<<CSS
/* Enhanced {$this->namespace} Module Styles */

:root {
    --{$this->namespace}-primary: #007bff;
    --{$this->namespace}-secondary: #6c757d;
    --{$this->namespace}-success: #28a745;
    --{$this->namespace}-info: #17a2b8;
    --{$this->namespace}-warning: #ffc107;
    --{$this->namespace}-danger: #dc3545;
}

.{$this->namespace}-container {
    padding: 20px 0;
}

.{$this->namespace}-card {
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    border: none;
    border-radius: 12px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.{$this->namespace}-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 15px rgba(0,0,0,0.15);
}

/* Enhanced debugging styles */
.debug-mode {
    border: 3px dashed var(--{$this->namespace}-warning) !important;
}

.debug-info {
    background: linear-gradient(45deg, #fff3cd, #fefefe);
    border-left: 4px solid var(--{$this->namespace}-warning);
    padding: 15px;
    margin: 15px 0;
}

/* Enhanced responsive design */
@media (max-width: 768px) {
    .{$this->namespace}-container {
        padding: 10px 0;
    }
    
    .{$this->namespace}-card {
        margin-bottom: 15px;
    }
}

/* Enhanced animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.{$this->namespace}-fade-in {
    animation: fadeInUp 0.6s ease;
}

/* Enhanced loading states */
.{$this->namespace}-loading {
    position: relative;
    opacity: 0.6;
    pointer-events: none;
}

.{$this->namespace}-loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    margin: -10px 0 0 -10px;
    border: 2px solid var(--{$this->namespace}-primary);
    border-top: 2px solid transparent;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Enhanced error states */
.{$this->namespace}-error {
    border-color: var(--{$this->namespace}-danger) !important;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

/* Enhanced success states */
.{$this->namespace}-success {
    border-color: var(--{$this->namespace}-success) !important;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}
CSS;
    }

    private function getEnhancedJsTemplate(): string
    {
        return <<<JS
/**
 * Enhanced {$this->namespace} Module JavaScript
 * Auto-discovery enabled, environment-aware
 */

class Enhanced{$this->namespace}Module {
    constructor() {
        this.moduleName = '{$this->namespace}';
        this.version = '2.0-enhanced';
        this.debugMode = this.isDebugMode();
        this.apiEndpoint = this.getApiEndpoint();
        
        this.log('Enhanced module initialized');
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.initializeComponents();
        this.checkAutoDiscovery();
        
        // Enhanced: Add fade-in animation
        document.querySelectorAll('.card').forEach(card => {
            card.classList.add(`\${this.moduleName}-fade-in`);
        });
    }
    
    bindEvents() {
        // Enhanced event binding with error handling
        try {
            this.bindFormEvents();
            this.bindNavigationEvents();
            this.bindApiEvents();
        } catch (error) {
            this.logError('Event binding failed:', error);
        }
    }
    
    bindFormEvents() {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', (e) => this.handleFormSubmit(e));
        });
    }
    
    bindNavigationEvents() {
        // Enhanced navigation with loading states
        const navLinks = document.querySelectorAll('a[href*="{\${this.moduleName.toLowerCase()}}"]');
        navLinks.forEach(link => {
            link.addEventListener('click', (e) => this.handleNavigation(e));
        });
    }
    
    bindApiEvents() {
        const apiButtons = document.querySelectorAll('[data-api-action]');
        apiButtons.forEach(button => {
            button.addEventListener('click', (e) => this.handleApiAction(e));
        });
    }
    
    handleFormSubmit(event) {
        const form = event.target;
        const submitButton = form.querySelector('button[type="submit"]');
        
        if (submitButton) {
            this.setLoading(submitButton, true);
            
            // Auto-restore loading state after 3 seconds (fallback)
            setTimeout(() => {
                this.setLoading(submitButton, false);
            }, 3000);
        }
        
        this.log('Form submitted:', form.action);
        return true;
    }
    
    handleNavigation(event) {
        const link = event.target.closest('a');
        this.log('Navigation:', link.href);
        
        // Add loading indicator for navigation
        this.showLoadingIndicator();
    }
    
    handleApiAction(event) {
        event.preventDefault();
        const button = event.target.closest('[data-api-action]');
        const action = button.dataset.apiAction;
        
        this.log('API Action:', action);
        this.callApi(action, button);
    }
    
    async callApi(action, button = null) {
        if (button) this.setLoading(button, true);
        
        try {
            const response = await fetch(this.apiEndpoint);
            const data = await response.json();
            
            this.log('API Response:', data);
            this.showApiResponse(data);
            
        } catch (error) {
            this.logError('API Error:', error);
            this.showError('API request failed');
        } finally {
            if (button) this.setLoading(button, false);
        }
    }
    
    showApiResponse(data) {
        const alert = this.createAlert('API Response', JSON.stringify(data, null, 2), 'info');
        this.showAlert(alert);
    }
    
    initializeComponents() {
        this.initializeTooltips();
        this.initializeModals();
        this.checkEnhancedFeatures();
    }
    
    initializeTooltips() {
        if (typeof bootstrap !== 'undefined') {
            const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            tooltips.forEach(tooltip => {
                new bootstrap.Tooltip(tooltip);
            });
        }
    }
    
    initializeModals() {
        if (typeof bootstrap !== 'undefined') {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                new bootstrap.Modal(modal);
            });
        }
    }
    
    checkAutoDiscovery() {
        this.log('Auto-discovery check: Module loaded via InitModsImproved.php');
        
        if (this.debugMode) {
            this.showDebugInfo();
        }
    }
    
    checkEnhancedFeatures() {
        const features = {
            autoDiscovery: true,
            environmentAware: this.getEnvironment() !== 'unknown',
            cachingEnabled: this.isCachingEnabled(),
            debugMode: this.debugMode
        };
        
        this.log('Enhanced features:', features);
        return features;
    }
    
    showDebugInfo() {
        const debugPanel = document.createElement('div');
        debugPanel.className = 'debug-info mt-3';
        debugPanel.innerHTML = \`
            <h6><i class="fas fa-bug"></i> Debug Information</h6>
            <p><strong>Module:</strong> \${this.moduleName} v\${this.version}</p>
            <p><strong>Environment:</strong> \${this.getEnvironment()}</p>
            <p><strong>Auto-Discovery:</strong> ✅ Enabled</p>
            <p><strong>Caching:</strong> \${this.isCachingEnabled() ? '✅ Enabled' : '❌ Disabled'}</p>
            <button class="btn btn-sm btn-outline-primary" onclick="window.enhanced\${this.moduleName}.testApi()">
                <i class="fas fa-code"></i> Test API
            </button>
        \`;
        
        const container = document.querySelector('.container');
        if (container) {
            container.appendChild(debugPanel);
        }
    }
    
    testApi() {
        this.log('Testing API endpoint...');
        this.callApi('test');
    }
    
    // Utility methods
    setLoading(element, isLoading) {
        if (isLoading) {
            element.classList.add(\`\${this.moduleName}-loading\`);
            element.disabled = true;
            element.dataset.originalText = element.innerHTML;
            element.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
        } else {
            element.classList.remove(\`\${this.moduleName}-loading\`);
            element.disabled = false;
            element.innerHTML = element.dataset.originalText || element.innerHTML;
        }
    }
    
    showLoadingIndicator() {
        const indicator = document.createElement('div');
        indicator.className = 'position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center';
        indicator.style.backgroundColor = 'rgba(0,0,0,0.5)';
        indicator.style.zIndex = '9999';
        indicator.innerHTML = '<div class="spinner-border text-light" role="status"></div>';
        
        document.body.appendChild(indicator);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            indicator.remove();
        }, 5000);
    }
    
    createAlert(title, message, type = 'info') {
        return \`
            <div class="alert alert-\${type} alert-dismissible fade show" role="alert">
                <h6 class="alert-heading">\${title}</h6>
                <pre class="mb-0" style="white-space: pre-wrap;">\${message}</pre>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        \`;
    }
    
    showAlert(html) {
        const container = document.querySelector('.container');
        if (container) {
            const alertDiv = document.createElement('div');
            alertDiv.innerHTML = html;
            container.insertBefore(alertDiv.firstElementChild, container.firstChild);
        }
    }
    
    showError(message) {
        const alert = this.createAlert('Error', message, 'danger');
        this.showAlert(alert);
    }
    
    // Environment detection
    isDebugMode() {
        return document.querySelector('.debug-indicator') !== null;
    }
    
    getEnvironment() {
        const debugIndicator = document.querySelector('.debug-indicator');
        if (debugIndicator) {
            return debugIndicator.textContent.includes('development') ? 'development' : 'production';
        }
        return 'unknown';
    }
    
    isCachingEnabled() {
        // This would need to be set by the PHP template
        return true; // Default assumption
    }
    
    getApiEndpoint() {
        const baseUrl = window.BASE_URL || '';
        return \`\${baseUrl}/{\${this.moduleName.toLowerCase()}}/api\`;
    }
    
    // Logging methods
    log(...args) {
        if (this.debugMode) {
            console.log(\`[\${this.moduleName}]\`, ...args);
        }
    }
    
    logError(...args) {
        console.error(\`[\${this.moduleName}] ERROR:\`, ...args);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.enhanced{$this->namespace} = new Enhanced{$this->namespace}Module();
});

// Export for external use
window.Enhanced{$this->namespace}Module = Enhanced{$this->namespace}Module;
JS;
    }

    private function getModuleConfigTemplate(): string
    {
        $createSubmodules = $this->config['create_submodules'] ? 'true' : 'false';
        $apiEnabled = in_array($this->config['type'], ['api', 'crud']) ? 'true' : 'false';
        $createTable = ($this->config['create_table'] ?? false) ? 'true' : 'false';
        $generatedAt = date('Y-m-d H:i:s');
        
        return <<<PHP
<?php
/**
 * {$this->namespace} Module Configuration
 * Generated by Enhanced Module Generator
 */

return [
    'module' => [
        'name' => '{$this->namespace}',
        'version' => '2.0-enhanced',
        'type' => '{$this->config['type']}',
        'generated_at' => '{$generatedAt}',
        'generator_version' => '2.0'
    ],
    
    'features' => [
        'auto_discovery' => true,
        'caching' => true,
        'middleware_ready' => true,
        'submodule_support' => {$createSubmodules},
        'api_enabled' => {$apiEnabled}
    ],
    
    'routes' => [
        'base_route' => '{$this->config['route_name']}',
        'namespace' => '{$this->namespace}\\\\Routes',
        'auto_discovery_path' => 'routes/Routes.php'
    ],
    
    'database' => [
        'table_name' => '{$this->config['table_name']}',
        'create_table' => {$createTable}
    ],
    
    'environment' => [
        'respect_cache_settings' => true,
        'debug_mode_aware' => true,
        'production_optimized' => true
    ]
];
PHP;
    }

    private function getEnhancedApiDocsTemplate(): string
    {
        return <<<MD
# {$this->namespace} Enhanced API Documentation

## Overview
This is an enhanced API module for {$this->namespace} with auto-discovery capabilities.

**Features:**
- ✅ Auto-discovered by InitModsImproved.php
- ✅ Environment-aware configuration
- ✅ Caching support
- ✅ Enhanced error handling
- ✅ Debug mode support

## Base URL
```
{BASE_URL}/{$this->config['route_name']}/api
```

## Auto-Discovery
This module is automatically discovered by `InitModsImproved.php`. No manual registration required!

**Discovery path:** `{$this->getModuleWebPath()}/Routes/Routes.php`

## Enhanced Features

### Environment Awareness
The API respects environment settings:
- `APP_ENV=development` - Enhanced debugging and error messages
- `APP_ENV=production` - Optimized performance and minimal output
- `ROUTE_USE_CACHE=true` - Enables response caching
- `ROUTE_DEBUG_OUTPUT=true` - Shows debug information

### Caching Support
Responses are automatically cached when `ROUTE_USE_CACHE=true`:
- Cache TTL: 3600 seconds (1 hour)
- Cache invalidation: Automatic on data changes
- Development mode: Caching disabled for fresh data

### Error Handling
Enhanced error responses with environment-appropriate detail levels:

**Development:**
```json
{
    "error": "Detailed error message",
    "debug": {
        "file": "/path/to/file.php",
        "line": 42,
        "trace": ["..."]
    },
    "environment": "development"
}
```

**Production:**
```json
{
    "error": "An error occurred",
    "environment": "production"
}
```

## Endpoints

### GET /{$this->config['route_name']}/api
Get module information and status.

**Response:**
```json
{
    "success": true,
    "module": "{$this->namespace}",
    "version": "2.0-enhanced",
    "features": {
        "auto_discovery": true,
        "caching": true,
        "middleware_ready": true,
        "submodule_support": true
    },
    "request": {
        "route": "/{$this->config['route_name']}/api",
        "method": "GET",
        "timestamp": "2023-01-01T12:00:00+00:00"
    }
}
```

### GET /{$this->config['route_name']}/api/debug
Get debug information (development only).

**Response:**
```json
{
    "debug": true,
    "environment": "development",
    "module_path": "{$this->getModuleWebPath()}/",
    "auto_discovery": {
        "enabled": true,
        "discovered_by": "InitModsImproved.php",
        "route_file": "Routes/Routes.php"
    },
    "cache_status": {
        "enabled": true,
        "driver": "file",
        "ttl": 3600
    }
}
```

## Integration Examples

### JavaScript/AJAX
```javascript
// Using the enhanced module JavaScript
const api = new Enhanced{$this->namespace}Module();
api.callApi('test').then(response => {
    console.log('API Response:', response);
});
```

### PHP Integration
```php
// The module is auto-discovered, just use the routes
\$response = file_get_contents(BASE_URL . '/{$this->config['route_name']}/api');
\$data = json_decode(\$response, true);
```

### cURL Example
```bash
curl -X GET "http://localhost/{$this->config['route_name']}/api" \\
     -H "Accept: application/json"
```

## Configuration

### Environment Variables
```properties
# Enable/disable caching for this module
ROUTE_USE_CACHE=true

# Enable debug output
ROUTE_DEBUG_OUTPUT=false

# Module-specific configuration
{strtoupper($this->namespace)}_API_ENABLED=true
{strtoupper($this->namespace)}_CACHE_TTL=3600
```

### Module Config
See `etc/config.php` for module-specific configuration options.

## Troubleshooting

### API Not Found
1. Check that `ROUTE_SUBMODULE_DISCOVERY=true` if this is a submodule
2. Verify `routes/Routes.php` exists and is properly formatted
3. Clear cache: set `ROUTE_USE_CACHE=false` temporarily

### Debug Information
Enable debug mode to see detailed information:
```properties
ROUTE_DEBUG_OUTPUT=true
APP_ENV=development
```

### Performance Optimization
For production environments:
```properties
ROUTE_USE_CACHE=true
APP_ENV=production
ROUTE_DEBUG_OUTPUT=false
```

## Support
This enhanced module is designed to work seamlessly with upMVC v2.0 and InitModsImproved.php auto-discovery.
MD;
    }

    // Remaining helper methods
    private function createDatabaseTable(): void
    {
        if (empty($this->config['fields'])) {
            echo "⚠️  No fields defined, skipping table creation\n";
            return;
        }

        try {
            // Enhanced: Use Environment for database configuration
            $host = Environment::get('DB_HOST', '127.0.0.1');
            $dbname = Environment::get('DB_NAME', 'upmvc');
            $username = Environment::get('DB_USER', 'root');
            $password = Environment::get('DB_PASS', '');

            $pdo = new PDO("mysql:host={$host};dbname={$dbname}", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sql = $this->generateTableSQL();
            $pdo->exec($sql);
            
            echo "✅ Database table '{$this->config['table_name']}' created successfully\n";
            
        } catch (PDOException $e) {
            echo "❌ Database error: " . $e->getMessage() . "\n";
            echo "📝 Please create the table manually using this SQL:\n";
            echo $this->generateTableSQL() . "\n";
        }
    }

    private function generateTableSQL(): string
    {
        $fields = ["id INT AUTO_INCREMENT PRIMARY KEY"];
        
        foreach ($this->config['fields'] as $field) {
            $fields[] = "`{$field['name']}` {$field['sql_type']} NOT NULL";
        }
        
        $fields[] = "created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
        $fields[] = "updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
        
        return "CREATE TABLE IF NOT EXISTS `{$this->config['table_name']}` (\n  " . 
               implode(",\n  ", $fields) . 
               "\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    }

    private function writeFile(string $relativePath, string $content): void
    {
        $filePath = $this->modulePath . $relativePath;
        $dir = dirname($filePath);
        
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        file_put_contents($filePath, $content);
        echo "📄 Generated: " . str_replace(__DIR__ . '/../../', '', $filePath) . "\n";
    }
}




