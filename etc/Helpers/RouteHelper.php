<?php
/**
 * RouteHelper.php - Routing and URL Generation
 * 
 * Handles named routes and URL generation.
 * 
 * @package upMVC\Helpers
 * @author BitsHost
 * @copyright 2023 BitsHost
 * @license MIT License
 */

namespace upMVC\Helpers;

use upMVC\Router;

class RouteHelper
{
    private static ?Router $router = null;
    
    /**
     * Set router instance
     */
    public static function setRouter(Router $router): void
    {
        self::$router = $router;
    }
    
    /**
     * Generate URL from named route
     */
    public static function route(string $name, array $params = []): string
    {
        if (!self::$router) {
            throw new \RuntimeException('Router not initialized. Call HelperFacade::setRouter() first.');
        }
        return self::$router->route($name, $params);
    }
    
    /**
     * Redirect to URL or named route
     */
    public static function redirect(string $to, array $params = [], int $status = 302): void
    {
        if (strpos($to, '.') !== false && !empty($params)) {
            $to = self::route($to, $params);
        }
        
        if ($to[0] === '/') {
            $to = UrlHelper::url($to);
        }
        
        http_response_code($status);
        header("Location: $to");
        exit;
    }
}
