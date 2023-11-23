<?php
/*
 * Created on Tue Oct 31 2023
 *
 * Copyright (c) 2023 BitsHost
 */

namespace MVC;

/**
 * Config
 */
class Config
{
    //Application directory
    //should be empty if domain location is root; e.g. public_html => $sitePath = "";
    //else - if domain location is: public_html/app => $sitePath = "/app"; public_html/folder/app => $sitePath = "/folder/app"
    public $sitePath = ""; //Application directory
    /**
     * initConfig
     *
     * @return void
     */
    public function initConfig()
    {
        /////////////////////////////////////
        //error
        error_reporting(0);
        /////////////////////////////////////

        define('THIS_DIR', str_replace("\\", "/", dirname(__FILE__, 2)));


        //Application URL
        //your domain address => https://www.yourdomain.com or https://yourdomain.com
        define('BASE_URL', 'https://www.yourdomain.com');

        //initialize session/test////////////
        session_start();
        $_SESSION["username"] = "defaultAppUser";
        // session_unset(); // logout
        //session_write_close();
        /////////////////////////////////////
    }

    public function setSitePath($sitePath)
    {
        $this->sitePath = $sitePath;
    }

    public function getSitePath()
    {
        return $this->sitePath;
    }

    public function cleanUrlQuestionMark($url)
    {
        if (strpos($url, "?") !== false) {
            $url = substr($url, 0, strpos($url, "?"));
            return $url;
        } else {
            return $url;
        }
    }

    public function cleanUrlSitePath($sitePath, $url)
    {
        $url = str_replace($sitePath, "", $url);
        return $url;
    }
}
