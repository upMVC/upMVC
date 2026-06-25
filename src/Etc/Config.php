<?php
/**
 * Config.php - Application Configuration Management
 *
 * Configuration Priority (highest → lowest):
 *   1. src/Etc/.env           ← CHANGE THIS for your install
 *   2. $config array below    ← session, timezone, debug flags
 *   3. $fallbacks array below ← last-resort path/domain defaults
 *
 * ============================================================
 * NEW INSTALLATION CHECKLIST — everything you need to change
 * ============================================================
 *
 * STEP 1 — src/Etc/.env  (copy .env.example if it doesn't exist)
 * ---------------------------------------------------------------
 *   DOMAIN_NAME=http://localhost        → your domain, no trailing slash
 *                                         e.g. https://myapp.com
 *
 *   SITE_PATH=/upMVC/public             → subfolder path, or empty '' for domain root
 *                                         e.g. /myapp/public  OR  (leave blank)
 *
 *   APP_KEY=                            → run: php -r "echo bin2hex(random_bytes(32));"
 *
 *   DB_HOST=localhost                   → database host
 *   DB_NAME=your_database               → your database name
 *   DB_USER=root                        → your database user
 *   DB_PASS=                            → your database password
 *   DB_CHARSET=utf8mb4                  → leave as utf8mb4 unless you have a reason
 *
 *   SESSION_LIFETIME=3600               → seconds before session expires
 *   SESSION_SECURE=false                → set true if site runs on HTTPS
 *
 * STEP 2 — JWT (only if using JWT auth on API routes)
 * ---------------------------------------------------------------
 *   JWT_SECRET=                         → run: php -r "echo bin2hex(random_bytes(32));"
 *                                         Must be set — JwtService throws if empty
 *
 *   JWT_ACCESS_TTL=3600                 → access token lifetime in seconds  (1 hour)
 *   JWT_REFRESH_TTL=2592000             → refresh token lifetime in seconds (30 days)
 *
 *   Protect a route with JWT by adding ['jwt'] to its route definition.
 *   See docs/JWT_AUTHENTICATION.md for the full guide.
 *
 * STEP 3 — Rate limiting (optional, sensible defaults provided)
 * ---------------------------------------------------------------
 *   RATE_LIMIT=100                      → general limit: requests/hour per IP
 *   RATE_LIMIT_LOGIN_MAX=10             → max login attempts
 *   RATE_LIMIT_LOGIN_WINDOW=900         → window in seconds (15 min)
 *   RATE_LIMIT_SIGNUP_MAX=5             → max signup attempts
 *   RATE_LIMIT_SIGNUP_WINDOW=3600       → window in seconds (1 hour)
 *   RATE_LIMIT_API_MAX=100              → max API requests
 *   RATE_LIMIT_API_WINDOW=60            → window in seconds (1 min)
 *
 * STEP 4 — Protected routes
 * ---------------------------------------------------------------
 *   PROTECTED_ROUTES=/admin/*,/dashboard/*
 *                                       → comma-separated prefixes requiring login
 *
 *   ⚠  If PROTECTED_ROUTES is empty or not set, the framework falls back to a
 *      hardcoded list in Start.php → $defaultProtectedRoutes (around line 58):
 *
 *        '/dashboardexample/*', '/admin/*', '/users/*', '/moda'
 *
 *      These defaults exist for the standalone demo modules. For your own app,
 *      always set PROTECTED_ROUTES in .env so you control exactly what is protected.
 *      The $defaultProtectedRoutes array will be removed in a future release.
 *
 * STEP 5 — Module discovery (defaults work out of the box)
 * ---------------------------------------------------------------
 *   ROUTE_ERROR_HANDLING=true           → show errors when a module fails to load
 *   ROUTE_VERBOSE_LOGGING=true          → log successful route registrations
 *   ROUTE_DEBUG_OUTPUT=false            → raw discovery debug (dev only)
 *   ROUTE_SUBMODULE_DISCOVERY=true      → scan Modules/{*}/Modules/{*} nested structure
 *   ROUTE_USE_CACHE=false               → cache discovered routes (true in production)
 *
 * STEP 6 — $fallbacks array  (this file, ~line 118)
 * ---------------------------------------------------------------
 *   Used ONLY when .env is missing or a key is absent.
 *   Match them to your STEP 1 values so the app works even without .env.
 *
 *   'site_path'   => '/upMVC/public'    → same as SITE_PATH above
 *   'domain_name' => 'http://localhost' → same as DOMAIN_NAME above
 *
 * STEP 7 — $config array  (this file, ~line 126)
 * ---------------------------------------------------------------
 *   'debug'            => true          → set FALSE in production
 *   'timezone'         => 'UTC'         → e.g. 'Europe/Bucharest'
 *   'session.secure'   => false         → true if HTTPS
 *   'session.lifetime' => 3600          → must match SESSION_LIFETIME in .env
 *
 * STEP 8 — src/Etc/ConfigDatabase.php  (DB fallbacks)
 * ---------------------------------------------------------------
 *   Only relevant when running WITHOUT .env (e.g. quick local test).
 *   Keep these as dummy values in version control.
 *
 * ============================================================
 *
 * @package upMVC
 * @author BitsHost
 * @copyright 2023 BitsHost
 * @license MIT License
 */

