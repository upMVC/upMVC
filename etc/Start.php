<?php

namespace upMVC;



class Start

{
    protected $reqUrl;
    protected $reqMet;

    public function getReqUrl()
    {
        return $this->reqUrl;
    }

    public function getReqMet()
    {
        return $this->reqMet;
    }

    public function setReqUrl($value)
    {
        $this->reqUrl = $value;
    }

    public function setReqMet($value)
    {
        $this->reqMet = $value;
    }


    public function upMVC()

    {
        $router = new Router();
        $config = new Config();

        $this->setReqUrl($_SERVER['REQUEST_URI']);
        $this->setReqMet($_SERVER['REQUEST_METHOD']);

        $config->initConfig();
        $sitePath = $config->getSitePath();

        $urlWithoutSitePath = $config->cleanUrlSitePath($sitePath, $this->getReqUrl());
        $reqRoute = $config->cleanUrlQuestionMark($urlWithoutSitePath);

        $initRoutes = new Routes($router);
        $initRoutes->startRoutes($reqRoute, $this->getReqMet());
    }
}
