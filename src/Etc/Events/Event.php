<?php
/*
 *   Created on October 11, 2025
 *   Copyright (c) 2025 BitsHost
 *   All rights reserved.
 *
 *   Enhanced upMVC - Event System
 */

namespace App\Etc\Events;

/**
 * Event
 * 
 * Base event class
 */
abstract class Event
{
    /**
     * @var bool
     */
    private bool $propagationStopped = false;

    /**
     * @var array
     */
    protected array $data = [];

    /**
     * @var float
     */
    private float $timestamp;

    public function __construct(array $data = [])
    {
        $this->data = $data;
        $this->timestamp = microtime(true);
    }

    /**
     * Stop event propagation
     *
     * @return void
     */
    public function stopPropagation(): void
    {
        $this->propagationStopped = true;
    }

    /**
     * Check if propagation is stopped
     *
     * @return bool
     */
    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }

    /**
     * Get event data
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Set event data
     *
     * @param array $data
     * @return void
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * Get specific data by key
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Set specific data by key
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $key, $value): void
    {
        $this->data[$key] = $value;
    }

    /**
     * Get event timestamp
     *
     * @return float
     */
    public function getTimestamp(): float
    {
        return $this->timestamp;
    }

    /**
     * Get event name
     *
     * @return string
     */
    public function getName(): string
    {
        return get_class($this);
    }
}

/**
 * User Events
 */
class UserRegistered extends Event {}
class UserLoggedIn extends Event {}
class UserLoggedOut extends Event {}
class UserUpdated extends Event {}
class UserDeleted extends Event {}

/**
 * System Events
 */
class RequestStarted extends Event {}
class RequestCompleted extends Event {}
class ErrorOccurred extends Event {}
class CacheCleared extends Event {}
class ConfigLoaded extends Event {}

/**
 * Module Events
 */
class ModuleLoaded extends Event {}
class ModuleInstalled extends Event {}
class ModuleUninstalled extends Event {}

/**
 * Database Events
 */
class DatabaseConnected extends Event {}
class QueryExecuted extends Event {}
class ModelCreated extends Event {}
class ModelUpdated extends Event {}
class ModelDeleted extends Event {}




