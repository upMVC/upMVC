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

/*
 * ---------------------------------------------------------------------------
 * Event taxonomy — PARKED, not implemented.
 *
 * The kernel does not dispatch any of these yet: nothing binds a shared
 * EventDispatcher, so there is no application-wide bus to publish to.
 *
 * Kept here as the intended taxonomy. To activate one, give it its own file
 * (PSR-4 autoloads one class per file — declaring it below leaves it
 * unreachable) and add the dispatch call at the site named beside it.
 *
 * UserRegistered is already live in UserRegistered.php.
 *
 * User events
 *   UserLoggedIn        src/Modules/Auth, pack Api/Modules/Auth
 *   UserLoggedOut       src/Modules/Auth, pack Api/Modules/Auth
 *   UserUpdated         src/Modules/User
 *   UserDeleted         src/Modules/User
 *
 * System events
 *   RequestStarted      src/Etc/Start.php  (upMVC())
 *   RequestCompleted    src/Etc/Start.php  (upMVC())
 *   ErrorOccurred       src/Etc/ErrorHandler.php
 *   CacheCleared        src/Etc/Cache/CacheManager.php  (forget/clearAll)
 *   ConfigLoaded        src/Etc/Config/ConfigManager.php  (load)
 *
 * Module events
 *   ModuleLoaded        src/Etc/InitModsImproved.php
 *   ModuleInstalled     no install mechanism exists yet
 *   ModuleUninstalled   no install mechanism exists yet
 *
 * Database events
 *   DatabaseConnected   src/Etc/Database.php
 *   QueryExecuted       src/Etc/Database.php
 *   ModelCreated        src/Common/Bmvc/BaseModel.php
 *   ModelUpdated        src/Common/Bmvc/BaseModel.php
 *   ModelDeleted        src/Common/Bmvc/BaseModel.php
 * ---------------------------------------------------------------------------
 */




