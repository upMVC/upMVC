<?php
/*
 *   Created on October 11, 2025
 *   Copyright (c) 2025 BitsHost
 *   Enhanced Module Example - Demonstrates new upMVC features
 */

namespace App\Modules\enhanced;

use App\Common\Bmvc\BaseController;
use App\Etc\Cache\CacheManager;
use App\Etc\Events\EventDispatcher;
use App\Etc\Events\UserRegistered;
use App\Etc\Container\Container;

/**
 * Enhanced Controller
 * 
 * Example controller demonstrating new upMVC features
 */
class Controller extends BaseController
{
    private Container $container;
    private EventDispatcher $eventDispatcher;

    public function __construct(Container $container = null, EventDispatcher $eventDispatcher = null)
    {
        // Demonstrate dependency injection
        $this->container = $container ?? new Container();
        $this->eventDispatcher = $eventDispatcher ?? new EventDispatcher();
        
        // Setup event listeners
        $this->setupEventListeners();
    }

    /**
     * Main display method
     */
    public function display($reqRoute, $reqMet)
    {
        try {
            $view = new View();
            
            // Demonstrate caching
            $cachedData = CacheManager::remember('enhanced_demo_data', function() {
                return $this->generateDemoData();
            }, 300); // Cache for 5 minutes

            // Demonstrate event dispatching
            $this->eventDispatcher->dispatch(new UserRegistered([
                'user_id' => 123,
                'email' => 'demo@example.com',
                'name' => 'Demo User'
            ]));

            $data = [
                'route' => $reqRoute,
                'method' => $reqMet,
                'cached_data' => $cachedData,
                'event_stats' => $this->eventDispatcher->getStats(),
                'cache_stats' => $this->getCacheStats()
            ];

            $view->render($data);

        } catch (\Exception $e) {
            // Enhanced error handling will catch this
            throw new \upMVC\Exceptions\DatabaseException(
                'Error in enhanced module: ' . $e->getMessage()
            );
        }
    }

    /**
     * API endpoint demonstrating enhanced features
     */
    public function api($reqRoute, $reqMet)
    {
        header('Content-Type: application/json');

        try {
            $response = [
                'status' => 'success',
                'data' => [
                    'features' => [
                        'middleware' => 'Active',
                        'caching' => 'Active', 
                        'events' => 'Active',
                        'di_container' => 'Active',
                        'error_handling' => 'Active'
                    ],
                    'stats' => [
                        'events' => $this->eventDispatcher->getStats(),
                        'cache' => $this->getCacheStats()
                    ]
                ],
                'timestamp' => time()
            ];

            echo json_encode($response, JSON_PRETTY_PRINT);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Setup event listeners
     */
    private function setupEventListeners(): void
    {
        $this->eventDispatcher->listen(UserRegistered::class, function($event) {
            // Log user registration
            error_log("User registered: " . json_encode($event->getData()));
        });

        $this->eventDispatcher->listenWildcard('User*', function($event) {
            // Log all user events
            error_log("User event dispatched: " . $event->getName());
        });
    }

    /**
     * Generate demo data
     */
    private function generateDemoData(): array
    {
        return [
            'generated_at' => date('Y-m-d H:i:s'),
            'random_number' => rand(1000, 9999),
            'features_demonstrated' => [
                'Caching with CacheManager',
                'Event dispatching with EventDispatcher', 
                'Dependency injection with Container',
                'Enhanced error handling',
                'Middleware pipeline'
            ]
        ];
    }

    /**
     * Get cache statistics
     */
    private function getCacheStats(): array
    {
        try {
            $fileCache = CacheManager::store('file');
            
            // Type check and method check for FileCache specific functionality
            if ($fileCache instanceof \upMVC\Cache\FileCache && method_exists($fileCache, 'getStats')) {
                return $fileCache->getStats();
            }
            
            // Fallback for other cache implementations
            return [
                'status' => 'Cache active',
                'driver' => get_class($fileCache),
                'available_methods' => get_class_methods($fileCache)
            ];
            
        } catch (\Exception $e) {
            return [
                'status' => 'Cache error: ' . $e->getMessage(),
                'driver' => 'unknown'
            ];
        }
    }
}










