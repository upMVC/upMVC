<?php
/**
 * MiddlewareManager.php - Middleware Pipeline Manager
 * 
 * This class manages and executes middleware chains for upMVC routes.
 * It provides a flexible system for applying middleware globally or per-route.
 * 
 * Key Responsibilities:
 * - Register global middleware (applies to all routes)
 * - Register route-specific middleware
 * - Build middleware pipeline using functional composition
 * - Execute pipeline with proper request flow
 * - Retrieve middleware configuration for inspection
 * 
 * Middleware Types:
 * 1. Global Middleware - Applied to every route
 *    Examples: Logging, CORS, Security Headers
 * 
 * 2. Route-Specific Middleware - Applied to specific routes
 *    Examples: Authentication (only on protected routes)
 * 
 * Execution Order:
 * Global middleware executes BEFORE route-specific middleware.
 * Middleware executes in registration order.
 * 
 * Pipeline Pattern:
 * Uses array_reduce() to build a nested chain of closures.
 * Each closure wraps the next, creating an "onion" pattern:
 * Request → MW1 → MW2 → MW3 → Controller → MW3 → MW2 → MW1 → Response
 * 
 * Implementation Details:
 * - Fluent interface (method chaining)
 * - Functional programming (closures)
 * - Lazy execution (pipeline built on demand)
 * - Memory efficient (no middleware duplication)
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

class MiddlewareManager
{
    // ========================================
    // Properties
    // ========================================
    
    /**
     * Route-specific middleware
     * 
     * Structure: ['route' => [MiddlewareInterface, ...]]
     * 
     * @var array
     */
    private array $middleware = [];

    /**
     * Global middleware (applies to all routes)
     * 
     * Executed before route-specific middleware.
     * 
     * @var array
     */
    private array $globalMiddleware = [];

    // ========================================
    // Middleware Registration
    // ========================================

    /**
     * Add global middleware that runs on all routes
     * 
     * Global middleware executes before route-specific middleware.
     * Common use cases: logging, CORS, security headers.
     * 
     * @param MiddlewareInterface $middleware Middleware instance
     * @return self Fluent interface for method chaining
     * 
     * @example
     * // Add logging to all routes
     * $manager->addGlobal(new LoggingMiddleware())
     *         ->addGlobal(new CorsMiddleware());
     */
    public function addGlobal(MiddlewareInterface $middleware): self
    {
        $this->globalMiddleware[] = $middleware;
        return $this; // Fluent interface
    }

    /**
     * Add middleware for specific route
     * 
     * Route-specific middleware executes after global middleware.
     * Multiple middleware can be added to same route.
     * 
     * @param string $route Route path (e.g., '/admin', '/api/users')
     * @param MiddlewareInterface $middleware Middleware instance
     * @return self Fluent interface for method chaining
     * 
     * @example
     * // Protect admin routes with authentication
     * $manager->addForRoute('/admin', new AuthMiddleware())
     *         ->addForRoute('/admin', new AdminRoleMiddleware());
     * 
     * @example
     * // Add rate limiting to API routes
     * $manager->addForRoute('/api/users', new RateLimitMiddleware());
     */
    public function addForRoute(string $route, MiddlewareInterface $middleware): self
    {
        // Initialize route middleware array if needed
        if (!isset($this->middleware[$route])) {
            $this->middleware[$route] = [];
        }
        
        // Add middleware to route
        $this->middleware[$route][] = $middleware;
        return $this; // Fluent interface
    }

    // ========================================
    // Pipeline Execution
    // ========================================

    /**
     * Execute middleware pipeline for route
     * 
     * Builds and executes middleware chain using functional composition.
     * Pipeline flows: Global MW → Route MW → Final callable (controller)
     * 
     * Uses array_reduce() to create nested closures ("onion" pattern):
     * Each middleware wraps the next, allowing pre/post processing.
     * 
     * @param string $route Route path being executed
     * @param array $request Request data
     * @param callable $final Final callable (typically the controller action)
     * @return mixed Result from final callable or middleware short-circuit
     * 
     * @example
     * // Execute pipeline for admin dashboard
     * $result = $manager->execute('/admin/dashboard', $request, function($req) {
     *     return $controller->dashboard();
     * });
     */
    public function execute(string $route, array $request, callable $final)
    {
        // Combine global and route-specific middleware
        // Global middleware comes first (executes before route middleware)
        $allMiddleware = array_merge(
            $this->globalMiddleware,
            $this->middleware[$route] ?? []
        );

        // Build middleware pipeline using functional composition
        // array_reverse() ensures middleware executes in registration order
        // array_reduce() builds nested closures from right to left
        $pipeline = array_reduce(
            array_reverse($allMiddleware),
            function ($next, $middleware) {
                // Return closure that calls middleware->handle()
                return function ($request) use ($middleware, $next) {
                    return $middleware->handle($request, $next);
                };
            },
            $final // Start with final callable (controller)
        );

        // Execute the pipeline
        return $pipeline($request);
    }

    // ========================================
    // Inspection
    // ========================================

    /**
     * Get all middleware for specific route
     * 
     * Returns combined list of global and route-specific middleware.
     * Useful for debugging and testing middleware configuration.
     * 
     * @param string $route Route path
     * @return array Array of MiddlewareInterface instances
     * 
     * @example
     * // Inspect middleware for route
     * $middleware = $manager->getMiddlewareForRoute('/admin');
     * foreach ($middleware as $mw) {
     *     echo get_class($mw) . "\n";
     * }
     */
    public function getMiddlewareForRoute(string $route): array
    {
        // Return combined list: global + route-specific
        return array_merge(
            $this->globalMiddleware,
            $this->middleware[$route] ?? []
        );
    }
}




