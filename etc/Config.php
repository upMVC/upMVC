<?php
/*
 *   Created on Tue Oct 31 2023
 *   Copyright (c) 2023 BitsHost
 *   All rights reserved.
 *
 *   Permission is hereby granted, free of charge, to any person obtaining a copy
 *   of this software and associated documentation files (the "Software"), to deal
 *   in the Software without restriction, including without limitation the rights
 *   to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *   copies of the Software, and to permit persons to whom the Software is
 *   furnished to do so, subject to the following conditions:
 *
 *   The above copyright notice and this permission notice shall be included in all
 *   copies or substantial portions of the Software.
 *
 *   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *   IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *   FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *   AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *   LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *   OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 *   SOFTWARE.
 *   Here you may host your app for free:
 *   https://bitshost.biz/
 */

namespace upMVC;

/**
 * Config
 */
class Config
{
    public const SITE_PATH = '/upMVC';
    public const DOMAIN_NAME = 'http://localhost';
    
    private static $config = [
        'debug' => true,
        'timezone' => 'UTC',
        'session' => [
            'name' => 'UPMVC_SESSION',
            'lifetime' => 3600,
            'secure' => false,
            'httponly' => true
        ],
        'cache' => [
            'enabled' => false,
            'driver' => 'file',
            'ttl' => 3600
        ],
        'security' => [
            'csrf_protection' => true,
            'rate_limit' => 100
        ]
    ];
    
    public static function get(string $key, $default = null)
    {
        $parts = explode('.', $key);
        $config = self::$config;
        
        foreach ($parts as $part) {
            if (isset($config[$part])) {
                $config = $config[$part];
            } else {
                return $default;
            }
        }
        
        return $config;
    }
    
    public static function set(string $key, $value): void
    {
        $parts = explode('.', $key);
        $config = &self::$config;
        
        foreach ($parts as $part) {
            if (!isset($config[$part])) {
                $config[$part] = [];
            }
            $config = &$config[$part];
        }
        
        $config = $value;
    }

    /**
     * Get the requested route from the current request URI.
     *
     * This method extracts the route from the request URI by:
     * 1. Removing the site path prefix from the URI.
     * 2. Removing any query string parameters from the URI.
     *
     * The resulting route is the clean, path-only representation of the
     * requested resource, which can be used for routing and processing.
     *
     * @param string $reqURI The full request URI.
     * @return string The extracted route.
     */


    public function getReqRoute($reqURI)
    {
        // Initialize the configuration
        $this->initConfig();

        // Remove the site path from the request URI
        $urlWithoutSitePath = $this->cleanUrlSitePath(self::SITE_PATH, $reqURI);

        // Remove the query string from the URL
        return $this->cleanUrlQuestionMark($urlWithoutSitePath);
    }

    /**
     * Initialize the application configuration.
     *
     * This method sets up the necessary configuration for the application,
     * including:
     * 1. Disabling error reporting for deprecated and notice-level errors.
     * 2. Defining the application's base directory and URL.
     * 3. Starting the PHP session.
     *
     * This initialization should be performed before any other application
     * logic is executed, to ensure a consistent and reliable configuration
     * environment.
     *
     * @return void
     */


    /**
     * initConfig
     *
     * @return void
     */
    
    private function initConfig(): void
    {
        // Set timezone
        date_default_timezone_set(self::get('timezone', 'UTC'));
        
        // Error reporting based on debug mode
        if (self::get('debug', false)) {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        } else {
            error_reporting(0);
            ini_set('display_errors', 0);
        }

        define('THIS_DIR', str_replace('\\', '/', dirname(__FILE__, 2)));
        define('BASE_URL', self::DOMAIN_NAME . self::SITE_PATH);
        define('SITEPATH', SELF::SITE_PATH);

        // Enhanced session configuration
        $sessionConfig = self::get('session', []);
        if (isset($sessionConfig['name'])) {
            session_name($sessionConfig['name']);
        }
        
        session_set_cookie_params([
            'lifetime' => $sessionConfig['lifetime'] ?? 3600,
            'secure' => $sessionConfig['secure'] ?? false,
            'httponly' => $sessionConfig['httponly'] ?? true,
            'samesite' => 'Strict'
        ]);
        
        session_start();
        
        // Initialize error handler
        ErrorHandler::register();
    }


    /**
     * cleanUrlQuestionMark
     *
     * @param string $urlWithoutSitePath
     * @return string
     */
    private function cleanUrlQuestionMark(string $urlWithoutSitePath): string
    {
        $parts = parse_url($urlWithoutSitePath);
        return $parts['path'] ?? $urlWithoutSitePath;
    }

    /**
     * cleanUrlSitePath
     *
     * @param string $sitePath
     * @param string $reqUrl
     * @return string
     */
    private function cleanUrlSitePath(string $sitePath, string $reqUrl): string
    {
        return str_replace($sitePath, '', $reqUrl);
    }
}
