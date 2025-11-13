<?php
/**
 * Config.php - Application Configuration Management
 * 
 * This class provides centralized configuration management for upMVC:
 * - Loads configuration from .env file (via Environment class)
 * - Provides fallback values for safety
 * - Manages session, cache, security settings
 * - Handles URL/path processing for routing
 * 
 * Configuration Priority:
 * 1. .env file (highest priority)
 * 2. ConfigManager settings
 * 3. Fallback array in this class (lowest priority)
 * 
 * @package upMVC
 * @author BitsHost
 * @copyright 2023 BitsHost
 * @license MIT License
 * @link https://bitshost.biz/
 * @created Tue Oct 31 2023
 */

namespace App\Etc;

use App\Etc\Config\Environment;

class Config
{
    // ========================================
    // Configuration Arrays
    // ========================================
    
    /**
     * Fallback configuration values for path and domain
     * 
     * Used only if .env file is missing or values are not set.
     * In normal operation, .env values are always used.
     * 
     * IMPORTANT: Change these values according to your setup!
     * - site_path: Should be empty '' if in root, or '/folder' if in subdirectory
     * - domain_name: Your domain URL without trailing slash
     * 
     * @var array
     */
    private static $fallbacks = [
        'site_path' => '/upMVC',
        'domain_name' => 'http://localhost/',
    ];
    
    /**
     * Static configuration array
     * 
     * These values can also be overridden via .env or ConfigManager.
     * These are fallback defaults for application settings.
     * 
     * IMPORTANT: For production, use .env file to override these values!
     * 
     * @var array
     */
    private static $config = [
        'debug' => true,              // Set to false in production
        'timezone' => 'UTC',          // Change to your timezone
        'session' => [
            'name' => 'UPMVC_SESSION',
            'lifetime' => 3600,       // 1 hour
            'secure' => false,        // Set true if using HTTPS
            'httponly' => true
        ],
        'cache' => [
            'enabled' => false,       // Enable in production
            'driver' => 'file',
            'ttl' => 3600            // Cache lifetime in seconds
        ],
        'security' => [
            'csrf_protection' => true,
            'rate_limit' => 100      // Requests per minute
        ]
    ];
    
    // Legacy constants - now loaded from .env
    // Use self::getSitePath() and self::getDomainName() instead
    // public const SITE_PATH = '/upMVC';  // Commented out - now using .env
    // public const DOMAIN_NAME = 'http://localhost';  // Commented out - now using .env

    // ========================================
    // Configuration Getters
    // ========================================
    
    /**
     * Get configuration value using dot notation
     * 
     * Used internally by initConfig() to read from $config array.
     * Supports nested keys like 'session.lifetime' or 'cache.enabled'.
     * 
     * @param string $key Configuration key (supports dot notation)
     * @param mixed $default Default value if key not found
     * @return mixed Configuration value or default
     * 
     * @example
     * Config::get('debug', false);           // Returns debug value
     * Config::get('session.lifetime', 3600); // Returns session lifetime
     */
    public static function get(string $key, $default = null)
    {
        $parts = explode('.', $key);
        $config = self::$config;
        
        foreach ($parts as $part) {
            if (isset($config[$part])) {
                $config = $config[$part];
            } else {
                return $default;
            }
        }
        
        return $config;
    }

    /**
     * Get SITE_PATH from .env or fallback
     * 
     * Returns the site path (e.g., '/upMVC' or '' for root).
     * Fallback rarely used since .env is always loaded in bootstrapApplication().
     *
     * @return string The site path
     */
    public static function getSitePath(): string
    {
        return Environment::get('SITE_PATH', self::$fallbacks['site_path']);
    }

    /**
     * Get DOMAIN_NAME from .env or fallback
     * 
     * Returns the domain name (e.g., 'http://localhost' or 'https://example.com').
     * Fallback rarely used since .env is always loaded in bootstrapApplication().
     *
     * @return string The domain name without trailing slash
     */
    public static function getDomainName(): string
    {
        return Environment::get('DOMAIN_NAME', self::$fallbacks['domain_name']);
    }

    // ========================================
    // Request Processing
    // ========================================

    /**
     * Get the requested route from the current request URI
     *
     * This method extracts the clean route from the request URI by:
     * 1. Initializing configuration
     * 2. Removing the site path prefix from the URI
     * 3. Removing any query string parameters from the URI
     *
     * The resulting route is the clean, path-only representation of the
     * requested resource, which can be used for routing and processing.
     *
     * @param string $reqURI The full request URI
     * @return string The extracted clean route
     * 
     * @example
     * // If SITE_PATH = '/upMVC' and URI = '/upMVC/users?id=5'
     * // Returns: '/users'
     */
    public function getReqRoute($reqURI)
    {
        $this->initConfig();
        
        $urlWithoutSitePath = $this->cleanUrlSitePath(self::getSitePath(), $reqURI);
        return $this->cleanUrlQuestionMark($urlWithoutSitePath);
    }

    // ========================================
    // Initialization
    // ========================================

    /**
     * Initialize the application configuration
     *
     * This method sets up the necessary configuration for the application:
     * 1. Sets timezone from config
     * 2. Configures error reporting based on debug mode
     * 3. Defines application directory and base URL constants
     * 4. Configures and starts PHP session with security settings
     * 5. Registers error handler
     *
     * This initialization should be performed before any other application
     * logic is executed to ensure a consistent configuration environment.
     *
     * @return void
     */
    private function initConfig(): void
    {
        // Set timezone
        date_default_timezone_set(self::get('timezone', 'UTC'));
        
        // Error reporting based on debug mode
        if (self::get('debug', false)) {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        } else {
            error_reporting(0);
            ini_set('display_errors', 0);
        }

        // Define application directory and base URL
        define('THIS_DIR', str_replace('\\', '/', dirname(__FILE__, 2)));
        define('BASE_URL', self::getDomainName() . self::getSitePath());
        define('SITEPATH', self::getSitePath());

        // Enhanced session configuration with security
        $sessionConfig = self::get('session', []);
        if (isset($sessionConfig['name'])) {
            session_name($sessionConfig['name']);
        }
        
        session_set_cookie_params([
            'lifetime' => $sessionConfig['lifetime'] ?? 3600,
            'secure' => $sessionConfig['secure'] ?? false,
            'httponly' => $sessionConfig['httponly'] ?? true,
            'samesite' => 'Strict'
        ]);
        
        session_start();
        
        ErrorHandler::register();
    }

    // ========================================
    // Helper Methods
    // ========================================

    /**
     * Remove query string from URL
     * 
     * Extracts the path component from a URL, removing query parameters.
     *
     * @param string $urlWithoutSitePath URL to clean
     * @return string Clean path without query string
     * 
     * @example
     * // Input: '/users?id=5&name=john'
     * // Returns: '/users'
     */
    private function cleanUrlQuestionMark(string $urlWithoutSitePath): string
    {
        $parts = parse_url($urlWithoutSitePath);
        return $parts['path'] ?? $urlWithoutSitePath;
    }

    /**
     * Remove site path prefix from URL
     * 
     * Strips the site path from the beginning of a URL to get the clean route.
     *
     * @param string $sitePath The site path to remove (e.g., '/upMVC')
     * @param string $reqUrl The full request URL
     * @return string URL without the site path prefix
     * 
     * @example
     * // If sitePath = '/upMVC' and reqUrl = '/upMVC/users'
     * // Returns: '/users'
     */
    private function cleanUrlSitePath(string $sitePath, string $reqUrl): string
    {
        return str_replace($sitePath, '', $reqUrl);
    }
}





