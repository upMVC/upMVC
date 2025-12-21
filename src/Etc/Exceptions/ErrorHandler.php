<?php
/**
 * ErrorHandler.php - Global Error and Exception Handler
 * 
 * This class provides comprehensive error handling for upMVC:
 * - PHP error handling (warnings, notices, etc.)
 * - Uncaught exception handling
 * - Fatal error handling (shutdown function)
 * - Structured error logging (JSON format)
 * - Debug mode error display
 * - Custom error pages (403, 404, 500)
 * 
 * Features:
 * - Automatic log directory creation
 * - Stack trace formatting
 * - Context preservation for upMVCException
 * - Request metadata logging (URL, IP, user agent)
 * - Configurable error views per status code
 * 
 * Debug Mode:
 * - Enabled: Shows detailed error information with stack traces
 * - Disabled: Shows custom error pages (production safe)
 * 
 * @package upMVC\Exceptions
 * @author BitsHost
 * @copyright 2025 BitsHost
 * @license MIT License
 * @link https://bitshost.biz/
 * @created October 11, 2025
 */

namespace App\Etc\Exceptions;

use App\Etc\Exceptions\upMVCException;
use Exception;
use Throwable;

class ErrorHandler
{
    // ========================================
    // Properties
    // ========================================
    
    /**
     * Debug mode flag
     * 
     * When true, displays detailed error information.
     * When false, shows custom error pages (production mode).
     * 
     * @var bool
     */
    private bool $debug;

    /**
     * Path to error log file
     * 
     * Errors are logged in JSON format for easy parsing.
     * 
     * @var string
     */
    private string $logFile;

    /**
     * Custom error view paths by HTTP status code
     * 
     * Maps status codes to error page templates.
     * 
     * @var array
     */
    private array $errorViews;

    // ========================================
    // Initialization
    // ========================================

    /**
     * Constructor - Initialize error handler
     * 
     * Sets up error logging and custom error views.
     * Automatically creates log directory if it doesn't exist.
     * 
     * @param bool $debug Enable debug mode (default: false)
     * @param string $logFile Path to log file (default: 'logs/errors.log')
     */
    public function __construct(bool $debug = false, string $logFile = 'logs/errors.log')
    {
        $this->debug = $debug;
        $baseDir = defined('THIS_DIR') ? THIS_DIR : dirname(__DIR__, 2);
        $this->logFile = $baseDir . '/' . $logFile;
        
        // Define default error views
        $this->errorViews = [
            404 => $baseDir . '/common/errors/404.php',
            500 => $baseDir . '/common/errors/500.php',
            403 => $baseDir . '/common/errors/403.php'
        ];

        // Ensure log directory exists
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    // ========================================
    // Registration
    // ========================================

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
     * $errorHandler = new ErrorHandler(true, 'logs/errors.log');
     * $errorHandler->register();
     */
    public function register(): void
    {
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);
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
     * @param int $level Error level (E_WARNING, E_NOTICE, etc.)
     * @param string $message Error message
     * @param string $file File where error occurred
     * @param int $line Line number where error occurred
     * @return bool True to prevent PHP's internal error handler
     */
    public function handleError(int $level, string $message, string $file = '', int $line = 0): bool
    {
        // Respect error_reporting settings
        if (!(error_reporting() & $level)) {
            return false;
        }

        // Log error with full context
        $this->logError([
            'type' => 'PHP_ERROR',
            'level' => $this->getErrorLevelName($level),
            'message' => $message,
            'file' => $file,
            'line' => $line,
            'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)
        ]);

        // Display error in debug mode
        if ($this->debug) {
            $this->displayError([
                'type' => 'PHP Error',
                'message' => $message,
                'file' => $file,
                'line' => $line
            ]);
        }

        return true;
    }

    /**
     * Handle uncaught exceptions
     * 
     * Logs exception and displays appropriate error page or debug info.
     * Handles upMVCException specially for HTTP status codes.
     * 
     * @param Throwable $exception Uncaught exception
     * @return void
     */
    public function handleException(Throwable $exception): void
    {
        // Log exception with full context
        $this->logError([
            'type' => 'EXCEPTION',
            'class' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTrace(),
            'context' => $exception instanceof upMVCException ? $exception->getContext() : []
        ]);

        // Handle based on exception type
        if ($exception instanceof upMVCException) {
            $this->handleupMVCException($exception);
        } else {
            $this->handleGenericException($exception);
        }
    }

    /**
     * Handle fatal errors during shutdown
     * 
     * Catches fatal errors that can't be caught by normal error handler.
     * Logs and displays error or custom error page.
     * 
     * @return void
     */
    public function handleShutdown(): void
    {
        $error = error_get_last();
        
        // Check if it's a fatal error
        if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
            // Log fatal error
            $this->logError([
                'type' => 'FATAL_ERROR',
                'level' => $this->getErrorLevelName($error['type']),
                'message' => $error['message'],
                'file' => $error['file'],
                'line' => $error['line']
            ]);

            // Display based on debug mode
            if ($this->debug) {
                $this->displayError([
                    'type' => 'Fatal Error',
                    'message' => $error['message'],
                    'file' => $error['file'],
                    'line' => $error['line']
                ]);
            } else {
                $this->renderErrorPage(500);
            }
        }
    }

