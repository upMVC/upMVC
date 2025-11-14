<?php
/**
 * Test Routing Debug
 * 
 * Debug script to trace the route processing pipeline
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Etc\Config\Environment;
use App\Etc\Config;

echo "=== ROUTING DEBUG TEST ===\n\n";

// 1. Load environment
try {
    Environment::load();
    echo "1. Environment loaded ✅\n";
    echo "   SITE_PATH: '" . Environment::get('SITE_PATH') . "'\n";
    echo "   DOMAIN_NAME: '" . Environment::get('DOMAIN_NAME') . "'\n\n";
} catch (Exception $e) {
    echo "❌ Environment load failed: " . $e->getMessage() . "\n";
    exit(1);
}

// 2. Test Config::getSitePath()
echo "2. Config::getSitePath(): '" . Config::getSitePath() . "'\n\n";

// 3. Simulate request URI processing
$testURIs = [
    '/upMVC/',
    '/upMVC/test',
    '/upMVC/test.php',
    '/test',
    '/',
];

$config = new Config();

echo "3. Testing URI processing:\n";
foreach ($testURIs as $uri) {
    try {
        $cleanRoute = $config->getReqRoute($uri);
        echo "   URI: '$uri' -> Clean Route: '$cleanRoute'\n";
    } catch (Exception $e) {
        echo "   URI: '$uri' -> ERROR: " . $e->getMessage() . "\n";
    }
}
echo "\n";

echo "=== END DEBUG ===\n";
