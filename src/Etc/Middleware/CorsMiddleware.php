<?php
/**
 * CorsMiddleware.php - CORS (Cross-Origin Resource Sharing) Middleware
 * 
 * This middleware handles CORS headers for API endpoints and cross-origin requests:
 * - Configurable allowed origins (whitelist or wildcard)
 * - HTTP methods control (GET, POST, PUT, DELETE, etc.)
 * - Custom headers support
 * - Credentials support (cookies, authorization headers)
 * - Preflight request handling (OPTIONS)
 * - Max-Age caching for preflight responses
 * 
 * Features:
 * - Origin whitelist validation
 * - Wildcard origin support (*)
 * - Automatic preflight (OPTIONS) handling
 * - Configurable cache duration (max-age)
 * - Credentials flag for cookies/auth
 * 
 * CORS Flow:
 * 1. Browser sends preflight OPTIONS request
 * 2. Middleware responds with allowed origins/methods/headers
 * 3. Browser caches response (max-age)
 * 4. Actual request proceeds with CORS headers
 * 
 * Configuration Example:
 * [
 *   'origins' => ['https://example.com', 'https://app.example.com'],
 *   'methods' => ['GET', 'POST', 'PUT', 'DELETE'],
 *   'headers' => ['Content-Type', 'Authorization'],
 *   'credentials' => true,
 *   'max_age' => 86400  // 24 hours
 * ]
 * 
 * Security Note:
 * Avoid '*' wildcard with credentials enabled - browsers reject this.
 * Always specify explicit origins when using credentials.
 * 
 * @package upMVC\Middleware
 * @author BitsHost
 * @copyright 2025 BitsHost
 * @license MIT License
 * @link https://bitshost.biz/
 * @created October 11, 2025
 */

namespace App\Etc\Middleware;

use App\Etc\Middleware\MiddlewareInterface;

class CorsMiddleware implements MiddlewareInterface
{
    // ========================================
    // Properties
    // ========================================
    
    /**
     * Allowed origins (domains)
     * 
     * List of allowed origin URLs or ['*'] for all origins.
     * Examples: ['https://example.com', 'https://app.example.com']
     * 
     * @var array
     */
    private array $allowedOrigins;

    /**
     * Allowed HTTP methods
     * 
     * List of HTTP methods permitted for cross-origin requests.
     * 
     * @var array
     */
    private array $allowedMethods;

    /**
     * Allowed request headers
     * 
     * List of headers the client is allowed to send.
     * 
     * @var array
     */
    private array $allowedHeaders;

    /**
     * Allow credentials flag
     * 
     * When true, allows cookies and authorization headers.
     * Cannot be used with wildcard (*) origins.
     * 
     * @var bool
     */
    private bool $allowCredentials;

    /**
     * Preflight cache duration in seconds
     * 
     * How long browsers should cache preflight responses.
     * Default: 86400 (24 hours)
     * 
     * @var int
     */
    private int $maxAge;

    // ========================================
    // Initialization
    // ========================================

    /**
     * Constructor - Initialize CORS middleware
     * 
     * @param array $config CORS configuration array
     * 
     * @example
     * // API with specific origins
     * $cors = new CorsMiddleware([
     *     'origins' => ['https://app.example.com'],
     *     'methods' => ['GET', 'POST', 'PUT', 'DELETE'],
     *     'headers' => ['Content-Type', 'Authorization'],
     *     'credentials' => true,
     *     'max_age' => 3600
     * ]);
     * 
     * @example
     * // Public API allowing all origins
     * $cors = new CorsMiddleware([
     *     'origins' => ['*'],
     *     'credentials' => false
     * ]);
     */
    public function __construct(array $config = [])
    {
        $origins = $config['origins'] ?? ['*'];
        if (is_string($origins)) {
            $origins = explode(',', $origins);
        }
        $this->allowedOrigins = array_values(array_filter(array_map(
            static fn($o) => is_string($o) ? trim($o) : '',
            (array) $origins
        ), static fn($o) => $o !== ''));
        if ($this->allowedOrigins === []) {
            $this->allowedOrigins = ['*'];
        }

        $this->allowedMethods = $config['methods'] ?? ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'];
        if (is_string($this->allowedMethods)) {
            $this->allowedMethods = array_map('trim', explode(',', $this->allowedMethods));
        }

        $this->allowedHeaders = $config['headers'] ?? ['Content-Type', 'Authorization', 'X-Requested-With', 'X-CSRF-Token'];
        if (is_string($this->allowedHeaders)) {
            $this->allowedHeaders = array_map('trim', explode(',', $this->allowedHeaders));
        }

        $this->allowCredentials = (bool) ($config['credentials'] ?? false);
        $this->maxAge = (int) ($config['max_age'] ?? 86400);
    }

