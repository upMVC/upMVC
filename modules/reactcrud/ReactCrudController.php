<?php

namespace ReactCrud;

//use ReactCrud\ReactCrudModel;
use ReactCrud\ReactCrudView;
//use ReactCrud\Component\Component;

class ReactCrudController
{
    public function display($request)
    {
        $view = new ReactCrudView();
        if (isset($_SESSION["username"])) {
            $view->View($request);
        } else {
            echo " Not Logged In! Something else.";
            header('Location: ' . BASE_URL . '/');
        }
    }

    public function logo()
    {

        require_once(THIS_DIR . "/modules/reactcrud/etc/build/logo192.png");
    }


    public function manifest()
    {

        require_once(THIS_DIR . "/modules/reactcrud/etc/build/manifest.json");
        //echo "bingo";
    }





    public function css()
    {
        require_once(THIS_DIR . "/modules/reactcrud/etc/build/static/css/2.326c04ff.chunk.css");
    }

    public function cssb()
    {
        require_once(THIS_DIR . "/modules/reactcrud/etc/build/static/css/main.b1413f35.chunk.css");
        //echo "works";
    }

    public function js()
    {

        require_once(THIS_DIR . "/modules/reactcrud/etc/build/static/js/2.b5004239.chunk.js");
    }

    public function jsb()
    {

        require_once(THIS_DIR . "/modules/reactcrud/etc/build/static/js/main.dc560686.chunk.js");
    }
}
