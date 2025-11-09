<?php
/**
 * Helpers.php - Global Helper Class
 * 
 * Pure PHP OOP helper class for upMVC framework.
 * Provides convenient static methods for routing, URLs, sessions, and more.
 * 
 * @package upMVC
 * @author BitsHost
 * @copyright 2023 BitsHost
 * @license MIT License
 */

namespace upMVC;

class Helpers
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
            throw new \RuntimeException('Router not initialized. Call Helpers::setRouter() first.');
        }
        return self::$router->route($name, $params);
    }
    
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
    
    /**
     * Redirect to URL or named route
     */
    public static function redirect(string $to, array $params = [], int $status = 302): void
    {
        if (strpos($to, '.') !== false && !empty($params)) {
            $to = self::route($to, $params);
        }
        
        if ($to[0] === '/') {
            $to = self::url($to);
        }
        
        http_response_code($status);
        header("Location: $to");
        exit;
    }
    
    /**
     * Get old input value from session
     */
    public static function old(string $key, $default = '')
    {
        return $_SESSION['_old_input'][$key] ?? $default;
    }
    
    /**
     * Get CSRF token from session
     */
    public static function csrfToken(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Generate CSRF hidden input field
     */
    public static function csrfField(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . self::csrfToken() . '">';
    }
    
    /**
     * Dump and die - Debug helper
     */
    public static function dd(...$vars): void
    {
        echo '<pre>';
        foreach ($vars as $var) {
            var_dump($var);
        }
        echo '</pre>';
        die(1);
    }
    
    /**
     * Get environment variable value
     */
    public static function env(string $key, $default = null)
    {
        return Config\Environment::get($key, $default);
    }
    
    /**
     * Get configuration value using dot notation
     */
    public static function config(string $key, $default = null)
    {
        return Config\ConfigManager::get($key, $default);
    }
    
    /**
     * Get or set session value
     */
    public static function session(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $_SESSION;
        }
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * Get request input value
     */
    public static function request(?string $key = null, $default = null)
    {
        $input = array_merge($_GET, $_POST);
        
        if ($key === null) {
            return $input;
        }
        return $input[$key] ?? $default;
    }
    
    /**
     * Render a view file
     */
    public static function view(string $path, array $data = []): void
    {
        extract($data);
        $viewPath = __DIR__ . '/../modules/' . str_replace('.', '/', $path) . '.php';
        
        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View not found: {$path}");
        }
        
        include $viewPath;
    }
    
    /**
     * Abort with HTTP status code
     */
    public static function abort(int $code, string $message = ''): void
    {
        http_response_code($code);
        
        if ($message) {
            echo $message;
        }
        
        exit;
    }
    
    /**
     * Return JSON response
     */
    public static function json($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
