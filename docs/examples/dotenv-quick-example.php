<?php
/**
 * QUICK EXAMPLE: How to Use .env Instead of Config Constants
 * 
 * File: examples/dotenv-usage-example.php
 */

// ============================================
// BEFORE: Hardcoded in Config.php
// ============================================
/*
class Config
{
    public const SITE_PATH = '/upMVC';
    public const DOMAIN_NAME = 'http://localhost';
    public const API_KEY = 'hardcoded_key_123';
}

$sitePath = Config::SITE_PATH;
*/

// ============================================
// AFTER: Using .env
// ============================================

// Step 1: Add to .env file
/*
SITE_PATH=/upMVC
DOMAIN_NAME=http://localhost
API_KEY=your_api_key_here
MY_CUSTOM_SETTING=some_value
*/

// Step 2: Use Environment class
use upMVC\Config\Environment;

// Get values from .env (with fallback defaults)
$sitePath = Environment::get('SITE_PATH', '/default');
$domainName = Environment::get('DOMAIN_NAME', 'http://localhost');
$apiKey = Environment::get('API_KEY');
$customSetting = Environment::get('MY_CUSTOM_SETTING', 'default_value');

// Build URLs
$baseUrl = $domainName . $sitePath;
echo "Base URL: {$baseUrl}\n";

// Or use the new helper methods in Config class
$sitePath = \upMVC\Config::getSitePath();
$domainName = \upMVC\Config::getDomainName();

echo "Site Path: {$sitePath}\n";
echo "Domain: {$domainName}\n";

// ============================================
// EXAMPLE: Module Controller
// ============================================
namespace MyModule;

use upMVC\Config\Environment;

class Controller
{
    private $apiKey;
    
    public function __construct()
    {
        // Load configuration from .env
        $this->apiKey = Environment::get('API_KEY');
    }
    
    public function index()
    {
        // Check if in development
        if (Environment::isDevelopment()) {
            echo "DEBUG MODE ACTIVE\n";
            echo "API Key: " . $this->apiKey . "\n";
        }
        
        // Use site path
        $sitePath = Environment::get('SITE_PATH', '/');
        echo "Current site path: {$sitePath}\n";
    }
}

// ============================================
// REAL EXAMPLE: Database Connection
// ============================================
class MyDatabase
{
    public function connect()
    {
        $host = Environment::get('DB_HOST', 'localhost');
        $port = Environment::get('DB_PORT', '3306');
        $name = Environment::get('DB_NAME', 'mydb');
        $user = Environment::get('DB_USER', 'root');
        $pass = Environment::get('DB_PASS', '');
        
        $dsn = "mysql:host={$host};port={$port};dbname={$name}";
        
        try {
            $pdo = new \PDO($dsn, $user, $pass);
            echo "Connected to database: {$name} on {$host}\n";
            return $pdo;
        } catch (\PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
}

// ============================================
// SUMMARY
// ============================================
/*

✅ WHAT YOU LEARNED:

1. Add settings to .env:
   SITE_PATH=/upMVC
   MY_SETTING=value

2. Access in PHP:
   Environment::get('SITE_PATH', 'default')

3. Use helper methods:
   Config::getSitePath()
   Config::getDomainName()

4. Benefits:
   - No code changes when deploying
   - Different settings per environment
   - Secure (don't commit .env to git)
   - Easy to configure

*/
