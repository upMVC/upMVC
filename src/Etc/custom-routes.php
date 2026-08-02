<?php

/**
 * App-owned exact routes registered after module discovery.
 * Later entries overwrite the same path — this is how the homepage owns '/'.
 *
 * Change or remove '/' anytime and point it at your own controller.
 * Welcome needs no database and no auth.
 */

return [
    [
        'path' => '/',
        'controller' => \App\Modules\Welcome\Controller::class,
        'method' => 'display',
    ],
    [
        'path' => '/index.php',
        'controller' => \App\Modules\Welcome\Controller::class,
        'method' => 'display',
    ],
];
