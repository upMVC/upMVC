<?php

namespace ReactCrud\Routes;

use ReactCrud\ReactCrudController;

class ReactCrudRoutes
{
    public function Routes($router)
    {
        $router->addRoute('/reactcrud', ReactCrudController::class, 'display');
        //react Build links from index.html 
        $router->addRoute('/crud/manifest', ReactCrudController::class, 'manifest');
        $router->addRoute('/crud/css', ReactCrudController::class, 'css');
        $router->addRoute('/crud/cssb', ReactCrudController::class, 'cssb');
        $router->addRoute('/crud/js', ReactCrudController::class, 'js');
        $router->addRoute('/crud/jsa', ReactCrudController::class, 'jsb');
    }
}
