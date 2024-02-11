<?php

namespace React;

use React\ReactModel;
use React\ReactView;
use React\Component\Component;

class ReactController
{
    public function display($request)
    {
        $view = new ReactView();
        if (isset($_SESSION["username"])) {
            $view->View($request);
        } else {
            echo " Not Logged In! Something else.";
            header('Location: ' . BASE_URL . '/');
        }
    }

    public function comp($request)
    {
        require_once(THIS_DIR . "/modules/react/etc/component.js");
        //$newComponent = new Component();
        //$newComponent->componentOne();
    }
}
