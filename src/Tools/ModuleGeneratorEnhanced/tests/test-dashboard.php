<?php
/**
 * Test dashboard module generation
 */

require_once __DIR__ . '/../../../../vendor/autoload.php';

use App\Tools\ModuleGeneratorEnhanced\ModuleGeneratorEnhanced;

echo "=== Testing Dashboard Module ===\n\n";

$config = [
    'name' => 'TestDashboard',
    'type' => 'dashboard',
    'use_middleware' => true
];

try {
    $generator = new ModuleGeneratorEnhanced($config);
    if ($generator->generate()) {
        echo "✅ Dashboard module created successfully!\n";
        echo "   Location: src/Modules/TestDashboard/\n";
        echo "   View template: views/dashboard.php\n\n";
        echo "📦 Next: Run 'composer dump-autoload'\n";
        echo "🌐 Test: http://localhost/upMVC/public/testdashboard\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
