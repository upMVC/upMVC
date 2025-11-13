<?php
/**
 * Test Submodule Route Discovery
 * 
 * Specifically check if Suba submodule routes are being discovered and registered
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Etc\Router;
use App\Etc\InitModsImproved;

echo "=== SUBMODULE ROUTE DISCOVERY TEST ===\n\n";

// 1. Create router
$router = new Router();
echo "1. Router created\n\n";

// 2. Create InitModsImproved with debug output enabled
$initMods = new InitModsImproved();
$initMods->setDebugOutput(true);
$initMods->setVerboseLogging(true);

echo "2. Calling addRoutes() with verbose output...\n";
echo str_repeat("-", 80) . "\n";

$initMods->addRoutes($router);

echo str_repeat("-", 80) . "\n";
echo "3. Route registration complete\n\n";

// 3. Check registered routes using reflection
$reflection = new ReflectionClass($router);
$routesProperty = $reflection->getProperty('routes');
$routesProperty->setAccessible(true);
$registeredRoutes = $routesProperty->getValue($router);

echo "4. Total routes registered: " . count($registeredRoutes) . "\n\n";

if (!empty($registeredRoutes)) {
    echo "5. Checking for Test module routes:\n";
    $testRoutes = ['/', '/test', '/test.php', '/index.php'];
    foreach ($testRoutes as $route) {
        $exists = isset($registeredRoutes[$route]);
        echo "   Route '$route': " . ($exists ? '✅ FOUND' : '❌ MISSING') . "\n";
        if ($exists) {
            echo "      -> {$registeredRoutes[$route]['className']}::{$registeredRoutes[$route]['methodName']}\n";
        }
    }
    
    echo "\n6. Checking for Suba submodule routes:\n";
    $subaRoutes = ['/suba', '/suba.php', '/suba/subpage'];
    foreach ($subaRoutes as $route) {
        $exists = isset($registeredRoutes[$route]);
        echo "   Route '$route': " . ($exists ? '✅ FOUND' : '❌ MISSING') . "\n";
        if ($exists) {
            echo "      -> {$registeredRoutes[$route]['className']}::{$registeredRoutes[$route]['methodName']}\n";
        }
    }
    
    echo "\n7. All registered route keys:\n";
    foreach (array_keys($registeredRoutes) as $key) {
        $route = $registeredRoutes[$key];
        echo "   '$key' -> {$route['className']}::{$route['methodName']}\n";
    }
} else {
    echo "⚠️ WARNING: No routes registered at all!\n";
}

echo "\n8. Statistics:\n";
$stats = $initMods->getStats();
print_r($stats);

echo "\n=== END TEST ===\n";
