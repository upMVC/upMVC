<?php
/**
 * CacheManager.php - Multi-Store Cache Management System
 * 
 * This class provides advanced caching capabilities for upMVC:
 * - Multiple cache store support (file, array, etc.)
 * - Store-specific configuration
 * - Dynamic store switching
 * - Cache tagging for group invalidation
 * - Remember pattern (lazy loading)
 * - Magic method forwarding to default store
 * 
 * Features:
 * - Singleton pattern per store (lazy instantiation)
 * - ConfigManager integration for store configuration
 * - FileCache and ArrayCache implementations
 * - Tagged cache for selective invalidation
 * - Remember/RememberForever helpers
 * 
 * Store Configuration:
 * cache.stores.file.driver = 'file'
 * cache.stores.file.path = 'cache/'
 * cache.ttl = 3600 (default TTL)
 * 
 * Architecture:
 * - CacheManager: Main facade with store management
 * - CacheInterface: Contract for all cache drivers
 * - FileCache: Persistent file-based storage
 * - ArrayCache: In-memory storage (testing/development)
 * - TaggedCache: Wrapper for tagged cache operations
 * 
 * @package upMVC\Cache
 * @author BitsHost
 * @copyright 2025 BitsHost
 * @license MIT License
 * @link https://bitshost.biz/
 * @created October 11, 2025
 */

namespace upMVC\Cache;

use upMVC\Cache\CacheInterface;
use upMVC\Cache\FileCache;
use upMVC\Config\ConfigManager;
use upMVC\Exceptions\ConfigurationException;

class CacheManager
{
    // ========================================
    // Properties
    // ========================================
    
    /**
     * Cached store instances (singleton per store)
     * 
     * Stores are lazy-loaded on first access.
     * 
     * @var array<string, CacheInterface>
     */
    private static array $stores = [];

    /**
     * Default cache store name
     * 
     * Used when no store is specified in method calls.
     * 
     * @var string
     */
    private static string $defaultStore = 'file';

    // ========================================
    // Store Management
    // ========================================

    /**
     * Get a cache store instance
     * 
     * Returns cached instance if already created, otherwise creates new one.
     * Uses default store if none specified.
     *
     * @param string|null $store Store name (null = default store)
     * @return CacheInterface Cache store instance
     * @throws ConfigurationException If store not configured
     * 
     * @example
     * // Get default store (file)
     * $cache = CacheManager::store();
     * 
     * @example
     * // Get specific store
     * $arrayCache = CacheManager::store('array');
     */
    public static function store(?string $store = null): CacheInterface
    {
        $store = $store ?? self::$defaultStore;

        // Lazy instantiation - create store if not exists
        if (!isset(self::$stores[$store])) {
            self::$stores[$store] = self::createStore($store);
        }

        return self::$stores[$store];
    }

    /**
     * Create a cache store instance from configuration
     * 
     * Factory method that instantiates cache drivers based on config.
     *
     * @param string $store Store name from config
     * @return CacheInterface Cache driver instance
     * @throws ConfigurationException If store not configured or driver not supported
     */
    private static function createStore(string $store): CacheInterface
    {
        // Load store configuration
        $config = ConfigManager::get("cache.stores.{$store}");

        if (!$config) {
            throw new ConfigurationException("Cache store [{$store}] is not configured.");
        }

        $driver = $config['driver'] ?? 'file';

        // Instantiate driver based on type
        switch ($driver) {
            case 'file':
                return new FileCache(
                    $config['path'] ?? null,
                    ConfigManager::get('cache.ttl', 3600)
                );

            case 'array':
                return new ArrayCache();

            default:
                throw new ConfigurationException("Cache driver [{$driver}] is not supported.");
        }
    }

    /**
     * Set the default cache store
     * 
     * Changes which store is used when no store is specified.
     *
     * @param string $store Store name to set as default
     * @return void
     * 
     * @example
     * // Use array cache for testing
     * CacheManager::setDefaultStore('array');
     */
    public static function setDefaultStore(string $store): void
    {
        self::$defaultStore = $store;
    }

    // ========================================
    // Magic Methods
    // ========================================

    /**
     * Dynamically call methods on the default cache store
     * 
     * Allows calling cache methods directly on CacheManager:
     * CacheManager::get($key) -> CacheManager::store()->get($key)
     *
     * @param string $method Method name to call
     * @param array $parameters Method parameters
     * @return mixed Method return value
     * 
     * @example
     * // These are equivalent:
     * CacheManager::get('key');
     * CacheManager::store()->get('key');
     * 
     * @example
     * // Direct cache operations
     * CacheManager::put('user_123', $data, 3600);
     * CacheManager::forget('old_key');
     */
    public static function __callStatic(string $method, array $parameters)
    {
        // Forward to default store
        return self::store()->{$method}(...$parameters);
    }

