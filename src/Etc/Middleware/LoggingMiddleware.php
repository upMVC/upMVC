<?php
/**
 * LoggingMiddleware.php - Request Logging Middleware
 * 
 * This middleware logs all HTTP requests with performance metrics:
 * - Request route and HTTP method
 * - Client IP address and user agent
 * - Execution time in milliseconds
 * - Success/error status
 * - Exception details for errors
 * 
 * Features:
 * - Automatic log directory creation
 * - JSON-formatted log entries
 * - File locking for concurrent writes
 * - Execution time tracking
 * - Exception capture and re-throw
 * - Enable/disable flag
 * 
 * Log Format:
 * [YYYY-MM-DD HH:MM:SS] LEVEL: {"route":"...","method":"...","ip":"..."}
 * 
 * Use Cases:
 * - Performance monitoring
 * - Debugging request flows
 * - Security audit trail
 * - API usage analytics
 * - Error tracking
 * 
 * Performance Impact:
 * Minimal overhead (~1-2ms) for logging operations.
 * Uses file locking to ensure data integrity.
 * 
 * Security Note:
 * Log files may contain sensitive information (IPs, routes, errors).
 * Ensure logs directory is protected and not web-accessible.
 * Consider log rotation for production environments.
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

class LoggingMiddleware implements MiddlewareInterface
{
    // ========================================
    // Properties
    // ========================================
    
    /**
     * Full path to log file
     * 
     * Stores request logs in JSON format.
     * 
     * @var string
     */
    private string $logFile;

    /**
     * Enable/disable logging
     * 
     * When false, middleware passes through without logging.
     * Useful for disabling logs in development or specific environments.
     * 
     * @var bool
     */
    private bool $logEnabled;

    // ========================================
    // Initialization
    // ========================================

    /**
     * Constructor - Initialize logging middleware
     * 
     * Sets up log file path and creates directory if needed.
     * 
     * @param string $logFile Relative path to log file (default: 'logs/requests.log')
     * @param bool $logEnabled Enable logging (default: true)
     * 
     * @example
     * // Default configuration
     * $logger = new LoggingMiddleware();
     * 
     * @example
     * // Custom log file
     * $logger = new LoggingMiddleware('logs/api-requests.log');
     * 
     * @example
     * // Disabled logging for testing
     * $logger = new LoggingMiddleware('logs/requests.log', false);
     */
    public function __construct(string $logFile = 'logs/requests.log', bool $logEnabled = true)
    {
        // Build full path to log file
        $baseDir = defined('THIS_DIR') ? THIS_DIR : dirname(__DIR__, 2);
        $this->logFile = $baseDir . '/' . $logFile;
        $this->logEnabled = $logEnabled;
        
        // Ensure logs directory exists
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    // ========================================
    // Middleware Handler
    // ========================================

    /**
     * Handle request logging with performance tracking
     * 
     * Logs request start, executes next middleware, logs completion.
     * Captures execution time and any exceptions thrown.
     * 
     * @param array $request Request data (route, method, etc.)
     * @param callable $next Next middleware in chain
     * @return mixed Next middleware result
     * @throws \Exception Re-throws any exception after logging
     * 
     * @example
     * // Middleware chain usage
     * $result = $loggingMiddleware->handle($request, function($req) {
     *     return $controller->action();
     * });
     */
    public function handle(array $request, callable $next)
    {
        // Skip logging if disabled
        if (!$this->logEnabled) {
            return $next($request);
        }

        // Capture request start time
        $startTime = microtime(true);
        
        // Extract request information
        $route = $request['route'] ?? 'unknown';
        $method = $request['method'] ?? 'unknown';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        try {
            // Execute next middleware/controller
            $response = $next($request);
            
            // Calculate execution time in milliseconds
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            
            // Log successful request
            $this->log('INFO', [
                'route' => $route,
                'method' => $method,
                'ip' => $ip,
                'execution_time_ms' => $executionTime,
                'status' => 'success',
                'user_agent' => $userAgent
            ]);

            return $response;
            
        } catch (\Exception $e) {
            // Calculate execution time even on error
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            
            // Log error with exception details
            $this->log('ERROR', [
                'route' => $route,
                'method' => $method,
                'ip' => $ip,
                'execution_time_ms' => $executionTime,
                'status' => 'error',
                'error' => $e->getMessage(),
                'user_agent' => $userAgent
            ]);

            // Re-throw exception for error handler
            throw $e;
        }
    }

    // ========================================
    // Logging
    // ========================================

    /**
     * Write log entry to file
     * 
     * Formats log entry with timestamp, level, and JSON data.
     * Uses file locking for thread-safe writes.
     * 
     * @param string $level Log level (INFO, ERROR, etc.)
     * @param array $data Log data to encode as JSON
     * @return void
     * 
     * @example
     * // Log entry format:
     * // [2025-10-21 14:30:45] INFO: {"route":"/api/users","method":"GET","ip":"192.168.1.1","execution_time_ms":45.23,"status":"success"}
     */
    private function log(string $level, array $data): void
    {
        // Format timestamp
        $timestamp = date('Y-m-d H:i:s');
        
        // Build log entry: [timestamp] LEVEL: {json}
        $logEntry = sprintf(
            "[%s] %s: %s\n",
            $timestamp,
            $level,
            json_encode($data, JSON_UNESCAPED_SLASHES)
        );

        // Write with file locking for concurrent safety
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
}




