<?php
namespace App\Modules\SaaS\Modules\ApiAuth\Routes;

use App\Modules\SaaS\Modules\ApiAuth\Controller;

class Routes
{
    public function Routes($router): void
    {
        // login + refresh are public (no jwt — they produce the token)
        $router->addRoute('/api/auth/login',   Controller::class, 'login',   ['cors']);
        $router->addRoute('/api/auth/refresh', Controller::class, 'refresh', ['cors']);

        // logout validates the caller's existing JWT before revoking
        $router->addRoute('/api/auth/logout',  Controller::class, 'logout',  ['cors', 'jwt']);
    }
}
