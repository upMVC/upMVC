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

        $config->initConfig();
        $sitePath = $config->getSitePath();

        $urlWithoutSitePath = $config->cleanUrlSitePath($sitePath, $reqURI);
        $reqRoute = $config->cleanUrlQuestionMark($urlWithoutSitePath);

        $initRoutes = new Routes($router);
        $initRoutes->startRoutes($reqRoute, $reqMet);
    }
}
