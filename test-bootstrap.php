<?php
/**
 * Simple bootstrap test
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "Step 1: Loading autoloader...\n";
require_once __DIR__ . '/vendor/autoload.php';
echo "✓ Autoloader loaded\n\n";

echo "Step 2: Testing Environment class...\n";
try {
    $envClass = new ReflectionClass('App\Etc\Config\Environment');
    echo "✓ Environment class found: " . $envClass->getFileName() . "\n\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Step 3: Testing ConfigManager class...\n";
try {
    $configClass = new ReflectionClass('App\Etc\Config\ConfigManager');
    echo "✓ ConfigManager class found: " . $configClass->getFileName() . "\n\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Step 4: Loading Environment...\n";
try {
    \App\Etc\Config\Environment::load();
    echo "✓ Environment loaded\n";
    echo "  DOMAIN_NAME: " . \App\Etc\Config\Environment::get('DOMAIN_NAME') . "\n";
    echo "  SITE_PATH: '" . \App\Etc\Config\Environment::get('SITE_PATH') . "'\n\n";
} catch (Throwable $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "  Trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "✅ All bootstrap tests passed!\n";
