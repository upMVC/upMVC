<?php
/**
 * Cache.php - Simple File-Based Cache System
 *
 * @deprecated Use App\Etc\Cache\CacheManager / FileCache instead. This legacy
 *             class remains for old call sites; Prefer storage/cache, not CWD.
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

/**
 * @deprecated Use App\Etc\Cache\CacheManager instead
 */
class Cache
{
    // ========================================
    // Properties
    // ========================================
    
    /**
     * Cache directory path (resolved under storage/cache — never public/)
     *
     * @var string|null
     */
    private static $cacheDir = null;
    
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
        
        $raw = file_get_contents($file);
        if ($raw === false) {
            return $default;
        }

        $data = unserialize($raw, ['allowed_classes' => false]);
        if (!is_array($data) || !array_key_exists('expires', $data) || !array_key_exists('value', $data)) {
            return $default;
        }

        if ($data['expires'] < time()) {
            unlink($file);
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
    public static function set(string $key, $value, ?int $ttl = null): bool
    {
        // Don't cache if disabled
        if (!Config::get('cache.enabled', false)) {
            return false;
        }
        
        $dir = self::cacheDirectory();
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

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
        $dir = self::cacheDirectory();
        if (!is_dir($dir)) {
            return true;
        }

        foreach (glob($dir . '*.cache') ?: [] as $file) {
            unlink($file);
        }

        return true;
    }

    // ========================================
    // Helper Methods
    // ========================================

    private static function cacheDirectory(): string
    {
        if (self::$cacheDir !== null) {
            return self::$cacheDir;
        }

        if (class_exists(Application::class)) {
            self::$cacheDir = rtrim(Application::getInstance()->path('storage/cache'), '\\/') . DIRECTORY_SEPARATOR;
        } else {
            self::$cacheDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
        }

        return self::$cacheDir;
    }

    /**
     * Get cache file path for key
     */
    private static function getCacheFile(string $key): string
    {
        return self::cacheDirectory() . md5($key) . '.cache';
    }
}





