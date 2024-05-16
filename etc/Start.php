<?php

namespace upMVC;

use upMVC\Routes;
use upMVC\Config;


class Start

{
    protected $url;
    protected $request;
    protected $sitePath;
    protected $urlWithoutSitePath;
    protected $initRoutes;
    protected $reqUrl;
    protected $reqMet;



    public function upMVC()
    {
        $reqUrl = $_SERVER['REQUEST_URI'];
        $reqMet = $_SERVER['REQUEST_METHOD'];
        

        $config = new Config();
        $config->initConfig();
        $sitePath = $config->getSitePath();

        $urlWithoutSitePath = $config->cleanUrlSitePath($sitePath, $reqUrl);
        $reqRoute = $config->cleanUrlQuestionMark($urlWithoutSitePath);


        $initRoutes = new Routes();
        $initRoutes->startRoutes($reqRoute, $reqMet);
    }
}
