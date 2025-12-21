<?php
/**
 * Final Comprehensive Test for Enhanced Module Generator
 * Tests all module types to ensure complete functionality
 */

require_once 'ModuleGeneratorEnhanced.php';

echo "🧪 Final Comprehensive Test - Enhanced Module Generator\n";
echo str_repeat("=", 60) . "\n\n";

// Test configurations for different module types
$testConfigs = [
    [
        'name' => 'BasicTest',
        'type' => 'basic',
        'description' => 'A basic test module',
        'fields' => [],
        'features' => ['views', 'assets'],
        'submodules' => false,
        'middleware' => false
    ],
    [
        'name' => 'CrudTest', 
        'type' => 'crud',
        'description' => 'A CRUD test module',
        'fields' => [
            ['name' => 'title', 'type' => 'string', 'required' => true],
            ['name' => 'price', 'type' => 'decimal', 'required' => true],
            ['name' => 'active', 'type' => 'boolean', 'required' => false]
        ],
        'features' => ['views', 'assets', 'api', 'search'],
        'submodules' => true,
        'middleware' => true
    ],
    [
        'name' => 'ApiTest',
        'type' => 'api', 
        'description' => 'An API test module',
        'fields' => [
            ['name' => 'endpoint', 'type' => 'string', 'required' => true]
        ],
        'features' => ['api'],
        'submodules' => false,
        'middleware' => true
    ]
];

$results = [];

foreach ($testConfigs as $index => $config) {
    echo "📋 Test " . ($index + 1) . ": {$config['name']} ({$config['type']})\n";
    echo str_repeat("-", 40) . "\n";
    
    try {
        // Create configuration array
        $generatorConfig = [
            'name' => $config['name'],
            'type' => $config['type'],
            'description' => $config['description'],
            'fields' => $config['fields'],
            'use_middleware' => $config['middleware'],
            'create_submodules' => $config['submodules']
        ];
        
        $generator = new \Tools\ModuleGeneratorEnhanced\ModuleGeneratorEnhanced($generatorConfig);
        
        // Generate module
        $result = $generator->generate();
        
        if ($result) {
            echo "✅ Module '{$config['name']}' generated successfully!\n";
            $results[$config['name']] = 'PASS';
            
            // Check if main files exist
            $moduleDir = "../../modules/{$config['name']}";
            $requiredFiles = ['Controller.php', 'Model.php', 'View.php', 'routes/Routes.php'];
            
            foreach ($requiredFiles as $file) {
                if (file_exists("$moduleDir/$file")) {
                    echo "   ✓ $file exists\n";
                } else {
                    echo "   ✗ $file missing\n";
                    $results[$config['name']] = 'PARTIAL';
                }
            }
            
        } else {
            echo "❌ Module '{$config['name']}' generation failed!\n";
            $results[$config['name']] = 'FAIL';
        }
        
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
        $results[$config['name']] = 'ERROR';
    }
    
    echo "\n";
}

// Summary
echo str_repeat("=", 60) . "\n";
echo "📊 Test Results Summary:\n";
echo str_repeat("=", 60) . "\n";

foreach ($results as $module => $status) {
    $icon = match($status) {
        'PASS' => '✅',
        'PARTIAL' => '⚠️',
        'FAIL' => '❌',
        'ERROR' => '💥'
    };
    echo "$icon $module: $status\n";
}

$passCount = count(array_filter($results, fn($status) => $status === 'PASS'));
$totalCount = count($results);

echo "\n📈 Overall Results: $passCount/$totalCount tests passed\n";

if ($passCount === $totalCount) {
    echo "🎉 All tests passed! Enhanced Module Generator is ready for production use.\n";
} else {
    echo "⚠️  Some tests failed. Please review the results above.\n";
}

echo "\n🔄 To clean up test modules, run:\n";
echo "Remove-Item -Path '../../modules/BasicTest' -Recurse -Force\n";
echo "Remove-Item -Path '../../modules/CrudTest' -Recurse -Force\n";
echo "Remove-Item -Path '../../modules/ApiTest' -Recurse -Force\n";
?>




