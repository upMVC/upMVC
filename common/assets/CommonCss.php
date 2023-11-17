<?php
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
                max-width: 1200px;
                margin: 10px auto;
                padding: 10px;
                text-align: center;
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
                margin-left: 15px;
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
                padding: 1rem 1.5rem;
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
                margin-left: 10px;
                float: right;
                font-size: 25px;
            }
        </style>
        <?php
    }

}