    // ========================================
    // Bulk Operations
    // ========================================

    /**
     * Clear all instantiated cache stores
     * 
     * Flushes every store that has been created.
     * Does not affect stores that haven't been instantiated yet.
     *
     * @return void
     * 
     * @example
     * // Clear all caches on deployment
     * CacheManager::clearAll();
     */
    public static function clearAll(): void
    {
        // Flush all instantiated stores
        foreach (self::$stores as $store) {
            $store->flush();
        }
    }

    // ========================================
    // Remember Pattern
    // ========================================

    /**
     * Remember a value in cache (lazy loading pattern)
     * 
     * Returns cached value if exists, otherwise calls callback,
     * stores result, and returns it.
     * 
     * This is the "cache-aside" pattern.
     *
     * @param string $key Cache key
     * @param callable $callback Callback to generate value if not cached
     * @param int|null $ttl Time to live in seconds (null = forever)
     * @param string|null $store Store name (null = default)
     * @return mixed Cached or generated value
     * 
     * @example
     * // Lazy load expensive data
     * $users = CacheManager::remember('all_users', function() {
     *     return Database::query('SELECT * FROM users')->fetchAll();
     * }, 3600);
     * 
     * @example
     * // With custom store
     * $config = CacheManager::remember('app_config', fn() => loadConfig(), 7200, 'file');
     */
    public static function remember(string $key, callable $callback, ?int $ttl = null, ?string $store = null)
    {
        $cache = self::store($store);
        
        // Try to get cached value
        $value = $cache->get($key);
        
        // Return cached value if exists
        if ($value !== null) {
            return $value;
        }

        // Generate value from callback
        $value = $callback();
        
        // Store for future use
        $cache->put($key, $value, $ttl);

        return $value;
    }

    /**
     * Remember a value in cache forever
     * 
     * Same as remember() but with no expiration (null TTL).
     *
     * @param string $key Cache key
     * @param callable $callback Callback to generate value
     * @param string|null $store Store name (null = default)
     * @return mixed Cached or generated value
     * 
     * @example
     * // Cache application settings permanently
     * $settings = CacheManager::rememberForever('app_settings', function() {
     *     return loadSettingsFromDatabase();
     * });
     */
    public static function rememberForever(string $key, callable $callback, ?string $store = null)
    {
        return self::remember($key, $callback, null, $store);
    }

    // ========================================
    // Tagged Cache
    // ========================================

    /**
     * Get tagged cache instance for group invalidation
     * 
     * Allows caching items with tags for selective invalidation.
     * All items with same tag can be cleared together.
     *
     * @param array $tags Tag names
     * @return TaggedCache Tagged cache wrapper
     * 
     * @example
     * // Cache with tags
     * CacheManager::tags(['users', 'admin'])->put('admin_users', $data, 3600);
     * 
     * @example
     * // Invalidate all items with 'users' tag
     * CacheManager::tags(['users'])->flush();
     */
    public static function tags(array $tags): TaggedCache
    {
        return new TaggedCache(self::store(), $tags);
    }
}

// ========================================
// ArrayCache Implementation
// ========================================

/**
 * ArrayCache - In-Memory Cache Driver
 * 
 * Simple in-memory cache implementation for testing/development.
 * Data is lost when process ends.
 * 
 * Features:
 * - Fast (no disk I/O)
 * - TTL support with timestamp checking
 * - Increment/decrement operations
 * - Automatic expiration on read
 * 
 * Use Cases:
 * - Unit testing
 * - Development environment
 * - Single-request caching
 * 
 * Limitations:
 * - Not persistent across requests
 * - Not shared between processes
 * - Memory usage grows indefinitely until flush
 * 
 * @package upMVC\Cache
 */
class ArrayCache implements CacheInterface
{
    /**
     * In-memory cache storage
     * 
     * Structure: ['key' => ['value' => mixed, 'expires' => int|null]]
     * 
     * @var array
     */
    private static array $cache = [];

    /**
     * Get cached value by key
     * 
     * @param string $key Cache key
     * @param mixed $default Default value if not found
     * @return mixed Cached value or default
     */
    public function get(string $key, $default = null)
    {
        $data = self::$cache[$key] ?? null;
        
        if ($data === null) {
            return $default;
        }

        // Check expiration
        if ($data['expires'] !== null && $data['expires'] < time()) {
            unset(self::$cache[$key]); // Auto-cleanup expired
            return $default;
        }

        return $data['value'];
    }