namespace App\Etc;

use App\Etc\Config\Environment;
use App\Etc\Config\ConfigManager;

class Config
{
    // ========================================
    // Configuration Arrays
    // ========================================

    // -------------------------------------------------------
    // STEP 2 — Fallback path & domain (match your .env values)
    // -------------------------------------------------------
    private static $fallbacks = [
        'site_path'   => '/upMVC/public',    // SITE_PATH in .env   — '' for root, '/folder/public' for subfolder
        'domain_name' => 'http://localhost', // DOMAIN_NAME in .env — no trailing slash
    ];

    // -------------------------------------------------------
    // STEP 3 — App settings (debug, timezone, session, cache)
    // -------------------------------------------------------
    private static $config = [
        'debug'    => true,             // ← FALSE in production
        'timezone' => 'UTC',            // ← e.g. 'Europe/Bucharest'
        'session'  => [
            'name'     => 'UPMVC_SESSION',
            'lifetime' => 3600,         // ← match SESSION_LIFETIME in .env
            'secure'   => false,        // ← true if HTTPS
            'httponly' => true,
        ],
        'cache' => [
            'enabled' => false,         // ← true in production
            'driver'  => 'file',
            'ttl'     => 3600,
        ],
        'security' => [
            'csrf_protection' => true,
            'rate_limit'      => 100,   // requests per minute
        ],
    ];

    // Legacy constants - now loaded from .env
    // Use self::getSitePath() and self::getDomainName() instead
    // public const SITE_PATH = '/upMVC';  // Commented out - now using .env
    // public const DOMAIN_NAME = 'http://localhost';  // Commented out - now using .env

    // ========================================
    // Configuration Getters
    // ========================================
    
    /**
     * Get configuration value using dot notation
     * 
     * Used internally by initConfig() to read from $config array.
     * Supports nested keys like 'session.lifetime' or 'cache.enabled'.
     * 
     * @param string $key Configuration key (supports dot notation)
     * @param mixed $default Default value if key not found
     * @return mixed Configuration value or default
     * 
     * @example
     * Config::get('debug', false);           // Returns debug value
     * Config::get('session.lifetime', 3600); // Returns session lifetime
     */
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

    /**
     * Get SITE_PATH from .env or fallback
     * 
     * Returns the site path (e.g., '/upMVC' or '' for root).
     * Fallback rarely used since .env is always loaded in bootstrapApplication().
     *
     * @return string The site path
     */
    public static function getSitePath(): string
    {
        return Environment::get('SITE_PATH', self::$fallbacks['site_path']);
    }

    /**
     * Get DOMAIN_NAME from .env or fallback
     * 
     * Returns the domain name (e.g., 'http://localhost' or 'https://example.com').
     * Fallback rarely used since .env is always loaded in bootstrapApplication().
     *
     * @return string The domain name without trailing slash
     */
    public static function getDomainName(): string
    {
        return Environment::get('DOMAIN_NAME', self::$fallbacks['domain_name']);
    }

    /**
     * Get the full base URL (domain + site path)
     * 
     * Replacement for BASE_URL constant.
     * 
     * @return string The full base URL without trailing slash
     * 
     * @example
     * // Returns: 'http://localhost/upMVC'
     */
    public static function getBaseUrl(): string
    {
        return rtrim(self::getDomainName(), '/') . self::getSitePath();
    }

    /**
     * Get the application root directory path
     * 
     * Replacement for THIS_DIR constant.
     * 
     * @return string The absolute path to the application root
     */
    public static function getAppDir(): string
    {
        return Application::getInstance()->getAppRoot();
    }

    /**
     * Get the site path (alias for backward compatibility)
     * 
     * Replacement for SITEPATH constant.
     * 
     * @return string The site path
     */
    public static function getSitePathConstant(): string
    {
        return self::getSitePath();
    }

    // ========================================
    // Request Processing
    // ========================================

