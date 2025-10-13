<?php
/*
 *   Created on October 11, 2025
 *   Copyright (c) 2025 BitsHost
 *   All rights reserved.
 *
 *   Enhanced upMVC - Error Handler
 */

namespace upMVC\Exceptions;

use upMVC\Exceptions\upMVCException;
use Exception;
use Throwable;

/**
 * ErrorHandler
 * 
 * Global error and exception handler
 */
class ErrorHandler
{
    /**
     * @var bool
     */
    private bool $debug;

    /**
     * @var string
     */
    private string $logFile;

    /**
     * @var array
     */
    private array $errorViews;

    public function __construct(bool $debug = false, string $logFile = 'logs/errors.log')
    {
        $this->debug = $debug;
        $baseDir = defined('THIS_DIR') ? THIS_DIR : dirname(__DIR__, 2);
        $this->logFile = $baseDir . '/' . $logFile;
        $baseDir = defined('THIS_DIR') ? THIS_DIR : dirname(__DIR__, 2);
        $this->errorViews = [
            404 => $baseDir . '/common/errors/404.php',
            500 => $baseDir . '/common/errors/500.php',
            403 => $baseDir . '/common/errors/403.php'
        ];

        // Create logs directory if it doesn't exist
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    /**
     * Register error handlers
     *
     * @return void
     */
    public function register(): void
    {
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);
    }

    /**
     * Handle PHP errors
     *
     * @param int $level
     * @param string $message
     * @param string $file
     * @param int $line
     * @return bool
     */
    public function handleError(int $level, string $message, string $file = '', int $line = 0): bool
    {
        if (!(error_reporting() & $level)) {
            return false;
        }

        $this->logError([
            'type' => 'PHP_ERROR',
            'level' => $this->getErrorLevelName($level),
            'message' => $message,
            'file' => $file,
            'line' => $line,
            'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)
        ]);

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
     * @param Throwable $exception
     * @return void
     */
    public function handleException(Throwable $exception): void
    {
        $this->logError([
            'type' => 'EXCEPTION',
            'class' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTrace(),
            'context' => $exception instanceof upMVCException ? $exception->getContext() : []
        ]);

        if ($exception instanceof upMVCException) {
            $this->handleupMVCException($exception);
        } else {
            $this->handleGenericException($exception);
        }
    }

    /**
     * Handle shutdown errors (fatal errors)
     *
     * @return void
     */
    public function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
            $this->logError([
                'type' => 'FATAL_ERROR',
                'level' => $this->getErrorLevelName($error['type']),
                'message' => $error['message'],
                'file' => $error['file'],
                'line' => $error['line']
            ]);

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

    /**
     * Handle upMVC specific exceptions
     *
     * @param upMVCException $exception
     * @return void
     */
    private function handleupMVCException(upMVCException $exception): void
    {
        $statusCode = $exception->getHttpStatusCode();
        http_response_code($statusCode);

        if ($this->debug) {
            $this->displayError([
                'type' => $exception->getErrorType(),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'context' => $exception->getContext(),
                'trace' => $exception->getTrace()
            ]);
        } else {
            $this->renderErrorPage($statusCode);
        }
    }

    /**
     * Handle generic exceptions
     *
     * @param Throwable $exception
     * @return void
     */
    private function handleGenericException(Throwable $exception): void
    {
        http_response_code(500);

        if ($this->debug) {
            $this->displayError([
                'type' => 'Exception',
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTrace()
            ]);
        } else {
            $this->renderErrorPage(500);
        }
    }

    /**
     * Log error to file
     *
     * @param array $errorData
     * @return void
     */
    private function logError(array $errorData): void
    {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'url' => $_SERVER['REQUEST_URI'] ?? 'CLI',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'CLI',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        ] + $errorData;

        $logLine = json_encode($logEntry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
        file_put_contents($this->logFile, $logLine, FILE_APPEND | LOCK_EX);
    }

    /**
     * Display error for debug mode
     *
     * @param array $errorData
     * @return void
     */
    private function displayError(array $errorData): void
    {
        if (!headers_sent()) {
            header('Content-Type: text/html; charset=utf-8');
        }

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
        
        if (isset($errorData['context']) && !empty($errorData['context'])) {
            echo "<p><strong>Context:</strong></p>";
            echo "<div class='trace'><pre>" . htmlspecialchars(print_r($errorData['context'], true)) . "</pre></div>";
        }
        
        if (isset($errorData['trace'])) {
            echo "<p><strong>Stack Trace:</strong></p>";
            echo "<div class='trace'><pre>" . htmlspecialchars($this->formatTrace($errorData['trace'])) . "</pre></div>";
        }
        
        echo "</div></body></html>";
    }

    /**
     * Render error page
     *
     * @param int $statusCode
     * @return void
     */
    private function renderErrorPage(int $statusCode): void
    {
        $viewFile = $this->errorViews[$statusCode] ?? $this->errorViews[500];
        
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            echo "<!DOCTYPE html>\n";
            echo "<html><head><title>Error {$statusCode}</title></head>";
            echo "<body><h1>Error {$statusCode}</h1>";
            echo "<p>An error occurred while processing your request.</p></body></html>";
        }
    }

    /**
     * Get error level name
     *
     * @param int $level
     * @return string
     */
    private function getErrorLevelName(int $level): string
    {
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
     * Format stack trace for display
     *
     * @param array $trace
     * @return string
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

    /**
     * Set custom error view
     *
     * @param int $statusCode
     * @param string $viewFile
     * @return void
     */
    public function setErrorView(int $statusCode, string $viewFile): void
    {
        $this->errorViews[$statusCode] = $viewFile;
    }
}