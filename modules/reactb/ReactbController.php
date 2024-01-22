<?php

namespace Reactb;

//use Reactb\ReactbModel;
use Reactb\ReactbView;
//use Reactb\Component\Component;

class ReactbController
{
    public function display($request)
    {
        $view = new ReactbView();
        if (isset($_SESSION["username"])) {
            $view->View($request);
        } else {
            echo " Not Logged In! Something else.";
            header('Location: ' . BASE_URL . '/');
        }
    }

    public function logo()
    {

        require_once(THIS_DIR . "/modules/reactb/etc/build/logo192.png");
    }


    public function manifest()
    {

        require_once(THIS_DIR . "/modules/reactb/etc/build/manifest.json");
    }


    public function mainjs()
    {

        require_once(THIS_DIR . "/modules/reactb/etc/build/static/js/main.10d2eb17.js");
    }


    public function maincss()
    {
        require_once(THIS_DIR . "/modules/reactb/etc/build/static/css/main.f855e6bc.css");
    }
}
