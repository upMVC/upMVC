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
        
        // Check if route requires authentication
        if ($this->requiresAuth($route)) {
            if (!$this->isAuthenticated()) {
                // Store intended URL for redirect after login
                $_SESSION['intended_url'] = $request['uri'] ?? $route;
                
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
        return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
    }
}