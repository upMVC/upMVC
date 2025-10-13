<?php
require_once 'vendor/autoload.php';

// Initialize config properly
$config = new upMVC\Config();
$config->getReqRoute('/');

echo "<h2>Testing Base URL</h2>";

if (defined('BASE_URL')) {
    echo "BASE_URL constant: " . BASE_URL . "<br>";
} else {
    echo "BASE_URL not defined<br>";
}

$controller = new Dashboardexample\Controller();

// Test the getBaseUrl method using reflection
$reflection = new ReflectionClass($controller);
$getBaseUrlMethod = $reflection->getMethod('getBaseUrl');
$getBaseUrlMethod->setAccessible(true);
$baseUrl = $getBaseUrlMethod->invoke($controller);

echo "getBaseUrl() returns: " . $baseUrl . "<br>";
echo "Expected redirect URL: " . $baseUrl . "/dashboardexample<br>";

// Test route registration for the exact path
echo "<h3>Testing route registration for redirect target...</h3>";
$router = new upMVC\Router();
$modulesRoutes = new upMVC\InitModsImproved();
$modulesRoutes->addRoutes($router);

$reflection = new ReflectionClass($router);
$routesProperty = $reflection->getProperty('routes');
$routesProperty->setAccessible(true);
$routes = $routesProperty->getValue($router);

if (isset($routes['/dashboardexample'])) {
    echo "✅ Route /dashboardexample is registered<br>";
} else {
    echo "❌ Route /dashboardexample is NOT registered<br>";
    echo "Available routes starting with /dashboard:<br>";
    foreach ($routes as $route => $config) {
        if (strpos($route, '/dashboard') === 0) {
            echo "- $route<br>";
        }
    }
}