<?php

namespace upMVC;



class Start

{

    public function upMVC()

    {
        $router = new Router();
        $config = new Config();

        $reqURI = $_SERVER['REQUEST_URI'];
        $reqMet = $_SERVER['REQUEST_METHOD'];

        $reqRoute = $config->getReqRoute($reqURI);

        $initRoutes = new Routes($router);
        $initRoutes->startRoutes($reqRoute, $reqMet);
    }
}
