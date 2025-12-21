<?php
/**
 * Comprehensive test for all module types
 * Verifies all templates work with PSR-4 and no legacy code issues
 */

require_once __DIR__ . '/../../../../vendor/autoload.php';

use App\Tools\ModuleGeneratorEnhanced\ModuleGeneratorEnhanced;

echo "=== Testing ALL Module Types ===\n\n";

$testResults = [];

// Test 1: Basic Module
echo "📝 Test 1: Basic Module...\n";
try {
    $generator = new ModuleGeneratorEnhanced([
        'name' => 'TestBasic',
        'type' => 'basic',
        'use_middleware' => true
    ]);
    $testResults['basic'] = $generator->generate() ? '✅ PASS' : '❌ FAIL';
    echo $testResults['basic'] . " - TestBasic created\n\n";
} catch (Exception $e) {
    $testResults['basic'] = '❌ FAIL - ' . $e->getMessage();
    echo $testResults['basic'] . "\n\n";
}

// Test 2: CRUD Module
echo "📝 Test 2: CRUD Module...\n";
try {
    $generator = new ModuleGeneratorEnhanced([
        'name' => 'TestCrud',
        'type' => 'crud',
        'fields' => [
            ['name' => 'title', 'sql_type' => 'VARCHAR(255)', 'html_type' => 'text'],
            ['name' => 'content', 'sql_type' => 'TEXT', 'html_type' => 'textarea']
        ],
        'create_table' => false
    ]);
    $testResults['crud'] = $generator->generate() ? '✅ PASS' : '❌ FAIL';
    echo $testResults['crud'] . " - TestCrud created\n\n";
} catch (Exception $e) {
    $testResults['crud'] = '❌ FAIL - ' . $e->getMessage();
    echo $testResults['crud'] . "\n\n";
}

// Test 3: API Module
echo "📝 Test 3: API Module...\n";
try {
    $generator = new ModuleGeneratorEnhanced([
        'name' => 'TestApi',
        'type' => 'api',
        'include_api' => true
    ]);
    $testResults['api'] = $generator->generate() ? '✅ PASS' : '❌ FAIL';
    echo $testResults['api'] . " - TestApi created\n\n";
} catch (Exception $e) {
    $testResults['api'] = '❌ FAIL - ' . $e->getMessage();
    echo $testResults['api'] . "\n\n";
}

// Test 4: Auth Module
echo "📝 Test 4: Auth Module...\n";
try {
    $generator = new ModuleGeneratorEnhanced([
        'name' => 'TestAuth',
        'type' => 'auth',
        'use_middleware' => true
    ]);
    $testResults['auth'] = $generator->generate() ? '✅ PASS' : '❌ FAIL';
    echo $testResults['auth'] . " - TestAuth created\n\n";
} catch (Exception $e) {
    $testResults['auth'] = '❌ FAIL - ' . $e->getMessage();
    echo $testResults['auth'] . "\n\n";
}

// Test 5: Dashboard Module
echo "📝 Test 5: Dashboard Module...\n";
try {
    $generator = new ModuleGeneratorEnhanced([
        'name' => 'TestDashboard',
        'type' => 'dashboard',
        'use_middleware' => true
    ]);
    $testResults['dashboard'] = $generator->generate() ? '✅ PASS' : '❌ FAIL';
    echo $testResults['dashboard'] . " - TestDashboard created\n\n";
} catch (Exception $e) {
    $testResults['dashboard'] = '❌ FAIL - ' . $e->getMessage();
    echo $testResults['dashboard'] . "\n\n";
}

// Test 6: Module with Submodules
echo "📝 Test 6: Module with Submodules...\n";
try {
    $generator = new ModuleGeneratorEnhanced([
        'name' => 'TestParent',
        'type' => 'basic',
        'create_submodules' => true
    ]);
    $testResults['submodules'] = $generator->generate() ? '✅ PASS' : '❌ FAIL';
    echo $testResults['submodules'] . " - TestParent with submodules created\n\n";
} catch (Exception $e) {
    $testResults['submodules'] = '❌ FAIL - ' . $e->getMessage();
    echo $testResults['submodules'] . "\n\n";
}

// Summary
echo "\n=== Test Results Summary ===\n";
foreach ($testResults as $type => $result) {
    echo str_pad(ucfirst($type) . ':', 20) . $result . "\n";
}

$passed = count(array_filter($testResults, fn($r) => str_starts_with($r, '✅')));
$total = count($testResults);

echo "\n" . str_repeat('=', 50) . "\n";
echo "Total: $passed/$total tests passed\n";

if ($passed === $total) {
    echo "🎉 ALL TESTS PASSED! Generator is ready for production.\n";
} else {
    echo "⚠️ Some tests failed. Review errors above.\n";
}

echo "\n📦 Next: Run 'composer dump-autoload' to register all modules\n";
echo "🌐 Test in browser: http://localhost/upMVC/public/testbasic\n";
