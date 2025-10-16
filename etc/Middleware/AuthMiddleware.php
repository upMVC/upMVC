<?php
/*
 *   Created on October 11, 2025
 *   Copyright (c) 2025 BitsHost
 *   All rights reserved.
 *
 *   Enhanced upMVC - Authentication Middleware
 */

namespace upMVC\Middleware;

use upMVC\Middleware\MiddlewareInterface;

/**
 * AuthMiddleware
 * 
 * Handles authentication checks
 */
class AuthMiddleware implements MiddlewareInterface
{
    /**
     * @var array
     */
    private array $protectedRoutes;

    /**
     * @var string
     */
    private string $redirectTo;

    public function __construct(array $protectedRoutes = [], string $redirectTo = '/auth')
    {
        $this->protectedRoutes = $protectedRoutes;
        $this->redirectTo = $redirectTo;
    }

    /**
     * Handle authentication
     *
     * @param array $request
     * @param callable $next
     * @return mixed
     */
    public function handle(array $request, callable $next)
    {
        $route = $request['route'] ?? '';
        
        // DEBUG: Write to upMVC logs folder
        $logFile = THIS_DIR . '/logs/debug_' . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');
        
        file_put_contents($logFile, "[$timestamp] DEBUG AuthMiddleware - route: $route\n", FILE_APPEND);
        file_put_contents($logFile, "[$timestamp] DEBUG AuthMiddleware - request[uri]: " . ($request['uri'] ?? 'NULL') . "\n", FILE_APPEND);
        
        // Check if route requires authentication
        if ($this->requiresAuth($route)) {
            file_put_contents($logFile, "[$timestamp] DEBUG AuthMiddleware - Route requires auth: $route\n", FILE_APPEND);
            
            if (!$this->isAuthenticated()) {
                // Store intended URL ONLY if not already set
                // This prevents overwriting when redirecting to login page
                if (!isset($_SESSION['intended_url'])) {
                    // ALWAYS use the original URI from Start.php ($this->reqURI)
                    $intendedUrl = $request['uri'];  // This is $this->reqURI from Start.php
                    $_SESSION['intended_url'] = $intendedUrl;
                    
                    // DEBUG: What are we storing?
                    file_put_contents($logFile, "[$timestamp] DEBUG AuthMiddleware - Storing intended_url: $intendedUrl\n", FILE_APPEND);
                } else {
                    // DEBUG: Already have intended_url, not overwriting
                    file_put_contents($logFile, "[$timestamp] DEBUG AuthMiddleware - intended_url already set: {$_SESSION['intended_url']}\n", FILE_APPEND);
                }
                
                $baseUrl = defined('BASE_URL') ? BASE_URL : '';
                header('Location: ' . $baseUrl . $this->redirectTo);
                exit;
            }
        }

        return $next($request);
    }

    /**
     * Check if route requires authentication
     *
     * @param string $route
     * @return bool
     */
    private function requiresAuth(string $route): bool
    {
        foreach ($this->protectedRoutes as $pattern) {
            if (fnmatch($pattern, $route)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user is authenticated
     *
     * @return bool
     */
    private function isAuthenticated(): bool
    {
        // Check both new and legacy session variables for compatibility
        $newAuth = isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
        $legacyAuth = isset($_SESSION['logged']) && $_SESSION['logged'] === true;
        
        return $newAuth || $legacyAuth;
    }
}