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

namespace App\Common\Bmvc;

use App\Common\Assets\CommonCss;

class BaseView
{

    protected $globals = [
        'settings' => [
            'theme' => 'light',
            'site_name' => 'Dashboard',
            'items_per_page' => '10',
            'maintenance_mode' => 'false'
        ]
    ];

    /**
     * Add a global variable accessible to all views
     * 
     * @param string $key Variable name
     * @param mixed $value Variable value
     */
    public function addGlobal($key, $value) {
        if ($key === 'settings' && isset($this->globals['settings'])) {
            // Merge settings instead of overwriting
            $this->globals['settings'] = array_merge($this->globals['settings'], $value);
        } else {
            $this->globals[$key] = $value;
        }
        error_log("BaseView addGlobal - Key: $key, Current globals: " . print_r($this->globals, true));
    }

    /**
     * Get a global variable
     * 
     * @param string $key Variable name
     * @return mixed|null Variable value or null if not found
     */
    public function getGlobal($key) {
        return $this->globals[$key] ?? null;
    }

    public function menu()
    {
?>
        <header class="header">
            <?php
            ?>
            <div class="wrapper">
                <ul class="menu">
                    <li>
                        <a href="https://upmvc.com">👩‍👩‍👧‍👧 upMVC</a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>">🏠 Home</a>
                         <ul>
                        <li>
                                <a href="<?php echo BASE_URL; ?>/admin">👩‍👩‍👧‍👧 Admin</a>
                            </li>
                            <li>
                                <a href="<?php echo BASE_URL; ?>/test/modern">👩‍👩‍👧‍👧 Modern</a>
                            </li>
                            
                        <li>
                                <a href="<?php echo BASE_URL; ?>/dashboardexample">👩‍👩‍👧‍👧 Dashboard Example</a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>/test">🏠 Routing</a>
                        <ul>
                            <li><a href="<?php echo BASE_URL; ?>/test">Test</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/test/subpage">Subpage</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/test/item/42">Int param {id:int}</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/test/article/my-slug">String param {slug}</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/test/pair/hello/world">Two params</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/moda">Moda</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/moda/subpage">Moda Subpage</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/suba">Suba</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/suba/subpage">Suba Subpage</a></li>
                        
                        </ul>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>">🏠 CRUD</a>
                        <ul>
                            <li>
                                <a href="<?php echo BASE_URL; ?>/users">👩‍👩‍👧‍👧 Users CRUD</a>
                            </li>
                            <li>
                                <a href="<?php echo BASE_URL; ?>/new">👩‍👩‍👧‍👧 Users CRUD PHPISTOLS</a>
                            </li>
                            <li>
                                <a href="<?php echo BASE_URL; ?>/reactcrud">👩‍👩‍👧‍👧 Users CRUD React</a>
                            </li>
                            <li>
                                <a href="<?php echo BASE_URL; ?>/usersorm">👩‍👩‍👧‍👧 Users CRUD ORM</a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>/apiInfo">👩‍👩‍👧‍👧 ApiInfo</a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>/react">👩‍👩‍👧‍👧 JS </a>
                        <ul>
                        <li>
                                <a href="<?php echo BASE_URL; ?>/react">👩‍👩‍👧‍👧 React</a>
                            </li>
                            <li>
                                <a href="<?php echo BASE_URL; ?>/reactb">👩‍👩‍👧‍👧 ReactB</a>
                            </li>
                            
                        <li>
                                <a href="<?php echo BASE_URL; ?>/reactnb">👩‍👩‍👧‍👧 NoBuild</a>
                            </li>
                            <li>
                                <a href="<?php echo BASE_URL; ?>/reacthmr">👩‍👩‍👧‍👧 HMR</a>
                            </li>
                        </ul>
                    </li>

                    <li>
                        <a href="<?php echo BASE_URL; ?>/auth">👩‍👩‍👧‍👧 Authentication</a>
                    </li>
                    <?php
                    if (isset($_SESSION["logged"]) && $_SESSION["logged"] == true) {
                    ?>
                        <li>
                            <a href="<?php echo BASE_URL; ?>/logout">👩‍👩‍👧‍👧 Logout</a>
                        </li>
                    <?php

                    }
                    ?>
                    <li>
                        <a href="https://github.com/upMVC/upMVC/wiki/How%E2%80%90to-Page" target="_blank">👩‍👩‍👧‍👧 Wiki</a>
                    </li>
                </ul>
            </div>


        </header>

    <?php

    }
    public function startHead($title)
    {
        $newCss = new CommonCss();
    ?>
        <!DOCTYPE html>
        <html>

        <head>
            <title>
                <?php echo $title ?>
            </title>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <!-- Bootstrap -->
            <link href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" rel="stylesheet" media="screen">

            <!-- Bootstrap 5.1.3
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" media="screen">
            -->
        <?php
        $newCss->menuCss();
    }

    public function endHead()
    {
        $this->menu();
        ?>
        </head>
    <?php

    }

    public function startBody($title)
    {
    ?>

        <body>
            <div style="text-align:center;">Use user: demo, pass: demo for login!</div>
            <div class="container">
                <h1>
                    <?php echo $title ?>
                </h1>
            </div>
            <div class="container">
            <?php

        }

        public function endBody()
        {
            ?>
            </div>

        </body>

    <?php

        }

        public function startFooter()
        {
    ?>
        <br><br>
        <div>
            <p style="text-align:center;"><a href="<?php //echo BASE_URL
                                                    ?> https://bitshost.biz/free-web-hosting.html" style="color: black; text-align:center; font-size: 15px;" target="_blank">©️ All rights reserved - BitsHost
                    Cloud
                    <?php echo date("Y"); ?><a>

            </p>
        </div>

    <?php


        }

        public function endFooter()
        {
    ?>

        </html>
<?php

        }
    }





