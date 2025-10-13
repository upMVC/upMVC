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

namespace Reactcrud;

use Common\Bmvc\BaseView;
use Common\Assets\CommonCss;

class View extends BaseView
{
    public function View($request)
    {
        $title = "ReactCrud Module";
        //$this->startHead($title)

?>

        <head>
            <!--  we just get parts from index.html from our react app latest Build from etc/Build -->
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">



            <meta name="viewport" content="'width=device-width,initial-scale=1" />
            <meta name="theme-color" content="#000000" />
            <link rel="manifest" href="<?php echo \BASE_URL; ?>/crud/manifest" />
            <title>CRUD React App</title>
            <?php
            $this->menuCssCustom();
            ?>
            <link href="<?php echo \BASE_URL; ?>/crud/css" rel="stylesheet">
            <link href="<?php echo \BASE_URL; ?>/crud/cssb" rel="stylesheet">

            <?php $this->menu() ?>

        </head>
        <?php

        $this->startBody($title);

        ?>

        <noscript>You need to enable JavaScript to run this app.</noscript>
        <div id="root" style="width: 1300px; margin: 5;"></div>
        <script>
            ! function(c) {
                function e(e) {
                    for (var r, t, n = e[0], o = e[1], u = e[2], i = 0, a = []; i < n.length; i++) t = n[i], f[t] && a.push(f[t][0]), f[t] = 0;
                    for (r in o) Object.prototype.hasOwnProperty.call(o, r) && (c[r] = o[r]);
                    for (d && d(e); a.length;) a.shift()();
                    return p.push.apply(p, u || []), l()
                }

                function l() {
                    for (var e, r = 0; r < p.length; r++) {
                        for (var t = p[r], n = !0, o = 1; o < t.length; o++) {
                            var u = t[o];
                            0 !== f[u] && (n = !1)
                        }
                        n && (p.splice(r--, 1), e = s(s.s = t[0]))
                    }
                    return e
                }
                var t = {},
                    f = {
                        1: 0
                    },
                    p = [];

                function s(e) {
                    if (t[e]) return t[e].exports;
                    var r = t[e] = {
                        i: e,
                        l: !1,
                        exports: {}
                    };
                    return c[e].call(r.exports, r, r.exports, s), r.l = !0, r.exports
                }
                s.e = function(u) {
                    var e = [],
                        t = f[u];
                    if (0 !== t)
                        if (t) e.push(t[2]);
                        else {
                            var r = new Promise(function(e, r) {
                                t = f[u] = [e, r]
                            });
                            e.push(t[2] = r);
                            var n, i = document.createElement("script");
                            i.charset = "utf-8", i.timeout = 120, s.nc && i.setAttribute("nonce", s.nc), i.src = s.p + "static/js/" + ({} [u] || u) + "." + {
                                3: "9973b74a"
                            } [u] + ".chunk.js", n = function(e) {
                                i.onerror = i.onload = null, clearTimeout(a);
                                var r = f[u];
                                if (0 !== r) {
                                    if (r) {
                                        var t = e && ("load" === e.type ? "missing" : e.type),
                                            n = e && e.target && e.target.src,
                                            o = new Error("Loading chunk " + u + " failed.\n(" + t + ": " + n + ")");
                                        o.type = t, o.request = n, r[1](o)
                                    }
                                    f[u] = void 0
                                }
                            };
                            var a = setTimeout(function() {
                                n({
                                    type: "timeout",
                                    target: i
                                })
                            }, 12e4);
                            i.onerror = i.onload = n, document.head.appendChild(i)
                        } return Promise.all(e)
                }, s.m = c, s.c = t, s.d = function(e, r, t) {
                    s.o(e, r) || Object.defineProperty(e, r, {
                        enumerable: !0,
                        get: t
                    })
                }, s.r = function(e) {
                    "undefined" != typeof Symbol && Symbol.toStringTag && Object.defineProperty(e, Symbol.toStringTag, {
                        value: "Module"
                    }), Object.defineProperty(e, "__esModule", {
                        value: !0
                    })
                }, s.t = function(r, e) {
                    if (1 & e && (r = s(r)), 8 & e) return r;
                    if (4 & e && "object" == typeof r && r && r.__esModule) return r;
                    var t = Object.create(null);
                    if (s.r(t), Object.defineProperty(t, "default", {
                            enumerable: !0,
                            value: r
                        }), 2 & e && "string" != typeof r)
                        for (var n in r) s.d(t, n, function(e) {
                            return r[e]
                        }.bind(null, n));
                    return t
                }, s.n = function(e) {
                    var r = e && e.__esModule ? function() {
                        return e.default
                    } : function() {
                        return e
                    };
                    return s.d(r, "a", r), r
                }, s.o = function(e, r) {
                    return Object.prototype.hasOwnProperty.call(e, r)
                }, s.p = "/", s.oe = function(e) {
                    throw console.error(e), e
                };
                var r = window.webpackJsonp = window.webpackJsonp || [],
                    n = r.push.bind(r);
                r.push = e, r = r.slice();
                for (var o = 0; o < r.length; o++) e(r[o]);
                var d = n;
                l()
            }([])
        </script>
        <script src="<?php echo \BASE_URL; ?>/crud/js"></script>
        <script src="<?php echo \BASE_URL; ?>/crud/jsa"></script>

    <?php
        $this->endBody();
        $this->startFooter();
        $this->endFooter();
    }

    public function menuCssCustom()
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
                padding: 9px;
                text-align: center;
                font-size: 14px;
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
                padding: 0.4rem 0.8rem;
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
?>