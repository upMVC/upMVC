<?php

namespace App\Modules\PlatformAdmin\Routes;

use App\Modules\PlatformAdmin\Controller;

class Routes
{
    public function routes($router): void
    {
        $router->addRoute('/platform-admin', Controller::class, 'display');

        // Status and plan updates carry the tenant ID in the path
        $router->addParamRoute('/platform-admin/tenants/{id:int}/status', Controller::class, 'display');
        $router->addParamRoute('/platform-admin/tenants/{id:int}/plan',   Controller::class, 'display');
    }
}
