<?php
/**
 * Diagnostic script to check routing
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/vendor/autoload.php';

use App\Etc\Config\Environment;
use App\Etc\Config;

echo "<h2>Routing Diagnostic</h2>";

// Load environment
Environment::load();

echo "<h3>1. Environment Variables</h3>";
echo "DOMAIN_NAME: " . Environment::get('DOMAIN_NAME') . "<br>";
echo "SITE_PATH: '" . Environment::get('SITE_PATH') . "'<br>";

echo "<h3>2. Config Values</h3>";
echo "getDomainName(): " . Config::getDomainName() . "<br>";
echo "getSitePath(): '" . Config::getSitePath() . "'<br>";

echo "<h3>3. Request Information</h3>";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "<br>";
echo "PHP_SELF: " . $_SERVER['PHP_SELF'] . "<br>";
echo "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "<br>";

// Simulate route extraction
$config = new Config();
$requestUri = $_SERVER['REQUEST_URI'];
$route = $config->getReqRoute($requestUri);

echo "<h3>4. Extracted Route</h3>";
echo "Full URI: " . $requestUri . "<br>";
echo "Extracted Route: '" . $route . "'<br>";

echo "<h3>5. Expected Behavior</h3>";
echo "For URL: http://localhost/upMVC/<br>";
echo "- REQUEST_URI should be: /upMVC/ or /upMVC<br>";
echo "- After removing SITE_PATH (/upMVC): /<br>";
echo "- This should match the '/' route in Test module<br>";

echo "<h3>6. What to Check</h3>";
echo "1. Is the extracted route '/' or something else?<br>";
echo "2. Does the Test module's Routes.php register '/' route?<br>";
echo "3. Is the router finding the route?<br>";
