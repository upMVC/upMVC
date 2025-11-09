<?php
/**
 * DataHelper.php - Session, Config, and Data Access
 * 
 * Handles session, config, environment variables, and request data.
 * 
 * @package upMVC\Helpers
 * @author BitsHost
 * @copyright 2023 BitsHost
 * @license MIT License
 */

namespace upMVC\Helpers;

use upMVC\Config\ConfigManager;
use upMVC\Config\Environment;

class DataHelper
{
    /**
     * Get or set session value
     */
    public static function session(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $_SESSION;
        }
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * Get configuration value using dot notation
     */
    public static function config(string $key, $default = null)
    {
        return ConfigManager::get($key, $default);
    }
    
    /**
     * Get environment variable value
     */
    public static function env(string $key, $default = null)
    {
        return Environment::get($key, $default);
    }
    
    /**
     * Get request input value
     */
    public static function request(?string $key = null, $default = null)
    {
        $input = array_merge($_GET, $_POST);
        
        if ($key === null) {
            return $input;
        }
        return $input[$key] ?? $default;
    }
}
