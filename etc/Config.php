<?php
/*
 *   Created on Tue Oct 31 2023
 
 *   Copyright (c) 2023 BitsHost
 *   All rights reserved.

 *   Permission is hereby granted, free of charge, to any person obtaining a copy
 *   of this software and associated documentation files (the "Software"), to deal
 *   in the Software without restriction, including without limitation the rights
 *   to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *   copies of the Software, and to permit persons to whom the Software is
 *   furnished to do so, subject to the following conditions:

 *   The above copyright notice and this permission notice shall be included in all
 *   copies or substantial portions of the Software.

 *   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *   IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *   FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *   AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *   LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *   OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 *   SOFTWARE.
 *   Here you may host your app for free:
 *   https://bitshost.biz/
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
        define('BASE_URL', 'https://www.yourdomain.com'); //Application URL

        //initialize session////////////
        session_start();
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
