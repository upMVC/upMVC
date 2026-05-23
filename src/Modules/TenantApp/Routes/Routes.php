<?php

namespace App\Modules\TenantApp\Routes;

use App\Modules\TenantApp\Controller;

class Routes
{
    public function routes($router): void
    {
        // /app  → controller redirects to /app/{slug} using session tenant
        $router->addRoute('/app', Controller::class, 'display');

        // /app/{slug}                 → public tenant frontend
        // /app/{slug}/admin           → tenant admin dashboard  (page=admin)
        // /app/{slug}/admin/{subpage} → tenant admin sub-pages  (subpage=users|settings…)
        $router->addParamRoute('/app/{slug}',                    Controller::class, 'display');
        $router->addParamRoute('/app/{slug}/{page}',             Controller::class, 'display');
        $router->addParamRoute('/app/{slug}/admin/{subpage}',    Controller::class, 'display');
    }
}
