<?php
/**
 * ErrorHandler.php - Static Error and Exception Handler
 * 
 * This class provides a simplified static error handling system for upMVC:
 * - Static method-based API (no instantiation required)
 * - PHP error handling (warnings, notices, etc.)
 * - Uncaught exception handling
 * - Fatal error handling (shutdown function)
 * - Daily log rotation (error_YYYY-MM-DD.log)
 * - Debug mode error display
 * 
 * Features:
 * - Automatic log directory creation
 * - JSON log format for easy parsing
 * - Styled error display in debug mode
 * - Integration with upMVC\Config for debug flag
 * - Daily log file rotation
 * 
 * Debug Mode:
 * - Enabled: Shows styled error boxes with stack traces
 * - Disabled: Shows 500 error page for exceptions
 * 
 * Usage:
 * Call ErrorHandler::register() once during bootstrap.
 * 
 * @package upMVC
 * @author BitsHost
 * @copyright 2025 BitsHost
 * @license MIT License
 * @link https://bitshost.biz/
 * @created October 21, 2025
 */

namespace App\Etc;

class ErrorHandler
{
    // ========================================
    // Properties
    // ========================================
    
    /**
     * Path to logs directory
     * 
     * Daily log files are created here (error_YYYY-MM-DD.log).
     * 
     * @var string
     */
    private static $logPath = 'logs/';
    
    // ========================================
    // Registration
    // ========================================

    /**
     * Override the default logs directory path.
     *
     * Accepts absolute or relative paths. A trailing directory
     * separator is automatically added when missing.
     *
     * @param string $path
     * @return void
     */
    public static function setLogPath(string $path): void
    {
        $path = rtrim($path, "\\/");
        if ($path === '') {
            $path = 'logs';
        }
        self::$logPath = $path . DIRECTORY_SEPARATOR;
    }
    
    /**
     * Register global error handlers
     * 
     * Registers three types of handlers:
     * - Error handler: PHP errors (warnings, notices, etc.)
     * - Exception handler: Uncaught exceptions
     * - Shutdown function: Fatal errors
     * 
     * Call this once during application bootstrap.
     *
     * @return void
     * 
     * @example
     * // In Start.php or index.php
     * ErrorHandler::register();
     */
    public static function register(): void
    {
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleFatalError']);
    }
    
    // ========================================
    // Error Handlers
    // ========================================
    
    /**
     * Handle PHP errors (warnings, notices, etc.)
     * 
     * Logs error and displays if debug mode is enabled.
     * Respects error_reporting() settings.
     * 
     * @param int $severity Error severity level (E_WARNING, E_NOTICE, etc.)
     * @param string $message Error message
     * @param string $file File where error occurred
     * @param int $line Line number where error occurred
     * @return bool True to prevent PHP's internal error handler
     * 
     * @example
     * // Triggered automatically by PHP
     * trigger_error("Something went wrong", E_USER_WARNING);
     */
    public static function handleError($severity, $message, $file, $line): bool
    {
        // Respect error_reporting settings
        if (!(error_reporting() & $severity)) return false;
        
        // Build error data structure
        $error = [
            'type' => 'Error',
            'severity' => $severity,
            'message' => $message,
            'file' => $file,
            'line' => $line,
            'time' => date('Y-m-d H:i:s')
        ];
        
        // Log error to daily file
        self::log($error);
        
        // Display in debug mode
        if (Config::get('debug', false)) {
            self::displayError($error);
        }
        
        return true;
    }
    
    /**
     * Handle uncaught exceptions
     * 
     * Logs exception and displays appropriate error page or debug info.
     * Shows 500.php error page in production mode.
     * 
     * @param \Throwable $exception Uncaught exception
     * @return void
     * 
     * @example
     * // Triggered automatically by PHP
     * throw new Exception("Database connection failed");
     */
    public static function handleException($exception): void
    {
        // Build error data with stack trace
        $error = [
            'type' => 'Exception',
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'time' => date('Y-m-d H:i:s')
        ];
        
        // Log exception
        self::log($error);
        
        // Display based on debug mode
        if (Config::get('debug', false)) {
            self::displayError($error);
        } else {
            // Show production error page
            include_once './Common/500.php';
        }
    }
    
    /**
     * Handle fatal errors during shutdown
     * 
     * Catches fatal errors that can't be caught by normal error handler.
     * Delegates to handleError() for consistent logging/display.
     * 
     * @return void
     */
    public static function handleFatalError(): void
    {
        $error = error_get_last();
        
        // Check if it's a fatal error
        if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR])) {
            self::handleError($error['type'], $error['message'], $error['file'], $error['line']);
        }
    }
    
    // ========================================
    // Logging
    // ========================================
    
    /**
     * Log error to daily file in JSON format
     * 
     * Creates log directory if it doesn't exist.
     * Each day gets a new log file: error_YYYY-MM-DD.log
     * Uses file locking for thread safety.
     * 
     * @param array $error Error data to log
     * @return void
     */
    private static function log(array $error): void
    {
        // Ensure log directory exists
        if (!is_dir(self::$logPath)) {
            mkdir(self::$logPath, 0755, true);
        }
        
        // Daily log file rotation
        $logFile = self::$logPath . 'error_' . date('Y-m-d') . '.log';
        
        // Write JSON line with file locking
        $logEntry = json_encode($error) . PHP_EOL;
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    // ========================================
    // Display
    // ========================================
    
    /**
     * Display styled error for debug mode
     * 
     * Outputs inline HTML error box with details.
     * Only used when debug mode is enabled.
     * 
     * @param array $error Error data to display
     * @return void
     */
    private static function displayError(array $error): void
    {
        // Inline styled error box
        echo "<div style='background:#f8d7da;color:#721c24;padding:15px;margin:10px;border:1px solid #f5c6cb;border-radius:4px;'>";
        echo "<h4>{$error['type']}: {$error['message']}</h4>";
        echo "<p><strong>File:</strong> {$error['file']} <strong>Line:</strong> {$error['line']}</p>";
        
        // Show stack trace if available (exceptions)
        if (isset($error['trace'])) {
            echo "<details><summary>Stack Trace</summary><pre>{$error['trace']}</pre></details>";
        }
        echo "</div>";
    }
}





