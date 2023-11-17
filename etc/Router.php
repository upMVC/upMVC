<?php
/*
 * Created on Tue Oct 31 2023
 *
 * Copyright (c) 2023 BitsHost
 */

namespace MVC;

/**
 * Router
 */
class Router
{
    protected $routes = [];

    /**
     * addRoute
     *
     * @param  mixed $route
     * @param  mixed $className
     * @param  mixed $methodName
     * @return void
     */
    public function addRoute($route, $className, $methodName)
    {
        $this->routes[$route] = ['className' => $className, 'methodName' => $methodName];

    }

    /**
     * dispatcher
     *
     * @param  mixed $url
     * @return void
     */
    public function dispatcher($url, $request)
    {
        if (array_key_exists($url, $this->routes)) {
           
            $className  = $this->routes[$url]['className'];
            $methodName = $this->routes[$url]['methodName'];
            $this->callController($className, $methodName, $request);
        }
        else {
            ?>
            <meta http-equiv="refresh" content="3; URL='<?php echo BASE_URL ?>'" />
            <?php
            include './common/404.php';
            //throw new \Exception("No route found for URI: $url");
        }
    }

    private function callController($className, $methodName, $request)
    {
        //initialize DB
        $database = new Database();
        //$db       = $database->getConnection();

        //middleware before

        //initialize class->method
        $className = new $className();
        $className->$methodName($request);

        //middleware after
    }
}
