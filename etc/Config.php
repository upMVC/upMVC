<?php
/*
 *   Created on Tue Oct 31 2023
 *   Copyright (c) 2023 BitsHost
 *   All rights reserved.
 *
 *   Permission is hereby granted, free of charge, to any person obtaining a copy
 *   of this software and associated documentation files (the "Software"), to deal
 *   in the Software without restriction, including without limitation the rights
 *   to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *   copies of the Software, and to permit persons to whom the Software is
 *   furnished to do so, subject to the following conditions:
 *
 *   The above copyright notice and this permission notice shall be included in all
 *   copies or substantial portions of the Software.
 *
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

namespace upMVC;

/**
 * Config
 */
class Config
{
    //Application directory
    //should be empty if domain location is root; e.g. public_html => $sitePath = ""
    //else - if domain location is: public_html/app => $sitePath = "/app" public_html/folder/app => $sitePath = "/folder/app"

    public const SITE_PATH = '/upMVC-DEV';


    //Application URL
    //your domain address => https://www.yourdomain.com or https://yourdomain.com
    // main domain, not subdomain, not subfolder
    
    public const DOMAIN_NAME = 'http://localhost';

    /**
     * initConfig
     *
     * @return void
     */
    public function initConfig(): void
    {
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

        define('THIS_DIR', str_replace('\\', '/', dirname(__FILE__, 2)));
        define('BASE_URL', self::DOMAIN_NAME . self::SITE_PATH);

        session_start();
    }

    /**
     * setSitePath
     *
     * @param string $value
     * @return void
     */
    public function setSitePath(string $value): void
    {
        define('SITE_PATH', $value);
    }

    /**
     * getSitePath
     *
     * @return string
     */
    public function getSitePath(): string
    {
        return self::SITE_PATH;
    }

    /**
     * cleanUrlQuestionMark
     *
     * @param string $urlWithoutSitePath
     * @return string
     */
    public function cleanUrlQuestionMark(string $urlWithoutSitePath): string
    {
        $parts = parse_url($urlWithoutSitePath);
        return $parts['path'] ?? $urlWithoutSitePath;
    }

    /**
     * cleanUrlSitePath
     *
     * @param string $sitePath
     * @param string $reqUrl
     * @return string
     */
    public function cleanUrlSitePath(string $sitePath, string $reqUrl): string
    {
        return str_replace($sitePath, '', $reqUrl);
    }
}
