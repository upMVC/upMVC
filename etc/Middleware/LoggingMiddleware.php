<?php
/*
 *   Created on October 11, 2025
 *   Copyright (c) 2025 BitsHost
 *   All rights reserved.
 *
 *   Enhanced upMVC - Logging Middleware
 */

namespace upMVC\Middleware;

use upMVC\Middleware\MiddlewareInterface;

/**
 * LoggingMiddleware
 * 
 * Logs request information and execution time
 */
class LoggingMiddleware implements MiddlewareInterface
{
    /**
     * @var string
     */
    private string $logFile;

    /**
     * @var bool
     */
    private bool $logEnabled;

    public function __construct(string $logFile = 'logs/requests.log', bool $logEnabled = true)
    {
        $baseDir = defined('THIS_DIR') ? THIS_DIR : dirname(__DIR__, 2);
        $this->logFile = $baseDir . '/' . $logFile;
        $this->logEnabled = $logEnabled;
        
        // Create logs directory if it doesn't exist
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    /**
     * Handle request logging
     *
     * @param array $request
     * @param callable $next
     * @return mixed
     */
    public function handle(array $request, callable $next)
    {
        if (!$this->logEnabled) {
            return $next($request);
        }

        $startTime = microtime(true);
        $route = $request['route'] ?? 'unknown';
        $method = $request['method'] ?? 'unknown';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        try {
            $response = $next($request);
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            
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
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            
            $this->log('ERROR', [
                'route' => $route,
                'method' => $method,
                'ip' => $ip,
                'execution_time_ms' => $executionTime,
                'status' => 'error',
                'error' => $e->getMessage(),
                'user_agent' => $userAgent
            ]);

            throw $e;
        }
    }

    /**
     * Write log entry
     *
     * @param string $level
     * @param array $data
     * @return void
     */
    private function log(string $level, array $data): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = sprintf(
            "[%s] %s: %s\n",
            $timestamp,
            $level,
            json_encode($data, JSON_UNESCAPED_SLASHES)
        );

        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
}