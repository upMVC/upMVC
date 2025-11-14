<?php
/**
 * Test InitModsImproved class name generation
 */

require_once __DIR__ . '/vendor/autoload.php';

// Simulate the logic
$modulesPath = 'd:/GitHub/upMVC/src/Modules';
$routeFile = 'd:/GitHub/upMVC/src/Modules/Moda/Modules/Suba/routes/Routes.php';

echo "Testing Submodule Class Name Generation\n";
echo "========================================\n\n";

echo "Input:\n";
echo "  modulesPath: {$modulesPath}\n";
echo "  routeFile: {$routeFile}\n\n";

// Step 1: Get relative path
$relativePath = str_replace($modulesPath . '/', '', dirname(dirname($routeFile)));
echo "Step 1 - Relative Path:\n";
echo "  dirname(dirname(routeFile)): " . dirname(dirname($routeFile)) . "\n";
echo "  relativePath: '{$relativePath}'\n\n";

// Step 2: Explode path
$parts = explode('/', $relativePath);
echo "Step 2 - Explode Path:\n";
echo "  parts: " . print_r($parts, true) . "\n";

// Step 3: Extract names
$subModuleName = end($parts);
$parentName = $parts[0] ?? '';
echo "Step 3 - Extract Names:\n";
echo "  subModuleName: '{$subModuleName}'\n";
echo "  parentName: '{$parentName}'\n\n";

// Step 4: Build class name
$className = 'App\\Modules\\' . ucfirst($parentName) . '\\Modules\\' . ucfirst($subModuleName) . '\\Routes\\Routes';
echo "Step 4 - Build Class Name:\n";
echo "  className: '{$className}'\n\n";

// Expected result
echo "Expected: 'App\\Modules\\Moda\\Modules\\Suba\\Routes\\Routes'\n";
echo "Match: " . ($className === 'App\\Modules\\Moda\\Modules\\Suba\\Routes\\Routes' ? '✅ YES' : '❌ NO') . "\n\n";

// Test if class exists
echo "Class exists check:\n";
if (class_exists($className)) {
    echo "  ✅ Class '{$className}' found!\n";
} else {
    echo "  ❌ Class '{$className}' NOT found!\n";
    echo "  Trying to find what exists...\n";
    
    // Try different variations
    $variations = [
        'App\\Modules\\Moda\\Modules\\Suba\\Routes\\Routes',
        'App\\Modules\\Moda\\modules\\Suba\\Routes\\Routes',
        'App\\Modules\\Moda\\Suba\\Routes\\Routes',
    ];
    
    foreach ($variations as $var) {
        if (class_exists($var)) {
            echo "  ✅ Found: {$var}\n";
        }
    }
}
