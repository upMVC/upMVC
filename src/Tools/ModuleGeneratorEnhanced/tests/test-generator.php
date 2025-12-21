<?php
/**
 * Test script for Enhanced Module Generator
 * Creates a sample CRUD module to verify functionality
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Tools\ModuleGeneratorEnhanced\ModuleGeneratorEnhanced;

echo "🧪 Testing Enhanced Module Generator\n";
echo "=====================================\n\n";

try {
    // Configuration for a test Products module
    $config = [
        'name' => 'TestProducts',
        'type' => 'crud',
        'namespace' => 'TestProducts',
        'table_name' => 'test_products',
        'route_name' => 'test-products',
        'fields' => [
            ['name' => 'name', 'sql_type' => 'VARCHAR(255)', 'html_type' => 'text'],
            ['name' => 'description', 'sql_type' => 'TEXT', 'html_type' => 'textarea'],
            ['name' => 'price', 'sql_type' => 'DECIMAL(10,2)', 'html_type' => 'number'],
            ['name' => 'category', 'sql_type' => 'VARCHAR(100)', 'html_type' => 'text'],
            ['name' => 'status', 'sql_type' => 'ENUM("active","inactive")', 'html_type' => 'select']
        ],
        'create_table' => false, // Don't create table in test
        'use_middleware' => true,
        'create_submodules' => true,
        'include_api' => true
    ];

    echo "📋 Test Configuration:\n";
    echo "   Module: {$config['name']}\n";
    echo "   Type: {$config['type']}\n";
    echo "   Fields: " . count($config['fields']) . "\n";
    echo "   Submodules: " . ($config['create_submodules'] ? 'Yes' : 'No') . "\n";
    echo "   Middleware: " . ($config['use_middleware'] ? 'Yes' : 'No') . "\n\n";

    // Generate the module
    $generator = new ModuleGeneratorEnhanced($config);
    
    if ($generator->generate()) {
        echo "\n✅ TEST PASSED: Enhanced module generated successfully!\n";
        echo "\n📁 Generated files should be in: modules/TestProducts/\n";
        echo "\n🔄 Next steps:\n";
        echo "   1. Check modules/TestProducts/ directory\n";
        echo "   2. Access at: http://localhost/upMVC/test-products\n";
        echo "   3. API at: http://localhost/upMVC/test-products/api\n";
        echo "   4. Submodule at: http://localhost/upMVC/test-products/example\n";
    } else {
        echo "\n❌ TEST FAILED: Module generation failed\n";
    }

} catch (Exception $e) {
    echo "\n❌ TEST ERROR: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=====================================\n";
echo "🧪 Test Complete\n";




