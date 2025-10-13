<?php

namespace upMVC;

class Cache
{
    private static $cacheDir = 'cache/';
    
    public static function get(string $key, $default = null)
    {
        if (!Config::get('cache.enabled', false)) {
            return $default;
        }
        
        $file = self::getCacheFile($key);
        
        if (!file_exists($file)) {
            return $default;
        }
        
        $data = unserialize(file_get_contents($file));
        
        if ($data['expires'] < time()) {
            unlink($file);
            return $default;
        }
        
        return $data['value'];
    }
    
    public static function set(string $key, $value, int $ttl = null): bool
    {
        if (!Config::get('cache.enabled', false)) {
            return false;
        }
        
        if (!is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0755, true);
        }
        
        $ttl = $ttl ?? Config::get('cache.ttl', 3600);
        $file = self::getCacheFile($key);
        
        $data = [
            'value' => $value,
            'expires' => time() + $ttl
        ];
        
        return file_put_contents($file, serialize($data), LOCK_EX) !== false;
    }
    
    public static function delete(string $key): bool
    {
        $file = self::getCacheFile($key);
        return file_exists($file) ? unlink($file) : true;
    }
    
    public static function clear(): bool
    {
        if (!is_dir(self::$cacheDir)) {
            return true;
        }
        
        $files = glob(self::$cacheDir . '*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
        
        return true;
    }
    
    private static function getCacheFile(string $key): string
    {
        return self::$cacheDir . md5($key) . '.cache';
    }
}