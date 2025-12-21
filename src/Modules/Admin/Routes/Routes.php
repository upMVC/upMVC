<?php
/**
 * Admin Module Routes - ROUTER V2 ENHANCED
 * 
 * This version demonstrates Router v2.0 capabilities:
 * - Type hints: {id:int} for automatic type casting
 * - Validation: Regex constraints for security
 * - Named routes: ->name() for URL generation
 * - Performance: Prefix grouping optimization
 * 
 * ROUTER V2 FEATURES USED:
 * ✅ Type Casting: {id:int} auto-casts to integer
 * ✅ Validation: '\d+' ensures only numeric IDs
 * ✅ Named Routes: route('admin.user.edit', ['id' => 5])
 * ✅ Security: Invalid IDs rejected at router level
 * 
 * COMPARISON WITH OTHER IMPLEMENTATIONS:
 * - Routesc.php: Cache-based expansion (DB query → expand → cache)
 * - Routesd.php: Basic param routing (no type hints or constraints)
 * - Routes.php: THIS FILE - Full Router V2 features
 * 
 * @see docs/routing/ROUTER_V2_EXAMPLES.md
 * @see docs/routing/PARAMETERIZED_ROUTING.md
 */

namespace App\Modules\Admin\Routes;

use App\Modules\Admin\Controller;

class Routes
{
    /**
     * Router v2.0 enhanced parameterized routing.
     * 
     * Benefits over previous versions:
     * - Type safety: Auto-cast params to int/float/bool
     * - Security: Regex validation prevents invalid input
     * - Refactor-safe: Named routes for URL generation
     * - Performance: No database queries, no cache files
     */

    public function routes($router)
    {
        // ========================================
        // Static Routes
        // ========================================
        
        $router->addRoute('/admin', Controller::class, 'display');
        $router->addRoute('/admin/users', Controller::class, 'display');
        $router->addRoute('/admin/users/add', Controller::class, 'display');

        // ========================================
        // Parameterized Routes - Router V2 Enhanced
        // ========================================
        
        if (method_exists($router, 'addParamRoute')) {
            // Edit User Route
            // ✅ {id:int} - Auto-casts to integer in $_GET['id']
            // ✅ '\d+' - Only numeric IDs accepted (security)
            // ✅ name() - Generate URLs: route('admin.user.edit', ['id' => 123])
            $router->addParamRoute(
                '/admin/users/edit/{id:int}',
                Controller::class,
                'display',
                [],
                ['id' => '\d+']  // Validation: only digits
            )->name('admin.user.edit');

            // Delete User Route
            // Same Router V2 features as edit route
            $router->addParamRoute(
                '/admin/users/delete/{id:int}',
                Controller::class,
                'display',
                [],
                ['id' => '\d+']  // Validation: only digits
            )->name('admin.user.delete');
        }
    }

    /**
     * Legacy compatibility stub - no cache in Router V2
     */
    public static function clearCache(): void
    {
        // Router V2 uses parameterized routing - no cache needed
    }

    /**
     * Legacy compatibility stub - returns Router V2 mode info
     */
    public static function getCacheStats(): array
    {
        return [
            'exists' => false,
            'age' => 0,
            'routes' => 0,
            'size' => 0,
            'created' => null,
            'mode' => 'router-v2-enhanced'
        ];
    }
}











