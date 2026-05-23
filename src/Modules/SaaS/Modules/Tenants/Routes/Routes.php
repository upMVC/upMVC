<?php
namespace App\Modules\SaaS\Modules\Tenants\Routes;

use App\Modules\SaaS\Modules\Tenants\Controller;

class Routes
{
    public function Routes($router): void
    {
        // Public signup — no auth
        $router->addRoute('/api/tenants/register', Controller::class, 'register', ['cors']);

        // Authenticated read/update — JWT required
        $router->addParamRoute('/api/tenants/{id:int}',        Controller::class, 'show',   ['cors', 'jwt']);
        $router->addParamRoute('/api/tenants/{id:int}/update', Controller::class, 'update', ['cors', 'jwt']);
    }
}
