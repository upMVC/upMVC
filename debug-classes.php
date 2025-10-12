<?php
require_once 'vendor/autoload.php';

echo "Testing class availability:\n";

$classes = [
    'upMVC\\Start',
    'upMVC\\Config',
    'upMVC\\Router', 
    'upMVC\\Routes',
    'upMVC\\Database',
    'upMVC\\Config\\ConfigManager',
    'upMVC\\Config\\Environment',
    'upMVC\\Container\\Container',
    'upMVC\\Exceptions\\ErrorHandler',
    'upMVC\\Middleware\\AuthMiddleware',
    'upMVC\\Middleware\\LoggingMiddleware',
    'upMVC\\Middleware\\CorsMiddleware'
];

foreach ($classes as $class) {
    if (class_exists($class)) {
        echo "✅ $class - EXISTS\n";
    } else {
        echo "❌ $class - MISSING\n";
    }
}

echo "\nChecking if files exist:\n";

$files = [
    'etc/Config.php',
    'etc/Router.php', 
    'etc/Routes.php',
    'etc/Database.php',
    'etc/Config/ConfigManager.php',
    'etc/Config/Environment.php',
    'etc/Container/Container.php',
    'etc/Exceptions/ErrorHandler.php',
    'etc/Middleware/AuthMiddleware.php',
    'etc/Middleware/LoggingMiddleware.php',
    'etc/Middleware/CorsMiddleware.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ $file - EXISTS\n";
    } else {
        echo "❌ $file - MISSING\n";
    }
}