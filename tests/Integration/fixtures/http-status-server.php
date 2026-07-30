<?php

/**
 * Front controller for HttpStatusTest.
 *
 * Served by PHP's built-in web server so that http_response_code() actually
 * writes a status line. Under PHPUnit's CLI SAPI it returns false and does
 * nothing, which is why the 404-answering-200 bug could never have been
 * caught by an ordinary unit test.
 *
 * This boots the Router alone — no Start, no .env, no database — because the
 * behaviour under test lives entirely in Router::handle404() and
 * Router::handle405(). Keeping the surface this small is what lets the test
 * run in CI, where none of that configuration exists.
 */

declare(strict_types=1);

$root = dirname(__DIR__, 3);

require_once $root . '/vendor/autoload.php';

// handle404() echoes a meta-refresh pointing at BASE_URL. Normally defined by
// Config::initConfig(); defined here so the view renders without the config
// layer.
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://' . ($_SERVER['HTTP_HOST'] ?? '127.0.0.1'));
}

/**
 * A route that exists, so the test can tell "404 on everything" apart from
 * "404 on things that are actually missing".
 */
class SmokeController
{
    public function index(string $route, string $method): void
    {
        echo 'smoke ok';
    }
}

$router = new App\Etc\Router();
$router->addRoute('/hit', SmokeController::class, 'index', [], ['GET']);

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

$router->dispatcher(
    is_string($path) ? $path : '/',
    $_SERVER['REQUEST_METHOD'] ?? 'GET',
    $_SERVER['REQUEST_URI'] ?? '/'
);
