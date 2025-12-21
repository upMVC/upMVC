<?php
/*
 *   Created on October 11, 2025
 *   Copyright (c) 2025 BitsHost
 *   All rights reserved.
 *
 *   Enhanced upMVC - Service Provider Interface
 */

namespace App\Etc\Container;

/**
 * ServiceProviderInterface
 * 
 * Interface for service providers
 */
interface ServiceProviderInterface
{
    /**
     * Register services in the container
     *
     * @param Container $container
     * @return void
     */
    public function register(Container $container): void;

    /**
     * Boot services (called after all providers are registered)
     *
     * @param Container $container
     * @return void
     */
    public function boot(Container $container): void;
}




