<?php

namespace upMVC;

class ErrorHandler
{
    private static $logPath = 'logs/';
    
    public static function register(): void
    {
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleFatalError']);
    }
    
    public static function handleError($severity, $message, $file, $line): bool
    {
        if (!(error_reporting() & $severity)) return false;
        
        $error = [
            'type' => 'Error',
            'severity' => $severity,
            'message' => $message,
            'file' => $file,
            'line' => $line,
            'time' => date('Y-m-d H:i:s')
        ];
        
        self::log($error);
        
        if (Config::get('debug', false)) {
            self::displayError($error);
        }
        
        return true;
    }
    
    public static function handleException($exception): void
    {
        $error = [
            'type' => 'Exception',
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'time' => date('Y-m-d H:i:s')
        ];
        
        self::log($error);
        
        if (Config::get('debug', false)) {
            self::displayError($error);
        } else {
            include_once './common/500.php';
        }
    }
    
    public static function handleFatalError(): void
    {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR])) {
            self::handleError($error['type'], $error['message'], $error['file'], $error['line']);
        }
    }
    
    private static function log(array $error): void
    {
        if (!is_dir(self::$logPath)) {
            mkdir(self::$logPath, 0755, true);
        }
        
        $logFile = self::$logPath . 'error_' . date('Y-m-d') . '.log';
        $logEntry = json_encode($error) . PHP_EOL;
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    private static function displayError(array $error): void
    {
        echo "<div style='background:#f8d7da;color:#721c24;padding:15px;margin:10px;border:1px solid #f5c6cb;border-radius:4px;'>";
        echo "<h4>{$error['type']}: {$error['message']}</h4>";
        echo "<p><strong>File:</strong> {$error['file']} <strong>Line:</strong> {$error['line']}</p>";
        if (isset($error['trace'])) {
            echo "<details><summary>Stack Trace</summary><pre>{$error['trace']}</pre></details>";
        }
        echo "</div>";
    }
}