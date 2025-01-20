<?php

namespace Admin;

use Admin\View;
//use App\Http\Request;

class Controller 

{

    private $table = 'users';

    public function display($reqRoute, $reqMet)
    {
        if (isset($_SESSION["username"])) {
            $this->index($reqRoute, $reqMet);
            //echo $reqMet . " " .  $reqRoute . " ";
        } else {
            $this->index($reqRoute, $reqMet);
            //header('Location: ' . BASE_URL . '/');
        }
    }


    public function index($reqRoute, $reqMet) 
    {
        $model = new Model(); 
        $view = new View();


        switch ($reqRoute) {
            case '/admin':
                $users = $model->getAllUsers($this->table);
                //print_r($users);
                $data = ['users' => $users, 'view'=> 'index'];
                return $view->render($data); 
                break;

                // ... other render methods

            default:
                return $view->render('error'); 
                break;
        }
    }

}