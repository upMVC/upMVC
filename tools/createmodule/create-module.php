<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Tools\CreateModule\ModuleGenerator;

// Function to get user input
function prompt(string $question): string {
    echo $question . " ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);
    return trim($line);
}

// Display welcome message
echo "\n=== upMVC Module Generator ===\n\n";

try {
    // Get module name
    $moduleName = prompt("Enter module name (e.g., Blog):");
    if (empty($moduleName)) {
        throw new Exception("Module name cannot be empty!");
    }

    // Get module type
    echo "\nAvailable module types:\n";
    echo "1. basic - Standard MVC module\n";
    echo "2. api - REST API module\n";
    echo "3. react - React integration module\n";
    
    $typeChoice = prompt("\nSelect module type (1-3, default: 1):");
    
    // Map choice to type
    $typeMap = [
        '1' => 'basic',
        '2' => 'api',
        '3' => 'react'
    ];
    
    $moduleType = $typeMap[$typeChoice] ?? 'basic';

    // Create and run generator
    $generator = new ModuleGenerator($moduleName, $moduleType);
    
    echo "\nGenerating {$moduleType} module '{$moduleName}'...\n";
    
    if ($generator->generate()) {
        echo "\n✅ Module generated successfully!\n";
        echo "\nNext steps:\n";
        echo "1. Run 'composer dump-autoload' to update autoloader\n";
        echo "2. Check the generated files in modules/{$moduleName}/\n";
        echo "3. Add your custom logic to the Controller, Model, and View\n";
        echo "4. Access your module at: http://your-domain/{$moduleName}\n";
    } else {
        echo "\n❌ Failed to generate module.\n";
    }

} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}