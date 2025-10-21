<?php
/**
 * AuthMiddleware.php - Authentication Middleware
 * 
 * This middleware provides route-based authentication protection for upMVC:
 * - Checks if user is authenticated before allowing access
 * - Redirects unauthenticated users to login page
 * - Stores intended URL for post-login redirect
 * - Pattern-based route matching (wildcards supported)
 * - Backward compatibility with legacy session variables
 * 
 * Features:
 * - Flexible route protection patterns (e.g., '/admin/*')
 * - Configurable redirect destination
 * - Intended URL preservation (prevents overwriting)
 * - Session-based authentication check
 * - fnmatch() pattern matching for routes
 * 
 * Session Variables Used:
 * - $_SESSION['authenticated'] (new standard)
 * - $_SESSION['logged'] (legacy compatibility)
 * - $_SESSION['intended_url'] (redirect after login)
 * 
 * Route Pattern Examples:
 * - '/admin/*' - All admin routes
 * - '/user/profile' - Specific route
 * - '/api/*' - All API routes
 * 
 * Security Note:
 * Intended URL is only set once to prevent session fixation attacks.
 * Always validate redirects to prevent open redirect vulnerabilities.
 * 
 * @package upMVC\Middleware
 * @author BitsHost
 * @copyright 2025 BitsHost
 * @license MIT License
 * @link https://bitshost.biz/
 * @created October 11, 2025
 */

namespace upMVC\Middleware;

use upMVC\Middleware\MiddlewareInterface;

class AuthMiddleware implements MiddlewareInterface
{
    // ========================================
    // Properties
    // ========================================
    
    /**
     * Protected route patterns
     * 
     * Array of fnmatch() patterns for routes requiring authentication.
     * Supports wildcards: * (multiple chars), ? (single char)
     * 
     * @var array
     */
    private array $protectedRoutes;

    /**
     * Redirect destination for unauthenticated users
     * 
     * Relative path to login page.
     * 
     * @var string
     */
    private string $redirectTo;

    // ========================================
    // Initialization
    // ========================================

    /**
     * Constructor - Initialize authentication middleware
     * 
     * @param array $protectedRoutes Route patterns requiring auth (default: [])
     * @param string $redirectTo Login page path (default: '/auth')
     * 
     * @example
     * // Protect admin and user profile routes
     * $auth = new AuthMiddleware(['/admin/*', '/user/profile'], '/login');
     * 
     * @example
     * // Protect all API routes
     * $auth = new AuthMiddleware(['/api/*'], '/auth/login');
     */
    public function __construct(array $protectedRoutes = [], string $redirectTo = '/auth')
    {
        $this->protectedRoutes = $protectedRoutes;
        $this->redirectTo = $redirectTo;
    }

    // ========================================
    // Middleware Handler
    // ========================================

    /**
     * Handle authentication check
     * 
     * Checks if current route requires authentication.
     * If required and user not authenticated, redirects to login page.
     * Stores intended URL for post-login redirect.
     * 
     * @param array $request Request data (route, uri, etc.)
     * @param callable $next Next middleware in chain
     * @return mixed Next middleware result or redirect
     * 
     * @example
     * // Middleware chain usage
     * $result = $authMiddleware->handle($request, function($req) {
     *     return $controller->action();
     * });
     */
    public function handle(array $request, callable $next)
    {
        $route = $request['route'] ?? '';
        
        // Check if route requires authentication
        if ($this->requiresAuth($route)) {
            if (!$this->isAuthenticated()) {
                // Store intended URL ONLY if not already set
                // This prevents overwriting when redirecting to login page
                // Security: Protects against session fixation attacks
                if (!isset($_SESSION['intended_url'])) {
                    $intendedUrl = $request['uri'];
                    $_SESSION['intended_url'] = $intendedUrl;
                }
                
                // Redirect to login page
                $baseUrl = defined('BASE_URL') ? BASE_URL : '';
                header('Location: ' . $baseUrl . $this->redirectTo);
                exit;
            }
        }

        // User authenticated or route not protected - continue
        return $next($request);
    }

    // ========================================
    // Route Protection
    // ========================================

    /**
     * Check if route requires authentication
     * 
     * Uses fnmatch() for pattern matching with wildcards.
     * Returns true if route matches any protected pattern.
     * 
     * @param string $route Current route path
     * @return bool True if route requires authentication
     * 
     * @example
     * // Protected routes: ['/admin/*', '/user/profile']
     * requiresAuth('/admin/dashboard')  // true (matches /admin/*)
     * requiresAuth('/user/profile')     // true (exact match)
     * requiresAuth('/public/page')      // false (no match)
     */
    private function requiresAuth(string $route): bool
    {
        // Check route against all protected patterns
        foreach ($this->protectedRoutes as $pattern) {
            if (fnmatch($pattern, $route)) {
                return true; // Route requires authentication
            }
        }
        return false; // Route is public
    }

    // ========================================
    // Authentication Check
    // ========================================

    /**
     * Check if user is authenticated
     * 
     * Supports both new and legacy session variables for compatibility.
     * Checks $_SESSION['authenticated'] (new) and $_SESSION['logged'] (legacy).
     * 
     * @return bool True if user is authenticated
     * 
     * @example
     * // Set authentication in login controller:
     * $_SESSION['authenticated'] = true;  // New standard
     * 
     * @example
     * // Legacy support (automatically detected):
     * $_SESSION['logged'] = true;  // Old variable name
     */
    private function isAuthenticated(): bool
    {
        // Check new session variable (recommended)
        $newAuth = isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
        
        // Check legacy session variable (backward compatibility)
        $legacyAuth = isset($_SESSION['logged']) && $_SESSION['logged'] === true;
        
        // User is authenticated if either variable is true
        return $newAuth || $legacyAuth;
    }
}