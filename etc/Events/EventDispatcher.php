<?php
/*
 *   Created on October 11, 2025
 *   Copyright (c) 2025 BitsHost
 *   All rights reserved.
 *
 *   Enhanced upMVC - Event Dispatcher
 */

namespace upMVC\Events;

use upMVC\Events\Event;

/**
 * EventDispatcher
 * 
 * Event dispatcher for publish-subscribe pattern
 */
class EventDispatcher
{
    /**
     * @var array
     */
    private array $listeners = [];

    /**
     * @var array
     */
    private array $wildcardListeners = [];

    /**
     * @var array
     */
    private array $eventHistory = [];

    /**
     * @var bool
     */
    private bool $logEvents = false;

    /**
     * Add an event listener
     *
     * @param string $eventName
     * @param callable $listener
     * @param int $priority
     * @return void
     */
    public function listen(string $eventName, callable $listener, int $priority = 0): void
    {
        if (!isset($this->listeners[$eventName])) {
            $this->listeners[$eventName] = [];
        }

        $this->listeners[$eventName][] = [
            'listener' => $listener,
            'priority' => $priority
        ];

        // Sort by priority (higher priority first)
        usort($this->listeners[$eventName], function ($a, $b) {
            return $b['priority'] <=> $a['priority'];
        });
    }

    /**
     * Add a wildcard listener
     *
     * @param string $pattern
     * @param callable $listener
     * @param int $priority
     * @return void
     */
    public function listenWildcard(string $pattern, callable $listener, int $priority = 0): void
    {
        $this->wildcardListeners[] = [
            'pattern' => $pattern,
            'listener' => $listener,
            'priority' => $priority
        ];

        // Sort by priority
        usort($this->wildcardListeners, function ($a, $b) {
            return $b['priority'] <=> $a['priority'];
        });
    }

    /**
     * Remove an event listener
     *
     * @param string $eventName
     * @param callable $listener
     * @return void
     */
    public function forget(string $eventName, callable $listener): void
    {
        if (!isset($this->listeners[$eventName])) {
            return;
        }

        $this->listeners[$eventName] = array_filter(
            $this->listeners[$eventName],
            function ($item) use ($listener) {
                return $item['listener'] !== $listener;
            }
        );

        if (empty($this->listeners[$eventName])) {
            unset($this->listeners[$eventName]);
        }
    }

    /**
     * Dispatch an event
     *
     * @param string|Event $event
     * @param array $data
     * @return Event
     */
    public function dispatch($event, array $data = []): Event
    {
        if (is_string($event)) {
            $eventName = $event;
            $event = new class($data) extends Event {};
        } else {
            $eventName = $event->getName();
        }

        if ($this->logEvents) {
            $this->eventHistory[] = [
                'name' => $eventName,
                'timestamp' => microtime(true),
                'data' => $data
            ];
        }

        // Execute specific listeners
        if (isset($this->listeners[$eventName])) {
            foreach ($this->listeners[$eventName] as $item) {
                if ($event->isPropagationStopped()) {
                    break;
                }

                try {
                    $item['listener']($event);
                } catch (\Exception $e) {
                    // Log error but continue with other listeners
                    error_log("Event listener error: " . $e->getMessage());
                }
            }
        }

        // Execute wildcard listeners
        foreach ($this->wildcardListeners as $item) {
            if ($event->isPropagationStopped()) {
                break;
            }

            if (fnmatch($item['pattern'], $eventName)) {
                try {
                    $item['listener']($event);
                } catch (\Exception $e) {
                    error_log("Wildcard listener error: " . $e->getMessage());
                }
            }
        }

        return $event;
    }

    /**
     * Check if an event has listeners
     *
     * @param string $eventName
     * @return bool
     */
    public function hasListeners(string $eventName): bool
    {
        if (isset($this->listeners[$eventName]) && !empty($this->listeners[$eventName])) {
            return true;
        }

        foreach ($this->wildcardListeners as $item) {
            if (fnmatch($item['pattern'], $eventName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all listeners for an event
     *
     * @param string $eventName
     * @return array
     */
    public function getListeners(string $eventName): array
    {
        $listeners = $this->listeners[$eventName] ?? [];
        
        foreach ($this->wildcardListeners as $item) {
            if (fnmatch($item['pattern'], $eventName)) {
                $listeners[] = $item;
            }
        }

        return $listeners;
    }

    /**
     * Remove all listeners
     *
     * @return void
     */
    public function flush(): void
    {
        $this->listeners = [];
        $this->wildcardListeners = [];
    }

    /**
     * Enable event logging
     *
     * @param bool $enabled
     * @return void
     */
    public function setEventLogging(bool $enabled): void
    {
        $this->logEvents = $enabled;
    }

    /**
     * Get event history
     *
     * @return array
     */
    public function getEventHistory(): array
    {
        return $this->eventHistory;
    }

    /**
     * Clear event history
     *
     * @return void
     */
    public function clearEventHistory(): void
    {
        $this->eventHistory = [];
    }

    /**
     * Subscribe to multiple events with one listener
     *
     * @param array $events
     * @param callable $listener
     * @param int $priority
     * @return void
     */
    public function subscribe(array $events, callable $listener, int $priority = 0): void
    {
        foreach ($events as $event) {
            $this->listen($event, $listener, $priority);
        }
    }

    /**
     * Dispatch multiple events
     *
     * @param array $events
     * @return array
     */
    public function dispatchMultiple(array $events): array
    {
        $results = [];
        foreach ($events as $eventName => $data) {
            $results[$eventName] = $this->dispatch($eventName, $data);
        }
        return $results;
    }

    /**
     * Get event statistics
     *
     * @return array
     */
    public function getStats(): array
    {
        $totalListeners = 0;
        foreach ($this->listeners as $listeners) {
            $totalListeners += count($listeners);
        }

        return [
            'total_events' => count($this->listeners),
            'total_listeners' => $totalListeners,
            'wildcard_listeners' => count($this->wildcardListeners),
            'events_dispatched' => count($this->eventHistory),
            'logging_enabled' => $this->logEvents
        ];
    }
}