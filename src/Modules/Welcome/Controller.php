<?php

namespace App\Modules\Welcome;

/**
 * Static homepage — no database, no auth.
 * Wired from src/Etc/custom-routes.php (path '/').
 * Replace or remove that entry anytime to use your own homepage.
 */
class Controller
{
    public function display($reqRoute, $reqMet): void
    {
        (new View())->home();
    }
}
