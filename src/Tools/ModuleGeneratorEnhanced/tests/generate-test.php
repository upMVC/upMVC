<?php
/**
 * Simple test to generate a module and verify namespace convention
 */

require_once 'ModuleGeneratorEnhanced.php';

use App\Tools\ModuleGeneratorEnhanced\ModuleGeneratorEnhanced;

echo "🧪 Generating Test Module: 'TestNamespace'\n";
echo "==========================================\n";

// Test with mixed case input
$config = [
    'name' => 'TestNamespace',
    'type' => 'basic',
    'create_table' => false,
    'use_middleware' => false,
    'create_submodules' => false
];

try {
    $generator = new ModuleGeneratorEnhanced($config);
    
    echo "🚀 Starting generation...\n";
    $success = $generator->generate();
    
    if ($success) {
        echo "\n✅ Module generated successfully!\n";
        echo "📂 Check: modules/testnamespace/ directory\n";
        echo "🏷️  Expected namespace: 'Testnamespace'\n";
    } else {
        echo "\n❌ Module generation failed!\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}




