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

// ============================================================================
// GLOBAL HELPER FUNCTIONS (Procedural API)
// ============================================================================
//
// These functions provide a clean procedural API that wraps the Helpers class.
// Both APIs work identically - choose based on preference:
//
// OOP:         Helpers::route('user.show', ['id' => 1])
// Procedural:  route('user.show', ['id' => 1])
//
// ============================================================================

if (!function_exists('route')) {
    /**
     * Generate URL from named route
     * 
     * @param string $name Route name
     * @param array $params Route parameters
     * @return string Generated URL
     */
    function route(string $name, array $params = []): string
    {
        return Helpers::route($name, $params);
    }
}

if (!function_exists('url')) {
    /**
     * Generate full URL with BASE_URL prefix
     * 
     * @param string $path Path to append to BASE_URL
     * @return string Full URL
     */
    function url(string $path = ''): string
    {
        return Helpers::url($path);
    }
}

if (!function_exists('asset')) {
    /**
     * Generate asset URL
     * 
     * @param string $path Asset path
     * @return string Full asset URL
     */
    function asset(string $path): string
    {
        return Helpers::asset($path);
    }
}

if (!function_exists('redirect')) {
    /**
     * Redirect to URL or named route
     * 
     * @param string $to URL or route name
     * @param array $params Route parameters (if using named route)
     * @param int $status HTTP status code
     * @return void
     */
    function redirect(string $to, array $params = [], int $status = 302): void
    {
        Helpers::redirect($to, $params, $status);
    }
}

if (!function_exists('old')) {
    /**
     * Get old input value from session (for form repopulation)
     * 
     * @param string $key Input field name
     * @param mixed $default Default value if not found
     * @return mixed Old input value
     */
    function old(string $key, $default = '')
    {
        return Helpers::old($key, $default);
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Get CSRF token from session
     * 
     * @return string CSRF token
     */
    function csrf_token(): string
    {
        return Helpers::csrfToken();
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Generate CSRF hidden input field
     * 
     * @return string HTML hidden input
     */
    function csrf_field(): string
    {
        return Helpers::csrfField();
    }
}

if (!function_exists('session')) {
    /**
     * Get session value
     * 
     * @param string|null $key Session key
     * @param mixed $default Default value
     * @return mixed Session value
     */
    function session(?string $key = null, $default = null)
    {
        return Helpers::session($key, $default);
    }
}

if (!function_exists('config')) {
    /**
     * Get config value
     * 
     * @param string $key Config key
     * @param mixed $default Default value
     * @return mixed Config value
     */
    function config(string $key, $default = null)
    {
        return Helpers::config($key, $default);
    }
}

if (!function_exists('env')) {
    /**
     * Get environment variable
     * 
     * @param string $key Environment variable name
     * @param mixed $default Default value
     * @return mixed Environment value
     */
    function env(string $key, $default = null)
    {
        return Helpers::env($key, $default);
    }
}

if (!function_exists('view')) {
    /**
     * Render a view file
     * 
     * @param string $path View path (dot notation)
     * @param array $data Data to pass to view
     * @return void
     */
    function view(string $path, array $data = []): void
    {
        Helpers::view($path, $data);
    }
}

if (!function_exists('abort')) {
    /**
     * Abort with HTTP status code
     * 
     * @param int $code HTTP status code
     * @param string $message Optional message
     * @return void
     */
    function abort(int $code, string $message = ''): void
    {
        Helpers::abort($code, $message);
    }
}

if (!function_exists('json')) {
    /**
     * Return JSON response
     * 
     * @param mixed $data Data to encode
     * @param int $status HTTP status code
     * @return void
     */
    function json($data, int $status = 200): void
    {
        Helpers::json($data, $status);
    }
}

if (!function_exists('request')) {
    /**
     * Get request input value
     * 
     * @param string|null $key Input key
     * @param mixed $default Default value
     * @return mixed Request value
     */
    function request(?string $key = null, $default = null)
    {
        return Helpers::request($key, $default);
    }
}

if (!function_exists('dd')) {
    /**
     * Dump and die - debug helper
     * 
     * @param mixed ...$vars Variables to dump
     * @return void
     */
    function dd(...$vars): void
    {
        Helpers::dd(...$vars);
    }
}

if (!function_exists('dump')) {
    /**
     * Dump variable without dying
     * 
     * @param mixed ...$vars Variables to dump
     * @return void
     */
    function dump(...$vars): void
    {
        echo '<pre>';
        foreach ($vars as $var) {
            var_dump($var);
        }
        echo '</pre>';
    }
}
