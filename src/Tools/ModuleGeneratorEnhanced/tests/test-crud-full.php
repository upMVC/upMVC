<?php
/**
 * Test full CRUD module generation
 */

require_once __DIR__ . '/../../../../vendor/autoload.php';

use App\Tools\ModuleGeneratorEnhanced\ModuleGeneratorEnhanced;

echo "=== Testing Full CRUD Module ===\n\n";

$config = [
    'name' => 'Product',
    'type' => 'crud',
    'fields' => [
        ['name' => 'name', 'sql_type' => 'VARCHAR(255)', 'html_type' => 'text'],
        ['name' => 'description', 'sql_type' => 'TEXT', 'html_type' => 'textarea'],
        ['name' => 'price', 'sql_type' => 'DECIMAL(10,2)', 'html_type' => 'number'],
        ['name' => 'status', 'sql_type' => 'ENUM("active","inactive")', 'html_type' => 'select'],
    ],
    'create_table' => true,
    'use_middleware' => false
];

try {
    $generator = new ModuleGeneratorEnhanced($config);
    if ($generator->generate()) {
        echo "\n✅ CRUD module created successfully!\n";
        echo "\n📊 Generated Files:\n";
        echo "   ✓ Controller.php (with create, edit, update, delete methods)\n";
        echo "   ✓ Model.php (with full CRUD database operations)\n";
        echo "   ✓ View.php (with flash messages)\n";
        echo "   ✓ views/index.php (list with edit/delete buttons)\n";
        echo "   ✓ views/form.php (dynamic form for all fields)\n";
        echo "   ✓ Routes/Routes.php (all CRUD routes registered)\n";
        
        echo "\n📦 Next: Run 'composer dump-autoload'\n";
        echo "🌐 Test: http://localhost/upMVC/public/products\n";
        echo "   - Create: ?action=create\n";
        echo "   - Edit: ?action=edit&id=1\n";
        echo "   - Delete: ?action=delete&id=1\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
