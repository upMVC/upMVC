<?php
/*
 *   Created on October 11, 2025
 *   Copyright (c) 2025 BitsHost
 *   All rights reserved.
 *
 *   Enhanced upMVC - Cache Manager
 */

namespace upMVC\Cache;

use upMVC\Cache\CacheInterface;
use upMVC\Cache\FileCache;
use upMVC\Config\ConfigManager;
use upMVC\Exceptions\ConfigurationException;

/**
 * CacheManager
 * 
 * Manages different cache stores and provides caching functionality
 */
class CacheManager
{
    /**
     * @var array
     */
    private static array $stores = [];

    /**
     * @var string
     */
    private static string $defaultStore = 'file';

    /**
     * Get a cache store instance
     *
     * @param string|null $store
     * @return CacheInterface
     * @throws ConfigurationException
     */
    public static function store(?string $store = null): CacheInterface
    {
        $store = $store ?? self::$defaultStore;

        if (!isset(self::$stores[$store])) {
            self::$stores[$store] = self::createStore($store);
        }

        return self::$stores[$store];
    }

    /**
     * Create a cache store instance
     *
     * @param string $store
     * @return CacheInterface
     * @throws ConfigurationException
     */
    private static function createStore(string $store): CacheInterface
    {
        $config = ConfigManager::get("cache.stores.{$store}");

        if (!$config) {
            throw new ConfigurationException("Cache store [{$store}] is not configured.");
        }

        $driver = $config['driver'] ?? 'file';

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
     * Dynamically call the default cache store
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public static function __callStatic(string $method, array $parameters)
    {
        return self::store()->{$method}(...$parameters);
    }

    /**
     * Set the default cache store
     *
     * @param string $store
     * @return void
     */
    public static function setDefaultStore(string $store): void
    {
        self::$defaultStore = $store;
    }

    /**
     * Clear all cache stores
     *
     * @return void
     */
    public static function clearAll(): void
    {
        foreach (self::$stores as $store) {
            $store->flush();
        }
    }

    /**
     * Remember a value in cache
     *
     * @param string $key
     * @param callable $callback
     * @param int|null $ttl
     * @param string|null $store
     * @return mixed
     */
    public static function remember(string $key, callable $callback, ?int $ttl = null, ?string $store = null)
    {
        $cache = self::store($store);
        
        $value = $cache->get($key);
        
        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $cache->put($key, $value, $ttl);

        return $value;
    }

    /**
     * Remember a value in cache forever
     *
     * @param string $key
     * @param callable $callback
     * @param string|null $store
     * @return mixed
     */
    public static function rememberForever(string $key, callable $callback, ?string $store = null)
    {
        return self::remember($key, $callback, null, $store);
    }

    /**
     * Cache tags for cache invalidation
     *
     * @param array $tags
     * @return TaggedCache
     */
    public static function tags(array $tags): TaggedCache
    {
        return new TaggedCache(self::store(), $tags);
    }
}

/**
 * Array Cache Implementation (for testing/development)
 */
class ArrayCache implements CacheInterface
{
    private static array $cache = [];

    public function get(string $key, $default = null)
    {
        $data = self::$cache[$key] ?? null;
        
        if ($data === null) {
            return $default;
        }

        if ($data['expires'] !== null && $data['expires'] < time()) {
            unset(self::$cache[$key]);
            return $default;
        }

        return $data['value'];
    }

    public function put(string $key, $value, ?int $ttl = null): bool
    {
        self::$cache[$key] = [
            'value' => $value,
            'expires' => $ttl > 0 ? time() + $ttl : null
        ];
        return true;
    }

    public function add(string $key, $value, ?int $ttl = null): bool
    {
        if ($this->has($key)) {
            return false;
        }
        return $this->put($key, $value, $ttl);
    }

    public function forget(string $key): bool
    {
        unset(self::$cache[$key]);
        return true;
    }

    public function flush(): bool
    {
        self::$cache = [];
        return true;
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    public function increment(string $key, int $value = 1): int
    {
        $current = (int) $this->get($key, 0);
        $new = $current + $value;
        $this->put($key, $new);
        return $new;
    }

    public function decrement(string $key, int $value = 1): int
    {
        return $this->increment($key, -$value);
    }
}

/**
 * Tagged Cache for cache invalidation
 */
class TaggedCache
{
    private CacheInterface $cache;
    private array $tags;

    public function __construct(CacheInterface $cache, array $tags)
    {
        $this->cache = $cache;
        $this->tags = $tags;
    }

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

    public function flush(): bool
    {
        foreach ($this->tags as $tag) {
            $taggedKeys = $this->cache->get("tag:{$tag}", []);
            foreach ($taggedKeys as $key) {
                $this->cache->forget($key);
            }
            $this->cache->forget("tag:{$tag}");
        }
        return true;
    }

    public function __call(string $method, array $parameters)
    {
        return $this->cache->{$method}(...$parameters);
    }
}