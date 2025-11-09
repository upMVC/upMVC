<?php
/**
 * Global Helper Functions
 * 
 * Convenience functions that wrap Helpers class methods.
 * Provides procedural-style API for common tasks.
 * 
 * These functions make code cleaner in views and controllers:
 * - route() instead of Helpers::route()
 * - url() instead of Helpers::url()
 * - redirect() instead of Helpers::redirect()
 * 
 * @package upMVC
 * @author BitsHost
 * @copyright 2023 BitsHost
 * @license MIT License
 */

use upMVC\Helpers;

if (!function_exists('route')) {
    /**
     * Generate URL from named route
     * 
     * @param string $name Route name
     * @param array $params Route parameters
     * @return string Generated URL
     * 
     * @example
     * route('user.show', ['id' => 123])  // /users/123
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
     * 
     * @example
     * url('/admin/users')  // http://localhost/admin/users
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
     * 
     * @example
     * asset('css/style.css')  // http://localhost/css/style.css
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
     * 
     * @example
     * redirect('/admin/users')
     * redirect('user.show', ['id' => 123])
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
     * 
     * @example
     * <input name="username" value="<?= old('username') ?>">
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
     * 
     * @example
     * <form method="POST">
     *     <?= csrf_field() ?>
     * </form>
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
     * @param string $key Session key
     * @param mixed $default Default value
     * @return mixed Session value
     */
    function session(string $key, $default = null)
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

if (!function_exists('dd')) {
    /**
     * Dump and die - debug helper
     * 
     * @param mixed ...$vars Variables to dump
     * @return void
     */
    function dd(...$vars): void
    {
        echo '<pre>';
        foreach ($vars as $var) {
            var_dump($var);
        }
        echo '</pre>';
        die(1);
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