    /**
     * Get the requested route from the current request URI
     *
     * This method extracts the clean route from the request URI by:
     * 1. Initializing configuration
     * 2. Removing the site path prefix from the URI
     * 3. Removing any query string parameters from the URI
     *
     * The resulting route is the clean, path-only representation of the
     * requested resource, which can be used for routing and processing.
     *
     * @param string $reqURI The full request URI
     * @return string The extracted clean route
     * 
     * @example
     * // If SITE_PATH = '/upMVC' and URI = '/upMVC/users?id=5'
     * // Returns: '/users'
     */
    public function getReqRoute($reqURI)
    {
        $this->initConfig();
        
        $urlWithoutSitePath = $this->cleanUrlSitePath(self::getSitePath(), $reqURI);
        return $this->cleanUrlQuestionMark($urlWithoutSitePath);
    }

    // ========================================
    // Initialization
    // ========================================

    /**
     * Initialize the application configuration
     *
     * This method sets up the necessary configuration for the application:
     * 1. Sets timezone from config
     * 2. Configures error reporting based on debug mode
     * 3. Defines application directory and base URL constants
     * 4. Configures and starts PHP session with security settings
     * 5. Registers error handler
     *
     * This initialization should be performed before any other application
     * logic is executed to ensure a consistent configuration environment.
     *
     * @return void
     */
    private function initConfig(): void
    {
        // Ensure modern configuration is loaded so APP_DEBUG / app.debug is available
        if (class_exists(ConfigManager::class)) {
            ConfigManager::load();
        }

        // Set timezone (prefer app.timezone if available, fallback to legacy config)
        $timezone = method_exists(ConfigManager::class, 'get')
            ? (ConfigManager::get('app.timezone', self::get('timezone', 'UTC')))
            : self::get('timezone', 'UTC');
        date_default_timezone_set($timezone);
        
        // Error reporting based on debug mode (prefer APP_DEBUG / app.debug)
        $debug = method_exists(ConfigManager::class, 'get')
            ? (bool) ConfigManager::get('app.debug', self::get('debug', false))
            : (bool) self::get('debug', false);

        if ($debug) {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        } else {
            error_reporting(0);
            ini_set('display_errors', 0);
        }

        // Define application directory and base URL
        if (!defined('THIS_DIR')) {
            define('THIS_DIR', self::getAppDir() . '/src');
        }
        if (!defined('BASE_URL')) {
            define('BASE_URL', self::getDomainName() . self::getSitePath());
        }
        if (!defined('SITEPATH')) {
            define('SITEPATH', self::getSitePath());
        }

        // Session configuration — only configure when no session is active yet.
        // session_name() and session_set_cookie_params() must be called before session_start().
        if (session_status() === PHP_SESSION_NONE) {
            $cookieCfg   = method_exists(ConfigManager::class, 'get') ? (ConfigManager::get('session.cookie', []) ?: []) : [];
            $sessionName = $cookieCfg['name']      ?? self::get('session.name',     'UPMVC_SESSION');
            $lifetime    = method_exists(ConfigManager::class, 'get')
                ? (int) ConfigManager::get('session.lifetime', self::get('session.lifetime', 3600))
                : (int) self::get('session.lifetime', 3600);
            $secure      = $cookieCfg['secure']    ?? self::get('session.secure',   false);
            $httpOnly    = $cookieCfg['http_only'] ?? self::get('session.httponly', true);
            $sameSite    = ucfirst(strtolower($cookieCfg['same_site'] ?? 'Lax'));
            $domain      = $cookieCfg['domain']    ?? '';

            session_name($sessionName);
            session_set_cookie_params([
                'lifetime' => $lifetime,
                'path'     => '/',
                'domain'   => $domain,
                'secure'   => (bool) $secure,
                'httponly' => (bool) $httpOnly,
                'samesite' => $sameSite,
            ]);
            session_start();
        }
    }

    // ========================================
    // Helper Methods
    // ========================================

    /**
     * Remove query string from URL
     * 
     * Extracts the path component from a URL, removing query parameters.
     *
     * @param string $urlWithoutSitePath URL to clean
     * @return string Clean path without query string
     * 
     * @example
     * // Input: '/users?id=5&name=john'
     * // Returns: '/users'
     */
    private function cleanUrlQuestionMark(string $urlWithoutSitePath): string
    {
        $parts = parse_url($urlWithoutSitePath);
        return $parts['path'] ?? $urlWithoutSitePath;
    }

    /**
     * Remove site path prefix from URL
     * 
     * Strips the site path from the beginning of a URL to get the clean route.
     *
     * @param string $sitePath The site path to remove (e.g., '/upMVC')
     * @param string $reqUrl The full request URL
     * @return string URL without the site path prefix
     * 
     * @example
     * // If sitePath = '/upMVC' and reqUrl = '/upMVC/users'
     * // Returns: '/users'
     */
    private function cleanUrlSitePath(string $sitePath, string $reqUrl): string
    {
        return str_replace($sitePath, '', $reqUrl);
    }
}





