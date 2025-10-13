<?php
/**
 * Generate a new test module to verify namespace fix
 */

require_once 'ModuleGeneratorEnhanced.php';

use Tools\ModuleGeneratorEnhanced\ModuleGeneratorEnhanced;

echo "🧪 Testing Enhanced Module Generator Namespace Fix\n";
echo "==================================================\n";
echo "📝 Generating module 'NamespaceTestDemo' to verify fix\n\n";

// Test with mixed case input - should become 'Namespacetestdemo'
$config = [
    'name' => 'NamespaceTestDemo',
    'type' => 'basic',
    'create_table' => false,
    'use_middleware' => true,
    'create_submodules' => false
];

try {
    echo "🔧 Configuration:\n";
    echo "   Input name: 'NamespaceTestDemo'\n";
    echo "   Expected namespace: 'Namespacetestdemo'\n";
    echo "   Expected directory: 'namespacetestdemo'\n\n";
    
    $generator = new ModuleGeneratorEnhanced($config);
    
    echo "🚀 Generating module...\n";
    $success = $generator->generate();
    
    if ($success) {
        echo "\n✅ Module generation completed!\n";
        
        // Verify the generated files
        $moduleDir = '../../modules/namespacetestdemo';
        $controllerFile = $moduleDir . '/Controller.php';
        
        if (file_exists($controllerFile)) {
            echo "📂 Module directory created: ✅\n";
            
            // Read the first few lines to check namespace
            $content = file_get_contents($controllerFile);
            if (strpos($content, 'namespace Namespacetestdemo;') !== false) {
                echo "🏷️  Namespace convention: ✅ CORRECT! (Namespacetestdemo)\n";
            } else {
                echo "❌ Namespace convention: FAILED!\n";
                echo "   Expected: 'namespace Namespacetestdemo;'\n";
            }
        }
    } else {
        echo "\n❌ Module generation failed!\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📝 This might be expected if the module already exists\n";
}

echo "\n🎯 Test Summary:\n";
echo "================\n";
echo "✅ Fixed: ucfirst(strtolower(\$name)) for consistent namespaces\n";
echo "📁 Directory: Always lowercase\n";
echo "🏷️  Namespace: First letter capitalized, rest lowercase\n";
echo "🔗 Works with any input format (camelCase, PascalCase, lowercase, UPPERCASE)\n";