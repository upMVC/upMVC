<?php
/*
 *   Created on October 11, 2025
 *   Copyright (c) 2025 BitsHost
 *   All rights reserved.
 *
 *   Enhanced upMVC - Cache Interface
 */

namespace upMVC\Cache;

/**
 * CacheInterface
 * 
 * Interface for cache implementations
 */
interface CacheInterface
{
    /**
     * Retrieve an item from the cache
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * Store an item in the cache
     *
     * @param string $key
     * @param mixed $value
     * @param int|null $ttl Time to live in seconds
     * @return bool
     */
    public function put(string $key, $value, ?int $ttl = null): bool;

    /**
     * Store an item in the cache if it doesn't exist
     *
     * @param string $key
     * @param mixed $value
     * @param int|null $ttl
     * @return bool
     */
    public function add(string $key, $value, ?int $ttl = null): bool;

    /**
     * Remove an item from the cache
     *
     * @param string $key
     * @return bool
     */
    public function forget(string $key): bool;

    /**
     * Clear all cache
     *
     * @return bool
     */
    public function flush(): bool;

    /**
     * Check if an item exists in cache
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Increment a value
     *
     * @param string $key
     * @param int $value
     * @return int
     */
    public function increment(string $key, int $value = 1): int;

    /**
     * Decrement a value
     *
     * @param string $key
     * @param int $value
     * @return int
     */
    public function decrement(string $key, int $value = 1): int;
}