<?php
namespace App\Modules\SaaS\Modules\Plans\Routes;

use App\Modules\SaaS\Modules\Plans\Controller;

class Routes
{
    public function Routes($router): void
    {
        // Plans are public — anyone can browse before signing up
        $router->addRoute('/api/plans', Controller::class, 'index', ['cors']);
        $router->addParamRoute('/api/plans/{id:int}', Controller::class, 'show', ['cors']);
    }
}
