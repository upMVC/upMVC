<?php
/**
 * Test the namespace fix in Enhanced Module Generator
 */

require_once 'ModuleGeneratorEnhanced.php';

use Tools\ModuleGeneratorEnhanced\ModuleGeneratorEnhanced;

echo "🧪 Testing Enhanced Module Generator Namespace Fix\n";
echo "=================================================\n";

// Test different input formats to verify namespace convention
$testCases = [
    'testnamespace',     // Should become 'Testnamespace'
    'TestNamespace',     // Should become 'Testnamespace'  
    'TESTNAMESPACE',     // Should become 'Testnamespace'
    'mixedCASE',         // Should become 'Mixedcase'
];

foreach ($testCases as $index => $testName) {
    echo "\n🔄 Test Case " . ($index + 1) . ": '$testName'\n";
    echo "----------------------------------------\n";
    
    try {
        $config = [
            'name' => $testName,
            'type' => 'basic',
            'create_table' => false,
            'use_middleware' => false,
            'create_submodules' => false
        ];
        
        $generator = new ModuleGeneratorEnhanced($config);
        
        // Get the validated config to see the namespace
        $reflection = new ReflectionClass($generator);
        $configProperty = $reflection->getProperty('config');
        $configProperty->setAccessible(true);
        $validatedConfig = $configProperty->getValue($generator);
        
        echo "✅ Input: '$testName'\n";
        echo "📁 Directory: '{$validatedConfig['directory_name']}'\n";
        echo "🏷️  Namespace: '{$validatedConfig['namespace']}'\n";
        echo "📋 Route: '{$validatedConfig['route_name']}'\n";
        
        // Verify the namespace follows our convention
        $expectedNamespace = ucfirst(strtolower($testName));
        if ($validatedConfig['namespace'] === $expectedNamespace) {
            echo "✅ Namespace convention: CORRECT!\n";
        } else {
            echo "❌ Namespace convention: FAILED!\n";
            echo "   Expected: '$expectedNamespace'\n";
            echo "   Got: '{$validatedConfig['namespace']}'\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
}

echo "\n🎯 Summary\n";
echo "==========\n";
echo "✅ All test cases should show 'Namespace convention: CORRECT!'\n";
echo "📝 The namespace should be: First letter capitalized, rest lowercase\n";
echo "📁 Directory names should be: All lowercase\n";
echo "🔗 Route names should be: All lowercase\n";
echo "\n🚀 Ready to generate real modules with correct naming!\n";