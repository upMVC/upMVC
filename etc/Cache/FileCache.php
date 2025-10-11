<?php
/*
 *   Created on October 11, 2025
 *   Copyright (c) 2025 BitsHost
 *   All rights reserved.
 *
 *   Enhanced upMVC - File Cache Implementation
 */

namespace upMVC\Cache;

use upMVC\Cache\CacheInterface;

/**
 * FileCache
 * 
 * File-based cache implementation
 */
class FileCache implements CacheInterface
{
    /**
     * @var string
     */
    private string $cachePath;

    /**
     * @var int
     */
    private int $defaultTtl;

    public function __construct(string $cachePath = null, int $defaultTtl = 3600)
    {
        $baseDir = defined('THIS_DIR') ? THIS_DIR : dirname(__DIR__, 2);
        $this->cachePath = $cachePath ?? $baseDir . '/storage/cache';
        $this->defaultTtl = $defaultTtl;

        // Create cache directory if it doesn't exist
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }

    /**
     * Get cache file path
     *
     * @param string $key
     * @return string
     */
    private function getCacheFilePath(string $key): string
    {
        $hash = sha1($key);
        $dir = $this->cachePath . '/' . substr($hash, 0, 2);
        
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return $dir . '/' . $hash . '.cache';
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key, $default = null)
    {
        $file = $this->getCacheFilePath($key);

        if (!file_exists($file)) {
            return $default;
        }

        $content = file_get_contents($file);
        if ($content === false) {
            return $default;
        }

        $data = unserialize($content);
        if ($data === false) {
            return $default;
        }

        // Check if expired
        if ($data['expires'] !== null && $data['expires'] < time()) {
            $this->forget($key);
            return $default;
        }

        return $data['value'];
    }

    /**
     * {@inheritdoc}
     */
    public function put(string $key, $value, ?int $ttl = null): bool
    {
        $file = $this->getCacheFilePath($key);
        $ttl = $ttl ?? $this->defaultTtl;

        $data = [
            'value' => $value,
            'expires' => $ttl > 0 ? time() + $ttl : null,
            'created' => time()
        ];

        $content = serialize($data);
        return file_put_contents($file, $content, LOCK_EX) !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function add(string $key, $value, ?int $ttl = null): bool
    {
        if ($this->has($key)) {
            return false;
        }

        return $this->put($key, $value, $ttl);
    }

    /**
     * {@inheritdoc}
     */
    public function forget(string $key): bool
    {
        $file = $this->getCacheFilePath($key);
        
        if (file_exists($file)) {
            return unlink($file);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function flush(): bool
    {
        $success = true;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->cachePath),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'cache') {
                if (!unlink($file->getPathname())) {
                    $success = false;
                }
            }
        }

        return $success;
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function increment(string $key, int $value = 1): int
    {
        $current = (int) $this->get($key, 0);
        $new = $current + $value;
        $this->put($key, $new);
        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function decrement(string $key, int $value = 1): int
    {
        return $this->increment($key, -$value);
    }

    /**
     * Clean expired cache files
     *
     * @return int Number of files cleaned
     */
    public function cleanup(): int
    {
        $cleaned = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->cachePath),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'cache') {
                $content = file_get_contents($file->getPathname());
                if ($content !== false) {
                    $data = unserialize($content);
                    if ($data !== false && 
                        $data['expires'] !== null && 
                        $data['expires'] < time()) {
                        if (unlink($file->getPathname())) {
                            $cleaned++;
                        }
                    }
                }
            }
        }

        return $cleaned;
    }

    /**
     * Get cache statistics
     *
     * @return array
     */
    public function getStats(): array
    {
        $stats = [
            'total_files' => 0,
            'total_size' => 0,
            'expired_files' => 0,
            'cache_path' => $this->cachePath
        ];

        if (!is_dir($this->cachePath)) {
            return $stats;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->cachePath),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'cache') {
                $stats['total_files']++;
                $stats['total_size'] += $file->getSize();

                $content = file_get_contents($file->getPathname());
                if ($content !== false) {
                    $data = unserialize($content);
                    if ($data !== false && 
                        $data['expires'] !== null && 
                        $data['expires'] < time()) {
                        $stats['expired_files']++;
                    }
                }
            }
        }

        return $stats;
    }
}