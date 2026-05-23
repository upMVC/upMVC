<?php
namespace App\Modules\SaaS\Modules\PlatformAdmin\Routes;

use App\Modules\SaaS\Modules\PlatformAdmin\Controller;

class Routes
{
    public function Routes($router): void
    {
        // All routes require a valid JWT — role check happens in the Controller constructor
        $router->addRoute('/api/admin/tenants', Controller::class, 'listTenants', ['cors', 'jwt']);

        $router->addParamRoute(
            '/api/admin/tenants/{id:int}/status',
            Controller::class,
            'updateStatus',
            ['cors', 'jwt']
        );

        $router->addParamRoute(
            '/api/admin/tenants/{id:int}/plan',
            Controller::class,
            'updatePlan',
            ['cors', 'jwt']
        );
    }
}
