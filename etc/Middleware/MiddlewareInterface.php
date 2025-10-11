<?php
/*
 *   Created on October 11, 2025
 *   Copyright (c) 2025 BitsHost
 *   All rights reserved.
 *
 *   Enhanced upMVC - Middleware System
 */

namespace upMVC\Middleware;

/**
 * Middleware Interface
 * 
 * Defines the contract for all middleware components
 */
interface MiddlewareInterface
{
    /**
     * Handle the request and optionally pass to next middleware
     *
     * @param array $request Request data
     * @param callable $next Next middleware in chain
     * @return mixed
     */
    public function handle(array $request, callable $next);
}