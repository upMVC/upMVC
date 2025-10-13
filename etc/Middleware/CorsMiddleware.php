<?php
/*
 *   Created on October 11, 2025
 *   Copyright (c) 2025 BitsHost
 *   All rights reserved.
 *
 *   Enhanced upMVC - CORS Middleware
 */

namespace upMVC\Middleware;

use upMVC\Middleware\MiddlewareInterface;

/**
 * CorsMiddleware
 * 
 * Handles Cross-Origin Resource Sharing (CORS) headers
 */
class CorsMiddleware implements MiddlewareInterface
{
    /**
     * @var array
     */
    private array $allowedOrigins;

    /**
     * @var array
     */
    private array $allowedMethods;

    /**
     * @var array
     */
    private array $allowedHeaders;

    /**
     * @var bool
     */
    private bool $allowCredentials;

    /**
     * @var int
     */
    private int $maxAge;

    public function __construct(array $config = [])
    {
        $this->allowedOrigins = $config['origins'] ?? ['*'];
        $this->allowedMethods = $config['methods'] ?? ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'];
        $this->allowedHeaders = $config['headers'] ?? ['Content-Type', 'Authorization', 'X-Requested-With'];
        $this->allowCredentials = $config['credentials'] ?? false;
        $this->maxAge = $config['max_age'] ?? 86400; // 24 hours
    }

    /**
     * Handle CORS headers
     *
     * @param array $request
     * @param callable $next
     * @return mixed
     */
    public function handle(array $request, callable $next)
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $method = $request['method'] ?? 'GET';

        // Set CORS headers
        $this->setCorsHeaders($origin);

        // Handle preflight requests
        if ($method === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        return $next($request);
    }

    /**
     * Set CORS headers
     *
     * @param string $origin
     * @return void
     */
    private function setCorsHeaders(string $origin): void
    {
        // Check if origin is allowed
        if ($this->isOriginAllowed($origin)) {
            header('Access-Control-Allow-Origin: ' . $origin);
        } elseif (in_array('*', $this->allowedOrigins)) {
            header('Access-Control-Allow-Origin: *');
        }

        // Set other CORS headers
        header('Access-Control-Allow-Methods: ' . implode(', ', $this->allowedMethods));
        header('Access-Control-Allow-Headers: ' . implode(', ', $this->allowedHeaders));
        header('Access-Control-Max-Age: ' . $this->maxAge);

        if ($this->allowCredentials) {
            header('Access-Control-Allow-Credentials: true');
        }
    }

    /**
     * Check if origin is allowed
     *
     * @param string $origin
     * @return bool
     */
    private function isOriginAllowed(string $origin): bool
    {
        return in_array($origin, $this->allowedOrigins);
    }
}