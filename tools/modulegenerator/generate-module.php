<?php
/**
 * Enhanced Module Generator for upMVC
 * 
 * Usage: php generate-module.php
 * 
 * This tool creates a complete module structure with MVC components,
 * routes, and optionally database tables.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Tools\ModuleGenerator\ModuleGenerator;

class ModuleGeneratorCLI
{
    private array $moduleTypes = [
        'basic' => 'Basic module with simple MVC structure',
        'crud' => 'Full CRUD module with database operations',
        'api' => 'API-only module with JSON responses',
        'auth' => 'Authentication module with login/logout',
        'dashboard' => 'Dashboard module with admin interface'
    ];

    public function run(): void
    {
        $this->displayHeader();
        
        try {
            $config = $this->getModuleConfiguration();
            $generator = new ModuleGenerator($config);
            
            if ($generator->generate()) {
                $this->displaySuccess($config['name']);
                $this->displayNextSteps($config);
            } else {
                $this->displayError("Failed to generate module. Check the logs for details.");
            }
        } catch (Exception $e) {
            $this->displayError("Error: " . $e->getMessage());
        }
    }

    private function displayHeader(): void
    {
        echo "\n";
        echo "╔══════════════════════════════════════════════════════════════════╗\n";
        echo "║                     upMVC Module Generator                       ║\n";
        echo "║                     Enhanced Version 2.0                        ║\n";
        echo "╚══════════════════════════════════════════════════════════════════╝\n";
        echo "\n";
    }

    private function getModuleConfiguration(): array
    {
        $config = [];
        
        // Get module name
        $config['name'] = $this->prompt("Enter module name (e.g., Blog, Product, User): ");
        if (empty($config['name'])) {
            throw new Exception("Module name is required.");
        }
        
        // Validate module name
        if (!preg_match('/^[A-Za-z][A-Za-z0-9]*$/', $config['name'])) {
            throw new Exception("Module name must start with a letter and contain only letters and numbers.");
        }
        
        // Get module type
        $config['type'] = $this->selectModuleType();
        
        // Get additional options based on type
        if ($config['type'] === 'crud') {
            $config['fields'] = $this->getCrudFields();
            $config['create_table'] = $this->confirm("Create database table automatically? (y/n): ");
        }
        
        if (in_array($config['type'], ['api', 'crud'])) {
            $config['include_api'] = true;
        }
        
        $config['namespace'] = ucfirst($config['name']);
        $config['table_name'] = strtolower($config['name']) . 's';
        
        return $config;
    }

    private function selectModuleType(): string
    {
        echo "\nAvailable module types:\n";
        foreach ($this->moduleTypes as $key => $description) {
            echo "  [{$key}] {$description}\n";
        }
        
        do {
            $type = $this->prompt("\nSelect module type (basic/crud/api/auth/dashboard): ", 'basic');
            if (array_key_exists($type, $this->moduleTypes)) {
                return $type;
            }
            echo "Invalid type. Please choose from: " . implode(', ', array_keys($this->moduleTypes)) . "\n";
        } while (true);
    }

    private function getCrudFields(): array
    {
        $fields = [];
        echo "\nDefine fields for your CRUD module:\n";
        echo "Enter field definitions (press Enter on empty field name to finish)\n";
        echo "Format: fieldname:type:html_input_type (e.g., name:VARCHAR(100):text)\n\n";
        
        while (true) {
            $fieldInput = $this->prompt("Field (name:sql_type:html_type): ");
            if (empty($fieldInput)) {
                break;
            }
            
            $parts = explode(':', $fieldInput);
            if (count($parts) < 1) {
                echo "Invalid format. Use: fieldname:sql_type:html_type\n";
                continue;
            }
            
            $field = [
                'name' => trim($parts[0]),
                'sql_type' => trim($parts[1] ?? 'VARCHAR(255)'),
                'html_type' => trim($parts[2] ?? 'text')
            ];
            
            if (empty($field['name'])) {
                echo "Field name cannot be empty.\n";
                continue;
            }
            
            $fields[] = $field;
            echo "Added field: {$field['name']} ({$field['sql_type']}) -> {$field['html_type']}\n";
        }
        
        if (empty($fields)) {
            // Default fields for basic CRUD
            $fields = [
                ['name' => 'name', 'sql_type' => 'VARCHAR(255)', 'html_type' => 'text'],
                ['name' => 'description', 'sql_type' => 'TEXT', 'html_type' => 'textarea'],
                ['name' => 'status', 'sql_type' => 'ENUM("active","inactive")', 'html_type' => 'select']
            ];
            echo "Using default fields: name, description, status\n";
        }
        
        return $fields;
    }

    private function prompt(string $message, string $default = ''): string
    {
        echo $message;
        if (!empty($default)) {
            echo "[{$default}] ";
        }
        
        $input = trim(fgets(STDIN));
        return empty($input) ? $default : $input;
    }

    private function confirm(string $message): bool
    {
        $response = strtolower($this->prompt($message));
        return in_array($response, ['y', 'yes', '1', 'true']);
    }

    private function displaySuccess(string $moduleName): void
    {
        echo "\n";
        echo "╔══════════════════════════════════════════════════════════════════╗\n";
        echo "║                     SUCCESS!                                    ║\n";
        echo "╚══════════════════════════════════════════════════════════════════╝\n";
        echo "\n";
        echo "Module '{$moduleName}' has been generated successfully!\n";
    }

    private function displayNextSteps(array $config): void
    {
        echo "\nNext steps:\n";
        echo "1. Run 'composer dump-autoload' to update autoloader\n";
        echo "2. Check the generated files in modules/{$config['name']}/\n";
        
        if ($config['type'] === 'crud' && !empty($config['fields'])) {
            echo "3. Run the SQL commands to create the database table\n";
        }
        
        echo "4. Access your module at: " . (defined('BASE_URL') ? BASE_URL : 'http://localhost') . "/{$config['table_name']}\n";
        echo "\nHappy coding!\n\n";
    }

    private function displayError(string $message): void
    {
        echo "\n";
        echo "╔══════════════════════════════════════════════════════════════════╗\n";
        echo "║                     ERROR!                                      ║\n";
        echo "╚══════════════════════════════════════════════════════════════════╝\n";
        echo "\n";
        echo $message . "\n\n";
    }
}

// Run the CLI tool
if (php_sapi_name() === 'cli') {
    $cli = new ModuleGeneratorCLI();
    $cli->run();
} else {
    echo "This tool must be run from the command line.\n";
    echo "Usage: php generate-module.php\n";
}