    /**
     * Store value in cache with TTL
     * 
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int|null $ttl Time to live in seconds (null = forever)
     * @return bool Always true
     */
    public function put(string $key, $value, ?int $ttl = null): bool
    {
        self::$cache[$key] = [
            'value' => $value,
            'expires' => $ttl > 0 ? time() + $ttl : null
        ];
        return true;
    }

    /**
     * Add value only if key doesn't exist
     * 
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int|null $ttl Time to live in seconds
     * @return bool True if added, false if key already exists
     */
    public function add(string $key, $value, ?int $ttl = null): bool
    {
        if ($this->has($key)) {
            return false; // Key already exists
        }
        return $this->put($key, $value, $ttl);
    }

    /**
     * Delete cached value by key
     * 
     * @param string $key Cache key
     * @return bool Always true
     */
    public function forget(string $key): bool
    {
        unset(self::$cache[$key]);
        return true;
    }

    /**
     * Clear all cached values
     * 
     * @return bool Always true
     */
    public function flush(): bool
    {
        self::$cache = [];
        return true;
    }

    /**
     * Check if key exists in cache
     * 
     * @param string $key Cache key
     * @return bool True if exists and not expired
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * Increment numeric cache value
     * 
     * @param string $key Cache key
     * @param int $value Amount to increment (default: 1)
     * @return int New value after increment
     */
    public function increment(string $key, int $value = 1): int
    {
        $current = (int) $this->get($key, 0);
        $new = $current + $value;
        $this->put($key, $new);
        return $new;
    }

    /**
     * Decrement numeric cache value
     * 
     * @param string $key Cache key
     * @param int $value Amount to decrement (default: 1)
     * @return int New value after decrement
     */
    public function decrement(string $key, int $value = 1): int
    {
        return $this->increment($key, -$value);
    }
}

// ========================================
// TaggedCache Implementation
// ========================================

/**
 * TaggedCache - Cache with Tag-Based Invalidation
 * 
 * Wrapper around CacheInterface that adds tagging support.
 * Allows grouping cache entries and invalidating them by tag.
 * 
 * How It Works:
 * - Each tag stores a list of associated keys
 * - Tag lists are stored in cache as "tag:{name}"
 * - Flushing a tag deletes all associated keys
 * 
 * Use Cases:
 * - Invalidate all user-related cache
 * - Clear product cache on inventory update
 * - Group related cache entries
 * 
 * @package upMVC\Cache
 */
class TaggedCache
{
    /**
     * Underlying cache store
     * 
     * @var CacheInterface
     */
    private CacheInterface $cache;
    
    /**
     * Tag names for this instance
     * 
     * @var array
     */
    private array $tags;

    /**
     * Constructor
     * 
     * @param CacheInterface $cache Cache store to wrap
     * @param array $tags Tag names
     */
    public function __construct(CacheInterface $cache, array $tags)
    {
        $this->cache = $cache;
        $this->tags = $tags;
    }

    /**
     * Store value in cache with tags
     * 
     * Stores the value and updates tag references.
     * 
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int|null $ttl Time to live in seconds
     * @return bool True on success
     */
    public function put(string $key, $value, ?int $ttl = null): bool
    {
        // Store the actual cache entry
        $result = $this->cache->put($key, $value, $ttl);
        
        // Update tag references
        foreach ($this->tags as $tag) {
            $taggedKeys = $this->cache->get("tag:{$tag}", []);
            $taggedKeys[] = $key;
            $this->cache->put("tag:{$tag}", array_unique($taggedKeys));
        }

        return $result;
    }

    /**
     * Flush all cache entries with these tags
     * 
     * Deletes all keys associated with any of the tags.
     * Also removes the tag reference lists.
     * 
     * @return bool True on success
     */
    public function flush(): bool
    {
        // For each tag, delete all associated keys
        foreach ($this->tags as $tag) {
            $taggedKeys = $this->cache->get("tag:{$tag}", []);
            foreach ($taggedKeys as $key) {
                $this->cache->forget($key);
            }
            // Remove tag reference list
            $this->cache->forget("tag:{$tag}");
        }
        return true;
    }

    /**
     * Forward other method calls to underlying cache
     * 
     * @param string $method Method name
     * @param array $parameters Method parameters
     * @return mixed Method return value
     */
    public function __call(string $method, array $parameters)
    {
        return $this->cache->{$method}(...$parameters);
    }
}