    // ========================================
    // Exception Type Handlers
    // ========================================

    /**
     * Handle upMVC-specific exceptions
     * 
     * Extracts HTTP status code and custom context from upMVCException.
     * 
     * @param upMVCException $exception upMVC exception with HTTP status code
     * @return void
     */
    private function handleupMVCException(upMVCException $exception): void
    {
        $statusCode = $exception->getHttpStatusCode();
        http_response_code($statusCode);

        if ($this->debug) {
            // Show detailed error with context
            $this->displayError([
                'type' => $exception->getErrorType(),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'context' => $exception->getContext(),
                'trace' => $exception->getTrace()
            ]);
        } else {
            // Show custom error page
            $this->renderErrorPage($statusCode);
        }
    }

    /**
     * Handle generic exceptions
     * 
     * Handles any exception not derived from upMVCException.
     * Always returns HTTP 500 status code.
     * 
     * @param Throwable $exception Generic exception
     * @return void
     */
    private function handleGenericException(Throwable $exception): void
    {
        http_response_code(500);

        if ($this->debug) {
            // Show detailed error
            $this->displayError([
                'type' => 'Exception',
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTrace()
            ]);
        } else {
            // Show generic 500 error page
            $this->renderErrorPage(500);
        }
    }

    // ========================================
    // Logging
    // ========================================

    /**
     * Log error to file in JSON format
     * 
     * Logs error with request metadata for debugging.
     * Uses file locking for thread safety.
     * 
     * @param array $errorData Error information
     * @return void
     */
    private function logError(array $errorData): void
    {
        // Build log entry with request metadata
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'url' => $_SERVER['REQUEST_URI'] ?? 'CLI',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'CLI',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        ] + $errorData;

        // Write JSON line with file locking
        $logLine = json_encode($logEntry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
        file_put_contents($this->logFile, $logLine, FILE_APPEND | LOCK_EX);
    }

    // ========================================
    // Display
    // ========================================

    /**
     * Display detailed error for debug mode
     * 
     * Outputs styled HTML error page with full details.
     * Only used when debug mode is enabled.
     * 
     * @param array $errorData Error information to display
     * @return void
     */
    private function displayError(array $errorData): void
    {
        if (!headers_sent()) {
            header('Content-Type: text/html; charset=utf-8');
        }

        // Output styled error page
        echo "<!DOCTYPE html>\n";
        echo "<html><head><title>Error</title>";
        echo "<style>body{font-family:monospace;background:#f5f5f5;padding:20px;}";
        echo ".error{background:#fff;border-left:5px solid #dc3545;padding:20px;margin:10px 0;}";
        echo ".trace{background:#f8f9fa;padding:10px;margin:10px 0;overflow:auto;}</style>";
        echo "</head><body>";
        
        echo "<div class='error'>";
        echo "<h2>" . htmlspecialchars($errorData['type']) . "</h2>";
        echo "<p><strong>Message:</strong> " . htmlspecialchars($errorData['message']) . "</p>";
        echo "<p><strong>File:</strong> " . htmlspecialchars($errorData['file'] ?? 'unknown') . "</p>";
        echo "<p><strong>Line:</strong> " . ($errorData['line'] ?? 'unknown') . "</p>";
        
        // Show context if available
        if (isset($errorData['context']) && !empty($errorData['context'])) {
            echo "<p><strong>Context:</strong></p>";
            echo "<div class='trace'><pre>" . htmlspecialchars(print_r($errorData['context'], true)) . "</pre></div>";
        }
        
        // Show stack trace if available
        if (isset($errorData['trace'])) {
            echo "<p><strong>Stack Trace:</strong></p>";
            echo "<div class='trace'><pre>" . htmlspecialchars($this->formatTrace($errorData['trace'])) . "</pre></div>";
        }
        
        echo "</div></body></html>";
    }

