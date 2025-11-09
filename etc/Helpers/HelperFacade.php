<?php
/**
 * HelperFacade.php - Unified Helper Access Point
 * 
 * Provides both OOP and procedural API for all helper functions.
 * This is the main helper class that Start.php will use.
 * 
 * @package upMVC\Helpers
 * @author BitsHost
 * @copyright 2023 BitsHost
 * @license MIT License
 */

namespace upMVC\Helpers;

use upMVC\Router;

class HelperFacade
{
    private static ?Router $router = null;
    
    /**
     * Set router instance (called from Start.php)
     */
    public static function setRouter(Router $router): void
    {
        self::$router = $router;
        RouteHelper::setRouter($router);
    }
    
    /**
     * Get router instance
     */
    public static function getRouter(): ?Router
    {
        return self::$router;
    }
    
    // ============================================================================
    // ROUTING HELPERS - Delegate to RouteHelper
    // ============================================================================
    
    public static function route(string $name, array $params = []): string
    {
        return RouteHelper::route($name, $params);
    }
    
    public static function url(string $path = ''): string
    {
        return UrlHelper::url($path);
    }
    
    public static function asset(string $path): string
    {
        return UrlHelper::asset($path);
    }
    
    public static function redirect(string $to, array $params = [], int $status = 302): void
    {
        RouteHelper::redirect($to, $params, $status);
    }
    
    // ============================================================================
    // FORM HELPERS - Delegate to FormHelper
    // ============================================================================
    
    public static function old(string $key, $default = '')
    {
        return FormHelper::old($key, $default);
    }
    
    public static function csrfToken(): string
    {
        return FormHelper::csrfToken();
    }
    
    public static function csrfField(): string
    {
        return FormHelper::csrfField();
    }
    
    // ============================================================================
    // DATA HELPERS - Delegate to DataHelper
    // ============================================================================
    
    public static function session(?string $key = null, $default = null)
    {
        return DataHelper::session($key, $default);
    }
    
    public static function config(string $key, $default = null)
    {
        return DataHelper::config($key, $default);
    }
    
    public static function env(string $key, $default = null)
    {
        return DataHelper::env($key, $default);
    }
    
    public static function request(?string $key = null, $default = null)
    {
        return DataHelper::request($key, $default);
    }
    
    // ============================================================================
    // RESPONSE HELPERS - Delegate to ResponseHelper
    // ============================================================================
    
    public static function view(string $path, array $data = []): void
    {
        ResponseHelper::view($path, $data);
    }
    
    public static function abort(int $code, string $message = ''): void
    {
        ResponseHelper::abort($code, $message);
    }
    
    public static function json($data, int $status = 200): void
    {
        ResponseHelper::json($data, $status);
    }
    
    // ============================================================================
    // DEBUG HELPERS - Delegate to DebugHelper
    // ============================================================================
    
    public static function dd(...$vars): void
    {
        DebugHelper::dd(...$vars);
    }
    
    public static function dump(...$vars): void
    {
        DebugHelper::dump(...$vars);
    }
}
