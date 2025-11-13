<?php
/**
 * Enhanced Module Generator CLI for upMVC v2.0
 * 
 * Features:
 * - Auto-discovery via InitModsImproved.php
 * - Submodule support
 * - Environment-aware configuration
 * - No manual framework file updates needed
 * 
 * Usage: php generate-module.php
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Tools\ModuleGeneratorEnhanced\ModuleGeneratorEnhanced;

class ModuleGeneratorEnhancedCLI
{
    private array $moduleTypes = [
        'basic' => 'Basic module with auto-discovery',
        'crud' => 'Full CRUD module with enhanced features',
        'api' => 'RESTful API module with auto-discovery',
        'auth' => 'Authentication module (middleware-ready)',
        'dashboard' => 'Dashboard module with analytics',
        'submodule' => 'Nested submodule within existing module'
    ];

    private array $existingModules = [];

    public function run(): void
    {
        $this->displayHeader();
        $this->checkPrerequisites();
        
        try {
            $config = $this->getModuleConfiguration();
            $generator = new ModuleGeneratorEnhanced($config);
            
            if ($generator->generate()) {
                $this->displaySuccess($config);
                $this->displayNextSteps($config);
            } else {
                $this->displayError("Failed to generate enhanced module. Check the logs for details.");
            }
        } catch (Exception $e) {
            $this->displayError("Error: " . $e->getMessage());
        }
    }

    private function displayHeader(): void
    {
        echo "\n";
        echo "╔════════════════════════════════════════════════════════════════════╗\n";
        echo "║                 Enhanced upMVC Module Generator v2.0               ║\n";
        echo "║                   Auto-Discovery • Submodules • Enhanced          ║\n";
        echo "╚════════════════════════════════════════════════════════════════════╝\n";
        echo "\n";
        echo "🚀 Features:\n";
        echo "   • Auto-discovery via InitModsImproved.php\n";
        echo "   • Submodule support with deep nesting\n";
        echo "   • Environment-aware configuration\n";
        echo "   • No manual framework file updates needed\n";
        echo "   • Caching and middleware integration ready\n";
        echo "\n";
    }

    private function checkPrerequisites(): void
    {
        echo "🔍 Checking prerequisites...\n";
        
        // Check InitModsImproved.php
        $initModsImproved = __DIR__ . '/../../etc/InitModsImproved.php';
        if (!file_exists($initModsImproved)) {
            throw new Exception("❌ InitModsImproved.php not found! This generator requires the enhanced upMVC system.");
        }
        echo "   ✅ InitModsImproved.php found\n";
        
        // Check Environment class
        if (!class_exists('upMVC\\Config\\Environment')) {
            echo "   ⚠️  Warning: Environment class not found. Some features may be limited.\n";
        } else {
            echo "   ✅ Environment class available\n";
        }
        
        // Load existing modules
        $this->loadExistingModules();
        echo "   ✅ Found " . count($this->existingModules) . " existing modules\n";
        
        echo "\n";
    }

    private function loadExistingModules(): void
    {
        $modulesPath = __DIR__ . '/../../modules';
        if (is_dir($modulesPath)) {
            $directories = array_filter(glob($modulesPath . '/*'), 'is_dir');
            foreach ($directories as $dir) {
                $this->existingModules[] = basename($dir);
            }
        }
    }

    private function getModuleConfiguration(): array
    {
        $config = [];
        
        // Get module name
        $config['name'] = $this->prompt("📝 Enter module name (e.g., Blog, Product, User): ");
        if (empty($config['name'])) {
            throw new Exception("Module name is required.");
        }
        
        // Validate module name
        if (!preg_match('/^[A-Za-z][A-Za-z0-9]*$/', $config['name'])) {
            throw new Exception("Module name must start with a letter and contain only letters and numbers.");
        }
        
        // Check if module exists
        if (in_array($config['name'], $this->existingModules)) {
            if (!$this->confirm("⚠️  Module '{$config['name']}' already exists. Continue anyway? (y/n): ")) {
                throw new Exception("Operation cancelled by user.");
            }
        }
        
        // Get module type
        $config['type'] = $this->selectModuleType();
        
        // Handle submodule specific configuration
        if ($config['type'] === 'submodule') {
            $config['parent_module'] = $this->selectParentModule();
        }
        
        // Get additional options based on type
        if ($config['type'] === 'crud') {
            $config['fields'] = $this->getCrudFields();
            $config['create_table'] = $this->confirm("🗄️  Create database table automatically? (y/n): ");
        }
        
        // Enhanced features
        $config['use_middleware'] = $this->confirm("🔒 Enable middleware integration? (y/n): ", true);
        $config['create_submodules'] = $this->confirm("📦 Create submodule structure? (y/n): ", false);
        
        if (in_array($config['type'], ['api', 'crud'])) {
            $config['include_api'] = true;
        }
        
        // Set defaults
        $config['namespace'] = ucfirst($config['name']);
        $config['table_name'] = strtolower($config['name']) . 's';
        
        // Enhanced: Auto-generate route names based on type
        $config['route_name'] = match($config['type']) {
            'submodule' => strtolower($config['name']),
            'api' => 'api/' . strtolower($config['name']),
            default => strtolower($config['name']) . 's'
        };
        
        return $config;
    }

    private function selectModuleType(): string
    {
        echo "\n📋 Available enhanced module types:\n";
        foreach ($this->moduleTypes as $key => $description) {
            $icon = match($key) {
                'basic' => '📄',
                'crud' => '📊',
                'api' => '🔌',
                'auth' => '🔐',
                'dashboard' => '📈',
                'submodule' => '📦'
            };
            echo "   [{$key}] {$icon} {$description}\n";
        }
        
        do {
            $type = $this->prompt("\n🎯 Select module type", 'basic');
            if (array_key_exists($type, $this->moduleTypes)) {
                return $type;
            }
            echo "❌ Invalid type. Please choose from: " . implode(', ', array_keys($this->moduleTypes)) . "\n";
        } while (true);
    }

    private function selectParentModule(): string
    {
        if (empty($this->existingModules)) {
            throw new Exception("No existing modules found. Create a parent module first.");
        }
        
        echo "\n📦 Available parent modules:\n";
        foreach ($this->existingModules as $index => $module) {
            echo "   [" . ($index + 1) . "] {$module}\n";
        }
        
        do {
            $selection = $this->prompt("\n🎯 Select parent module (number or name): ");
            
            if (is_numeric($selection)) {
                $index = (int)$selection - 1;
                if (isset($this->existingModules[$index])) {
                    return $this->existingModules[$index];
                }
            } elseif (in_array($selection, $this->existingModules)) {
                return $selection;
            }
            
            echo "❌ Invalid selection. Please choose a valid module.\n";
        } while (true);
    }

    private function getCrudFields(): array
    {
        $fields = [];
        echo "\n📝 Define fields for your CRUD module:\n";
        echo "💡 Enter field definitions (press Enter on empty field name to finish)\n";
        echo "📋 Format: fieldname:type:html_input_type (e.g., name:VARCHAR(100):text)\n";
        echo "🔧 Available HTML types: text, email, number, textarea, select, date, checkbox\n\n";
        
        while (true) {
            $fieldInput = $this->prompt("🔹 Field (name:sql_type:html_type): ");
            if (empty($fieldInput)) {
                break;
            }
            
            $parts = explode(':', $fieldInput);
            if (count($parts) < 1) {
                echo "❌ Invalid format. Use: fieldname:sql_type:html_type\n";
                continue;
            }
            
            $field = [
                'name' => trim($parts[0]),
                'sql_type' => trim($parts[1] ?? 'VARCHAR(255)'),
                'html_type' => trim($parts[2] ?? 'text')
            ];
            
            if (empty($field['name'])) {
                echo "❌ Field name cannot be empty.\n";
                continue;
            }
            
            $fields[] = $field;
            echo "   ✅ Added field: {$field['name']} ({$field['sql_type']}) -> {$field['html_type']}\n";
        }
        
        if (empty($fields)) {
            // Enhanced default fields
            $fields = [
                ['name' => 'title', 'sql_type' => 'VARCHAR(255)', 'html_type' => 'text'],
                ['name' => 'description', 'sql_type' => 'TEXT', 'html_type' => 'textarea'],
                ['name' => 'status', 'sql_type' => 'ENUM("active","inactive")', 'html_type' => 'select'],
                ['name' => 'created_by', 'sql_type' => 'INT', 'html_type' => 'number']
            ];
            echo "📄 Using enhanced default fields: title, description, status, created_by\n";
        }
        
        return $fields;
    }

    private function prompt(string $message, string $default = ''): string
    {
        echo $message;
        if (!empty($default)) {
            echo " [{$default}]";
        }
        echo ": ";
        
        $input = trim(fgets(STDIN));
        return empty($input) ? $default : $input;
    }

    private function confirm(string $message, bool $default = false): bool
    {
        $defaultText = $default ? 'Y/n' : 'y/N';
        $response = strtolower($this->prompt($message . " [{$defaultText}]"));
        
        if (empty($response)) {
            return $default;
        }
        
        return in_array($response, ['y', 'yes', '1', 'true']);
    }

    private function displaySuccess(array $config): void
    {
        echo "\n";
        echo "╔════════════════════════════════════════════════════════════════════╗\n";
        echo "║                          🎉 SUCCESS! 🎉                           ║\n";
        echo "╚════════════════════════════════════════════════════════════════════╝\n";
        echo "\n";
        echo "✅ Enhanced module '{$config['name']}' has been generated successfully!\n";
        echo "\n";
        echo "🔍 Module Details:\n";
        echo "   • Type: {$config['type']}\n";
        echo "   • Auto-discovery: ✅ Enabled (InitModsImproved.php)\n";
        echo "   • Location: modules/{$config['name']}/\n";
        
        if ($config['type'] === 'submodule') {
            echo "   • Parent: {$config['parent_module']}\n";
            echo "   • Path: modules/{$config['parent_module']}/modules/{$config['name']}/\n";
        }
        
        if ($config['create_submodules'] ?? false) {
            echo "   • Submodules: ✅ Structure created\n";
        }
        
        echo "\n";
    }

    private function displayNextSteps(array $config): void
    {
        echo "🚀 Next Steps:\n";
        echo "\n";
        
        echo "1️⃣  Update autoloader:\n";
        echo "   composer dump-autoload\n";
        echo "\n";
        
        echo "2️⃣  Review generated files:\n";
        $path = $config['type'] === 'submodule' 
            ? "modules/{$config['parent_module']}/modules/{$config['name']}/"
            : "modules/{$config['name']}/";
        echo "   📁 {$path}\n";
        echo "   📄 {$path}Controller.php\n";
        echo "   📄 {$path}routes/Routes.php (auto-discovered!)\n";
        echo "\n";
        
        if ($config['type'] === 'crud' && !empty($config['fields'])) {
            echo "3️⃣  Create database table:\n";
            echo "   Check the SQL output above or run the generated migration\n";
            echo "\n";
        }
        
        echo "4️⃣  Access your module:\n";
        $baseUrl = $this->getBaseUrl();
        $route = $config['route_name'];
        if ($config['type'] === 'submodule') {
            $parentRoute = strtolower($config['parent_module']) . 's';
            echo "   🌐 {$baseUrl}/{$parentRoute}/{$route}\n";
        } else {
            echo "   🌐 {$baseUrl}/{$route}\n";
        }
        echo "\n";
        
        echo "5️⃣  Environment Configuration:\n";
        echo "   ⚙️  Check .env for route discovery settings:\n";
        echo "       ROUTE_SUBMODULE_DISCOVERY=true\n";
        echo "       ROUTE_USE_CACHE=true\n";
        echo "       ROUTE_DEBUG_OUTPUT=false\n";
        echo "\n";
        
        if ($config['use_middleware']) {
            echo "6️⃣  Middleware Integration:\n";
            echo "   🔒 Uncomment middleware lines in Controller.php\n";
            echo "   📝 Configure authentication/authorization as needed\n";
            echo "\n";
        }
        
        echo "✨ Your enhanced module is ready! No manual framework updates needed.\n";
        echo "🔄 InitModsImproved.php will auto-discover your routes.\n";
        echo "\n";
        echo "📚 For help: Check the README.md in tools/modulegenerator-enhanced/\n";
        echo "\n";
    }

    private function getBaseUrl(): string
    {
        // Try to get BASE_URL from environment or config
        if (defined('BASE_URL')) {
            return BASE_URL;
        }
        
        // Try to detect from .env
        $envFile = __DIR__ . '/../../etc/.env';
        if (file_exists($envFile)) {
            $content = file_get_contents($envFile);
            if (preg_match('/APP_URL=(.+)/', $content, $matches)) {
                $appUrl = trim($matches[1]);
                if (preg_match('/APP_PATH=(.+)/', $content, $pathMatches)) {
                    $appPath = trim($pathMatches[1]);
                    return $appUrl . $appPath;
                }
                return $appUrl;
            }
        }
        
        return 'http://localhost/upMVC';
    }

    private function displayError(string $message): void
    {
        echo "\n";
        echo "╔════════════════════════════════════════════════════════════════════╗\n";
        echo "║                          ❌ ERROR! ❌                             ║\n";
        echo "╚════════════════════════════════════════════════════════════════════╝\n";
        echo "\n";
        echo $message . "\n\n";
        echo "💡 Troubleshooting:\n";
        echo "   • Ensure InitModsImproved.php exists in etc/\n";
        echo "   • Check that the modules/ directory is writable\n";
        echo "   • Verify composer autoload is working\n";
        echo "   • Review the error message above for specific issues\n";
        echo "\n";
        echo "📚 For help: Check the README.md in tools/modulegenerator-enhanced/\n";
        echo "\n";
    }
}

// Run the enhanced CLI tool
if (php_sapi_name() === 'cli') {
    $cli = new ModuleGeneratorEnhancedCLI();
    $cli->run();
} else {
    echo "<!DOCTYPE html>\n<html><head><title>Enhanced Module Generator</title></head><body>\n";
    echo "<h1>🚀 Enhanced Module Generator v2.0</h1>\n";
    echo "<p>This tool must be run from the command line for the best experience.</p>\n";
    echo "<h2>Usage:</h2>\n";
    echo "<pre>php generate-module.php</pre>\n";
    echo "<h2>Features:</h2>\n";
    echo "<ul>\n";
    echo "<li>✅ Auto-discovery via InitModsImproved.php</li>\n";
    echo "<li>✅ Submodule support with deep nesting</li>\n";
    echo "<li>✅ Environment-aware configuration</li>\n";
    echo "<li>✅ No manual framework file updates needed</li>\n";
    echo "<li>✅ Caching and middleware integration ready</li>\n";
    echo "</ul>\n";
    echo "<p><strong>Note:</strong> For full functionality, please run this generator from the command line.</p>\n";
    echo "</body></html>\n";
}




