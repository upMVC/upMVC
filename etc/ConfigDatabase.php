<?php
/**
 * ConfigDatabase.php - Database Configuration Management
 * 
 * This class provides database configuration for upMVC:
 * - Stores database connection credentials
 * - Provides dot notation access to configuration values
 * - Fallback defaults for missing keys
 * 
 * Configuration Keys:
 * - db.host: Database server address (default: 127.0.0.1)
 * - db.name: Database name (default: test)
 * - db.user: Database username (default: root)
 * - db.pass: Database password (default: empty)
 * 
 * Usage:
 * $host = ConfigDatabase::get('db.host');
 * $name = ConfigDatabase::get('db.name', 'defaultDB');
 * 
 * IMPORTANT: Update these values for your environment or consider
 * loading from .env file for better security.
 * 
 * @package upMVC
 * @author BitsHost
 * @copyright 2023 BitsHost
 * @license MIT License
 * @link https://bitshost.biz/
 * @created Tue Oct 31 2023
 */

namespace upMVC;

class ConfigDatabase
{
    // ========================================
    // Configuration
    // ========================================
    
    /**
     * Database configuration array (FALLBACK ONLY)
     * 
     * ⚠️ IMPORTANT: These are FALLBACK values for development only!
     * 
     * HYBRID CONFIGURATION PRIORITY:
     * 1. .env file (PREFERRED) - Database.php checks .env first
     * 2. This file (FALLBACK) - Used only if .env values not found
     * 
     * Why Hybrid?
     * - Production: Use .env (secure, not in Git)
     * - Development: Use this file (convenient defaults)
     * - Testing: Easy to alter these values to verify .env is working
     * 
     * Current Status:
     * - Values intentionally altered (testa, roota) to test .env priority
     * - If you see real database connection, .env is working! ✅
     * - If connection fails, .env might not be loaded properly
     * 
     * Security Best Practices:
     * - Production: ALWAYS use .env for real credentials
     * - Development: Keep dummy/local values here
     * - Never commit real passwords to version control
     * 
     * @var array
     */
    private static $config = [
        'db' => [
            'host' => '127.0.0.1',    // Database server (FALLBACK - .env preferred)
            'name' => 'testa',        // Database name (FALLBACK - altered for testing)
            'user' => 'roota',        // Database username (FALLBACK - altered for testing)
            'pass' => '',             // Database password (FALLBACK)
        ],
    ];

    // ========================================
    // Configuration Access
    // ========================================

    /**
     * Get configuration value using dot notation
     * 
     * Retrieves configuration values using dot notation for nested keys.
     * Returns default value if key is not found.
     * 
     * @param string $key Configuration key (supports dot notation like 'db.host')
     * @param mixed $default Default value to return if key not found
     * @return mixed Configuration value or default
     * 
     * @example
     * // Get database host
     * $host = ConfigDatabase::get('db.host');  // Returns '127.0.0.1'
     * 
     * @example
     * // Get database name with default
     * $name = ConfigDatabase::get('db.name', 'myapp');  // Returns 'test'
     * 
     * @example
     * // Get missing key with default
     * $port = ConfigDatabase::get('db.port', 3306);  // Returns 3306
     */
    public static function get(string $key, $default = null)
    {
        $parts = explode('.', $key);
        $currentConfig = self::$config;
        
        // Navigate through nested configuration
        foreach ($parts as $part) {
            if (isset($currentConfig[$part])) {
                $currentConfig = $currentConfig[$part];
            } else {
                return $default;
            }
        }
        
        return $currentConfig;
    }
}