    /**
     * Render custom error page (production mode)
     * 
     * Includes custom error view if available, otherwise shows generic error.
     * 
     * @param int $statusCode HTTP status code (403, 404, 500, etc.)
     * @return void
     */
    private function renderErrorPage(int $statusCode): void
    {
        $viewFile = $this->errorViews[$statusCode] ?? $this->errorViews[500];
        
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            // Generic fallback error page
            echo "<!DOCTYPE html>\n";
            echo "<html><head><title>Error {$statusCode}</title></head>";
            echo "<body><h1>Error {$statusCode}</h1>";
            echo "<p>An error occurred while processing your request.</p></body></html>";
        }
    }

    // ========================================
    // Helper Methods
    // ========================================

    /**
     * Get human-readable error level name
     * 
     * Converts PHP error constant to readable string.
     * 
     * @param int $level PHP error level constant
     * @return string Error level name (e.g., 'E_WARNING', 'E_NOTICE')
     */
    private function getErrorLevelName(int $level): string
    {
        // Map of PHP error constants to names
        $levels = [
            E_ERROR => 'E_ERROR',
            E_WARNING => 'E_WARNING',
            E_PARSE => 'E_PARSE',
            E_NOTICE => 'E_NOTICE',
            E_CORE_ERROR => 'E_CORE_ERROR',
            E_CORE_WARNING => 'E_CORE_WARNING',
            E_COMPILE_ERROR => 'E_COMPILE_ERROR',
            E_COMPILE_WARNING => 'E_COMPILE_WARNING',
            E_USER_ERROR => 'E_USER_ERROR',
            E_USER_WARNING => 'E_USER_WARNING',
            E_USER_NOTICE => 'E_USER_NOTICE',
            E_STRICT => 'E_STRICT',
            E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
            E_DEPRECATED => 'E_DEPRECATED',
            E_USER_DEPRECATED => 'E_USER_DEPRECATED',
        ];

        return $levels[$level] ?? 'UNKNOWN';
    }

    /**
     * Format stack trace for readable display
     * 
     * Converts array stack trace to formatted string.
     * 
     * @param array $trace Stack trace array from exception or debug_backtrace()
     * @return string Formatted multi-line stack trace
     */
    private function formatTrace(array $trace): string
    {
        $output = '';
        foreach ($trace as $i => $step) {
            $output .= "#{$i} ";
            if (isset($step['file'])) {
                $output .= $step['file'] . '(' . ($step['line'] ?? '?') . '): ';
            }
            if (isset($step['class'])) {
                $output .= $step['class'] . $step['type'];
            }
            $output .= $step['function'] . "()\n";
        }
        return $output;
    }

    // ========================================
    // Configuration
    // ========================================

    /**
     * Set custom error view for specific HTTP status code
     * 
     * Allows customization of error pages per status code.
     * 
     * @param int $statusCode HTTP status code (403, 404, 500, etc.)
     * @param string $viewFile Absolute path to error view file
     * @return void
     * 
     * @example
     * $errorHandler->setErrorView(404, '/path/to/custom-404.php');
     */
    public function setErrorView(int $statusCode, string $viewFile): void
    {
        $this->errorViews[$statusCode] = $viewFile;
    }
}




