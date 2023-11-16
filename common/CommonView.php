<?php

namespace Html;

class CommonView
{
   
    public function menu()
    {
        ?>
        <header class="header">
            <?php
            ?>
            <div class="wrapper">
                <ul class="menu">
                    <li>
                        <a href="<?php echo BASE_URL; ?>">🏠 Home</a>

                        <ul>
                            <li><a href="<?php echo BASE_URL; ?>/test">👩‍👩‍👧‍👧 Test</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/test-one">👩‍👩‍👧‍👧 1 Parameter(GET)</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/test?param=one">👩‍👩‍👧‍👧 1 Parameter(GET) Classic</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/test-page-one">👩‍👩‍👧‍👧 1 Parameter(GET)</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/test?param=page-one">👩‍👩‍👧‍👧 1 Parameter(GET) Classic</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/test-one/two">👩‍👩‍👧‍👧 2 Parameters(GET)</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/test?param=one&another=two">👩‍👩‍👧‍👧 2 Parameters(GET) Classic</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/test-page-one/two">👩‍👩‍👧‍👧 2 Parameters(GET)</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/moda">👩‍👩‍👧‍👧 Moda</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/suba">👩‍👩‍👧‍👧 Suba</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/error">👩‍👩‍👧‍👧 Error Page</a></li>

                        </ul>
                </ul>
            </div>


        </header>

        <?php

    }
    public function startHeader($title)
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
            <link href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" rel="stylesheet"
                media="screen">
            <?php
            $newCss->menuCss();
            $this->menu();

    }

    public function endHeader()
    {
        ?>
        </head>
        <?php

    }

    public function startBody($title)
    {
        ?>

        <body>
            <div class="container">
                <h1>
                    <?php echo $title ?>
                </h1>
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
            <p style="text-align:center;"><a href="<?php //echo BASE_URL; ?> https://bitshost.biz/free-web-hosting.html"
                    style="color: black; text-align:center; font-size: 15px;" target="_blank">©️ All rights reserved - BitsHost
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