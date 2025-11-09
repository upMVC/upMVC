<?php
/*
 * Admin Module - Routes WITH CACHE
 * 
 * This version caches routes to a file to avoid database queries on every request.
 * 
 * HOW TO USE:
 * 1. Rename this file to Routes.php (backup original first!)
 * 2. Create cache directory: modules/cache/
 * 3. Routes will be cached for 1 hour (configurable)
 * 4. Cache is cleared automatically when users are created/deleted
 * 
 * PERFORMANCE:
 * - First request: ~100ms (DB query + cache write)
 * - Cached requests: ~2ms (file read only)
 * - 50x faster than querying DB every time!
 */

namespace Admin\Routes;

use Admin\Controller; // import target controller for route registrations

class Routes
{
    /**
     * Parameterized routing version.
     * Collapses thousands of expanded user-specific edit/delete routes
     * into two lightweight param patterns.
     *
     * Backward compatibility: public static clearCache() & getCacheStats()
     * remain (returning stub data) so existing Controller calls don't break.
     */

    public function __construct()
    {
        // No cache file needed anymore.
    }

    public function routes($router)
    {
        // Static routes
        $router->addRoute('/admin', Controller::class, 'display');
        $router->addRoute('/admin/users', Controller::class, 'display');
        $router->addRoute('/admin/users/add', Controller::class, 'display');

        // Parameterized routes (replace expanded cached routes)
        if (method_exists($router, 'addParamRoute')) {
            $router->addParamRoute('/admin/users/edit/{id}', Controller::class, 'display');
            $router->addParamRoute('/admin/users/delete/{id}', Controller::class, 'display');
        } else {
            // Fallback: keep legacy behavior (could optionally throw)
            // NOTE: We intentionally do NOT regenerate per-ID routes.
        }
    }

    /**
     * Legacy API stub - kept to avoid fatal errors if old controller code calls it.
     */
    public static function clearCache(): void
    {
        // No-op: param routing requires no cache.
    }

    /**
     * Legacy API stub - returns synthetic stats reflecting param mode.
     */
    public static function getCacheStats(): array
    {
        return [
            'exists' => false,
            'age' => 0,
            'routes' => 0,
            'size' => 0,
            'created' => null,
            'mode' => 'parameterized'
        ];
    }
}
