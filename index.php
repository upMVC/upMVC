<?php
/*
 * Created on Tue Oct 31 2023
 *
 * Copyright (c) 2023 BitsHost
 * 
 * Here you may host your app for free:
 * https://bitshost.biz/
 */


require 'vendor/autoload.php';

use MVC\Routes;
use MVC\Config;

$url = $_SERVER['REQUEST_URI'];
$request = $_SERVER['REQUEST_METHOD'];

$config = new Config();
$config->initConfig();
$sitePath = $config->getSitePath();

$urlWithoutSitePath = $config->cleanUrlSitePath($sitePath, $url);
$url = $config->cleanUrlQuestionMark($urlWithoutSitePath);


$start = new Routes();
$start->startRoutes($url, $request);
