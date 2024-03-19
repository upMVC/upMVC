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

namespace Common\Assets;

class CommonCss{
    public function menuCss()
    {
        ?>
        <style>
            /**
                                                                   * General Styles, for looks.
                                                                  */

            @import url("https://fonts.googleapis.com/css?family=Lato:400");


            .wrapper {
                max-width: 1250px;
                margin: 15px auto;
                padding: 8px;
                text-align: center;
                font-size: 13px;
            }

            /**
                                                                   * Styles on screen size smaller then 400 px
                                                                  */

            .menu {
                margin: 0;
                padding: 0;
                list-style: none;
                background-color: #2f3640;
                box-shadow: rgba(50, 50, 93, 0.25) 0px 13px 27px -5px,
                    rgba(0, 0, 0, 0.3) 0px 8px 16px -8px;
            }

            .menu ul {
                height: 0;
                min-width: 150px;
                margin: 0;
                padding: 0;
                list-style: none;
                overflow: hidden;
            }

            .menu li {
                display: block;
                position: relative;
                text-align: left;
            }

            .menu li:focus-within>ul,
            .menu li:hover>ul {
                height: auto;
                margin-left: 13px;
            }

            /**
                                                                   * Styles on screen size bigger 400 px
                                                                  */

            @media (min-width: 550px) {
                .menu ul {
                    height: auto;
                    overflow: visible;
                    position: absolute;
                    top: -999em;
                    left: -999em;
                }

                .menu li {
                    display: inline-block;
                    position: relative;
                    text-align: left;
                }

                .menu li:focus-within>ul,
                .menu li:hover>ul {
                    top: auto;
                    left: auto;
                    margin-left: 0;
                    max-width: 550px;
                }

                .menu li li {
                    left: auto;
                    top: auto;
                    display: block;
                }

                .menu li li:focus-within>ul,
                .menu li li:hover>ul {
                    left: 100%;
                    top: 0;
                }
            }

            /**
                                                                   * Colouring is fun!
                                                                  */

            .menu {
                background-color: #2f3640;
            }

            .menu a {
                display: block;
                white-space: nowrap;
                color: #fff;
                font-weight: bold;
                text-decoration: none;
                padding: 1.1rem 1.3rem;
            }

            .menu a:focus-within,
            .menu a:hover {
                color: #1e272e;
                background: #ffa801;
            }

            .menu ul {
                background-color: #2f3640;
                box-shadow: rgba(50, 50, 93, 0.25) 0px 13px 27px -5px,
                    rgba(0, 0, 0, 0.3) 0px 8px 16px -8px;
            }

            .menu li:focus-within,
            .menu li:hover {
                background: #ffa801;
            }

            .menu li:focus-within>a,
            .menu li:hover>a {
                color: #2f3640;
            }

            /* ------------ Experimental ------------
                                                                   * adds a "»" to indicate a dropdown menu
                                                                  */
            .menu li a:not(:only-child)::after {
                content: "»";
                display: inline-block;
                margin-left: 15px;
                float: right;
                font-size: 20px;
            }
        </style>
        <?php
    }

}