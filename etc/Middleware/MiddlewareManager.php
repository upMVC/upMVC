<?php
/*
 *   Created on October 11, 2025
 *   Copyright (c) 2025 BitsHost
 *   All rights reserved.
 *
 *   Enhanced upMVC - Middleware Manager
 */

namespace upMVC\Middleware;

use upMVC\Middleware\MiddlewareInterface;

/**
 * MiddlewareManager
 * 
 * Manages and executes middleware pipeline
 */
class MiddlewareManager
{
    /**
     * @var array
     */
    private array $middleware = [];

    /**
     * @var array
     */
    private array $globalMiddleware = [];

    /**
     * Add global middleware (runs on all routes)
     *
     * @param MiddlewareInterface $middleware
     * @return self
     */
    public function addGlobal(MiddlewareInterface $middleware): self
    {
        $this->globalMiddleware[] = $middleware;
        return $this;
    }

    /**
     * Add middleware for specific route
     *
     * @param string $route
     * @param MiddlewareInterface $middleware
     * @return self
     */
    public function addForRoute(string $route, MiddlewareInterface $middleware): self
    {
        if (!isset($this->middleware[$route])) {
            $this->middleware[$route] = [];
        }
        $this->middleware[$route][] = $middleware;
        return $this;
    }

    /**
     * Execute middleware pipeline
     *
     * @param string $route
     * @param array $request
     * @param callable $final
     * @return mixed
     */
    public function execute(string $route, array $request, callable $final)
    {
        // Combine global and route-specific middleware
        $allMiddleware = array_merge(
            $this->globalMiddleware,
            $this->middleware[$route] ?? []
        );

        // Create middleware pipeline
        $pipeline = array_reduce(
            array_reverse($allMiddleware),
            function ($next, $middleware) {
                return function ($request) use ($middleware, $next) {
                    return $middleware->handle($request, $next);
                };
            },
            $final
        );

        return $pipeline($request);
    }

    /**
     * Get middleware for route
     *
     * @param string $route
     * @return array
     */
    public function getMiddlewareForRoute(string $route): array
    {
        return array_merge(
            $this->globalMiddleware,
            $this->middleware[$route] ?? []
        );
    }
}