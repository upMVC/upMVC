<?php
/**
 * Quick test script for ModuleGeneratorEnhanced
 * Tests the updated PSR-4 structure
 */

require_once __DIR__ . '/../../../vendor/autoload.php';

use App\Tools\ModuleGeneratorEnhanced\ModuleGeneratorEnhanced;

echo "=== Testing Enhanced Module Generator with PSR-4 Structure ===\n\n";

// Test 1: Basic Module
echo "📝 Test 1: Creating basic module 'TestBlog'...\n";
$config1 = [
    'name' => 'TestBlog',
    'type' => 'basic',
    'use_middleware' => true,
    'create_submodules' => false
];

try {
    $generator1 = new ModuleGeneratorEnhanced($config1);
    if ($generator1->generate()) {
        echo "✅ TestBlog module created successfully!\n";
        echo "   Location: src/Modules/TestBlog/\n";
        echo "   Namespace: App\\Modules\\TestBlog\n\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
}

// Test 2: CRUD Module with fields
echo "📝 Test 2: Creating CRUD module 'TestProduct'...\n";
$config2 = [
    'name' => 'TestProduct',
    'type' => 'crud',
    'fields' => [
        ['name' => 'name', 'sql_type' => 'VARCHAR(255)', 'html_type' => 'text'],
        ['name' => 'price', 'sql_type' => 'DECIMAL(10,2)', 'html_type' => 'number'],
        ['name' => 'description', 'sql_type' => 'TEXT', 'html_type' => 'textarea'],
    ],
    'create_table' => false, // Skip DB creation for test
    'use_middleware' => true,
    'create_submodules' => false
];

try {
    $generator2 = new ModuleGeneratorEnhanced($config2);
    if ($generator2->generate()) {
        echo "✅ TestProduct CRUD module created successfully!\n";
        echo "   Location: src/Modules/TestProduct/\n";
        echo "   Namespace: App\\Modules\\TestProduct\n\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
}

echo "=== Test Complete ===\n";
echo "Next steps:\n";
echo "1. Run: composer dump-autoload\n";
echo "2. Check generated modules in src/Modules/\n";
echo "3. Verify auto-discovery with InitModsImproved.php\n";
