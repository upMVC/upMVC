<?php
// Simple test for web environment
$_SERVER['REQUEST_URI'] = '/upMVC/test';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

require_once 'vendor/autoload.php';

use upMVC\Start;

echo "Testing upMVC with simulated web environment...\n";

try {
    $fireUpMVC = new Start();
    $fireUpMVC->upMVC();
    echo "✅ upMVC started successfully!\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}