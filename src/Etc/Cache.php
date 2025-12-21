<?php
/**
 * Cache.php - Simple File-Based Cache System
 * 
 * This class provides a lightweight file-based caching mechanism for upMVC:
 * - Key-value storage with TTL (Time To Live)
 * - Automatic expiration handling
 * - MD5 key hashing for safe filenames
 * - Integration with upMVC\Config
 * - Optional cache disable via config
 * 
 * Features:
 * - Automatic cache directory creation
 * - PHP serialize/unserialize for data storage
 * - File locking for write safety
 * - Expired cache auto-cleanup on read
 * - Bulk cache clearing
 * 
 * Configuration:
 * - cache.enabled: Enable/disable caching (default: false)
 * - cache.ttl: Default TTL in seconds (default: 3600)
 * 
 * Cache File Format:
 * Each cache entry is stored as serialized array:
 * ['value' => mixed, 'expires' => timestamp]
 * 
 * Security Note:
 * Cache directory should be outside web root or protected by .htaccess.
 * 
 * @package upMVC
 * @author BitsHost
 * @copyright 2025 BitsHost
 * @license MIT License
 * @link https://bitshost.biz/
 * @created October 21, 2025
 */

namespace App\Etc;

class Cache
{
    // ========================================
    // Properties
    // ========================================
    
    /**
     * Cache directory path
     * 
     * All cache files are stored here with .cache extension.
     * Keys are MD5 hashed for safe filenames.
     * 
     * @var string
     */
    private static $cacheDir = 'cache/';
    
    // ========================================
    // Read Operations
    // ========================================
    
    /**
     * Get cached value by key
     * 
     * Returns default value if:
     * - Caching is disabled
     * - Key doesn't exist
     * - Cache entry has expired
     * 
     * Expired entries are automatically deleted.
     * 
     * @param string $key Cache key
     * @param mixed $default Default value if not found (default: null)
     * @return mixed Cached value or default
     * 
     * @example
     * // Get cached user data
     * $user = Cache::get('user_123', []);
     * 
     * @example
     * // With custom default
     * $settings = Cache::get('app_settings', ['theme' => 'default']);
     */
    public static function get(string $key, $default = null)
    {
        // Return default if caching is disabled
        if (!Config::get('cache.enabled', false)) {
            return $default;
        }
        
        $file = self::getCacheFile($key);
        
        // Return default if cache file doesn't exist
        if (!file_exists($file)) {
            return $default;
        }
        
        // Unserialize cached data
        $data = unserialize(file_get_contents($file));
        
        // Check if cache has expired
        if ($data['expires'] < time()) {
            unlink($file); // Auto-cleanup expired cache
            return $default;
        }
        
        return $data['value'];
    }
    
    // ========================================
    // Write Operations
    // ========================================
    
    /**
     * Set cached value with TTL
     * 
     * Stores value with expiration timestamp.
     * Returns false if caching is disabled.
     * 
     * @param string $key Cache key
     * @param mixed $value Value to cache (must be serializable)
     * @param int|null $ttl Time to live in seconds (null = use config default)
     * @return bool True on success, false if caching disabled or write failed
     * 
     * @example
     * // Cache for 1 hour (3600 seconds)
     * Cache::set('user_123', $userData, 3600);
     * 
     * @example
     * // Cache with config default TTL
     * Cache::set('products', $products);
     * 
     * @example
     * // Cache for 5 minutes
     * Cache::set('api_response', $data, 300);
     */
    public static function set(string $key, $value, int $ttl = null): bool
    {
        // Don't cache if disabled
        if (!Config::get('cache.enabled', false)) {
            return false;
        }
        
        // Ensure cache directory exists
        if (!is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0755, true);
        }
        
        // Use config TTL if not specified
        $ttl = $ttl ?? Config::get('cache.ttl', 3600);
        $file = self::getCacheFile($key);
        
        // Build cache data with expiration
        $data = [
            'value' => $value,
            'expires' => time() + $ttl
        ];
        
        // Write with file locking
        return file_put_contents($file, serialize($data), LOCK_EX) !== false;
    }
    
    // ========================================
    // Delete Operations
    // ========================================
    
    /**
     * Delete cached value by key
     * 
     * Returns true even if key doesn't exist (idempotent).
     * 
     * @param string $key Cache key to delete
     * @return bool True on success or if key doesn't exist
     * 
     * @example
     * // Invalidate user cache
     * Cache::delete('user_123');
     */
    public static function delete(string $key): bool
    {
        $file = self::getCacheFile($key);
        return file_exists($file) ? unlink($file) : true;
    }
    
    /**
     * Clear all cached values
     * 
     * Deletes all .cache files in cache directory.
     * Safe to call even if directory doesn't exist.
     * 
     * @return bool Always returns true
     * 
     * @example
     * // Clear all cache on deployment
     * Cache::clear();
     * 
     * @example
     * // Clear cache in admin action
     * if ($_POST['action'] === 'clear_cache') {
     *     Cache::clear();
     *     echo "Cache cleared!";
     * }
     */
    public static function clear(): bool
    {
        // Return early if directory doesn't exist
        if (!is_dir(self::$cacheDir)) {
            return true;
        }
        
        // Delete all .cache files
        $files = glob(self::$cacheDir . '*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
        
        return true;
    }
    
    // ========================================
    // Helper Methods
    // ========================================
    
    /**
     * Get cache file path for key
     * 
     * Uses MD5 hash to create safe filenames from any key.
     * All cache files use .cache extension.
     * 
     * @param string $key Cache key
     * @return string Full path to cache file
     */
    private static function getCacheFile(string $key): string
    {
        return self::$cacheDir . md5($key) . '.cache';
    }
}





