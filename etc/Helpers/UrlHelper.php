<?php
/**
 * UrlHelper.php - URL and Asset Generation
 * 
 * Handles URL construction and asset paths.
 * 
 * @package upMVC\Helpers
 * @author BitsHost
 * @copyright 2023 BitsHost
 * @license MIT License
 */

namespace upMVC\Helpers;

class UrlHelper
{
    /**
     * Generate full URL with BASE_URL prefix
     */
    public static function url(string $path = ''): string
    {
        if (!defined('BASE_URL')) {
            throw new \RuntimeException('BASE_URL constant not defined');
        }
        return BASE_URL . $path;
    }
    
    /**
     * Generate asset URL
     */
    public static function asset(string $path): string
    {
        return self::url('/' . ltrim($path, '/'));
    }
}
