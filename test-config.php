<?php
/**
 * Quick test to verify Config classes load properly
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/vendor/autoload.php';

use App\Etc\Config\ConfigManager;
use App\Etc\Config\Environment;

echo "Testing Config Classes...\n\n";

try {
    // Test Environment loading
    echo "1. Loading Environment...\n";
    Environment::load();
    echo "   ✓ Environment loaded successfully\n\n";
    
    // Test getting environment variables
    echo "2. Testing Environment::get()...\n";
    $appName = Environment::get('APP_NAME', 'Default App');
    echo "   APP_NAME: {$appName}\n";
    $appEnv = Environment::get('APP_ENV', 'production');
    echo "   APP_ENV: {$appEnv}\n\n";
    
    // Test environment methods
    echo "3. Testing Environment methods...\n";
    echo "   isDevelopment(): " . (Environment::isDevelopment() ? 'true' : 'false') . "\n";
    echo "   isProduction(): " . (Environment::isProduction() ? 'true' : 'false') . "\n";
    echo "   isTesting(): " . (Environment::isTesting() ? 'true' : 'false') . "\n\n";
    
    // Test ConfigManager loading
    echo "4. Loading ConfigManager...\n";
    ConfigManager::load();
    echo "   ✓ ConfigManager loaded successfully\n\n";
    
    // Test getting config values
    echo "5. Testing ConfigManager::get()...\n";
    $appConfig = ConfigManager::get('app.name', 'N/A');
    echo "   app.name: {$appConfig}\n";
    $dbHost = ConfigManager::get('database.connections.mysql.host', 'N/A');
    echo "   database.connections.mysql.host: {$dbHost}\n\n";
    
    echo "✅ All tests passed! Config classes are working correctly.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . "\n";
    echo "   Line: " . $e->getLine() . "\n";
    exit(1);
}
