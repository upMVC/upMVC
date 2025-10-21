<?php
/**
 * MiddlewareInterface.php - Middleware Contract
 * 
 * This interface defines the contract that all middleware components must implement.
 * It establishes the middleware pattern used throughout upMVC for request processing.
 * 
 * Middleware Pattern:
 * Middleware creates a chain of request processors, each handling a specific concern:
 * - Authentication (check user credentials)
 * - Logging (record request details)
 * - CORS (handle cross-origin requests)
 * - Rate limiting (throttle requests)
 * - Input validation (sanitize data)
 * - Error handling (catch exceptions)
 * 
 * Chain Flow:
 * Request → Middleware1 → Middleware2 → Middleware3 → Controller → Response
 *         ← Middleware1 ← Middleware2 ← Middleware3 ← Controller ←
 * 
 * Each middleware can:
 * 1. Process request before passing to next
 * 2. Pass request to next middleware
 * 3. Process response after next middleware returns
 * 4. Short-circuit chain (return early, e.g., redirect)
 * 
 * Implementation Guidelines:
 * - Always call $next($request) to continue chain (unless short-circuiting)
 * - Perform pre-processing before calling $next()
 * - Perform post-processing after calling $next()
 * - Return the result from $next() or your own response
 * - Use try-catch if you need to handle exceptions
 * 
 * Built-in Middleware:
 * - AuthMiddleware: Route protection with authentication
 * - CorsMiddleware: Cross-origin resource sharing headers
 * - LoggingMiddleware: Request/response logging with metrics
 * 
 * @package upMVC\Middleware
 * @author BitsHost
 * @copyright 2025 BitsHost
 * @license MIT License
 * @link https://bitshost.biz/
 * @created October 11, 2025
 */

namespace upMVC\Middleware;

interface MiddlewareInterface
{
    /**
     * Handle the request and pass to next middleware in chain
     * 
     * This method is the core of the middleware pattern. It receives:
     * - The request data (route, method, parameters, etc.)
     * - A callable representing the next middleware/controller
     * 
     * The middleware can:
     * - Inspect/modify request before calling $next()
     * - Call $next($request) to continue the chain
     * - Inspect/modify response after $next() returns
     * - Return early to short-circuit the chain (e.g., redirect, error)
     * 
     * Request Array Structure:
     * [
     *   'route' => '/path/to/route',
     *   'method' => 'GET|POST|PUT|DELETE',
     *   'uri' => '/full/request/uri',
     *   'params' => [...],
     *   ...additional data
     * ]
     * 
     * @param array $request Request data containing route, method, uri, params, etc.
     * @param callable $next Next middleware in chain (or final controller)
     * @return mixed Response from next middleware/controller, or early return value
     * 
     * @example
     * // Basic middleware implementation
     * public function handle(array $request, callable $next) {
     *     // Pre-processing
     *     echo "Before next middleware\n";
     *     
     *     // Continue chain
     *     $response = $next($request);
     *     
     *     // Post-processing
     *     echo "After next middleware\n";
     *     
     *     return $response;
     * }
     * 
     * @example
     * // Short-circuit example (redirect)
     * public function handle(array $request, callable $next) {
     *     if (!$this->isAuthenticated()) {
     *         header('Location: /login');
     *         exit; // Don't call $next() - chain stops here
     *     }
     *     return $next($request);
     * }
     * 
     * @example
     * // Exception handling middleware
     * public function handle(array $request, callable $next) {
     *     try {
     *         return $next($request);
     *     } catch (\Exception $e) {
     *         // Log error and return error response
     *         $this->logError($e);
     *         return ['error' => $e->getMessage()];
     *     }
     * }
     * 
     * @example
     * // Request modification
     * public function handle(array $request, callable $next) {
     *     // Add timestamp to request
     *     $request['timestamp'] = time();
     *     
     *     // Pass modified request
     *     return $next($request);
     * }
     */
    public function handle(array $request, callable $next);
}