    // ========================================
    // Middleware Handler
    // ========================================

    /**
     * Handle CORS headers and preflight requests
     * 
     * Sets CORS headers on all responses.
     * Handles OPTIONS preflight requests by returning 200 immediately.
     * 
     * @param array $request Request data (method, headers, etc.)
     * @param callable $next Next middleware in chain
     * @return mixed Next middleware result or preflight response
     * 
     * @example
     * // Middleware chain usage
     * $result = $corsMiddleware->handle($request, function($req) {
     *     return $controller->action();
     * });
     */
    public function handle(array $request, callable $next)
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $method = $request['method'] ?? 'GET';

        // Set CORS headers on response
        $this->setCorsHeaders($origin);

        // Handle preflight (OPTIONS) requests
        // Browser sends OPTIONS before actual request to check permissions
        if ($method === 'OPTIONS') {
            http_response_code(200);
            exit; // Stop execution, don't process route
        }

        // Continue to next middleware/controller
        return $next($request);
    }

    // ========================================
    // CORS Headers
    // ========================================

    /**
     * Set CORS headers on response
     * 
     * Sets appropriate Access-Control-* headers based on configuration.
     * Validates origin against whitelist if not using wildcard.
     * 
     * @param string $origin Request origin from HTTP_ORIGIN header
     * @return void
     */
    private function setCorsHeaders(string $origin): void
    {
        $reflected = false;
        $wildcard = in_array('*', $this->allowedOrigins, true);

        if ($origin !== '' && $this->isOriginAllowed($origin)) {
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Vary: Origin');
            $reflected = true;
        } elseif ($wildcard && !$this->allowCredentials) {
            header('Access-Control-Allow-Origin: *');
        } elseif ($wildcard && $this->allowCredentials) {
            // Never reflect arbitrary Origin with credentials (CWE-942).
            // Browsers also reject Allow-Origin: * + credentials — refuse both.
            error_log('CorsMiddleware: origins=* with credentials=true is unsafe; set an explicit origin whitelist');
        }

        header('Access-Control-Allow-Methods: ' . implode(', ', $this->allowedMethods));
        header('Access-Control-Allow-Headers: ' . implode(', ', $this->allowedHeaders));
        header('Access-Control-Max-Age: ' . $this->maxAge);

        if ($this->allowCredentials && $reflected) {
            header('Access-Control-Allow-Credentials: true');
        }
    }

    // ========================================
    // Origin Validation
    // ========================================

    /**
     * Check if origin is in allowed list
     * 
     * Performs exact match against whitelist.
     * Does NOT check wildcard - that's handled separately.
     * 
     * @param string $origin Origin URL from request
     * @return bool True if origin is explicitly allowed
     * 
     * @example
     * // Allowed origins: ['https://example.com']
     * isOriginAllowed('https://example.com')     // true
     * isOriginAllowed('https://evil.com')        // false
     * isOriginAllowed('http://example.com')      // false (protocol matters!)
     */
    private function isOriginAllowed(string $origin): bool
    {
        return in_array($origin, $this->allowedOrigins, true);
    }
}




