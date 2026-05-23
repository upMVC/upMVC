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

namespace App\Modules\Reactcrud;

use App\Common\Bmvc\BaseView;
use App\Common\Assets\CommonCss;

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

            <script>
                // Set API URL dynamically BEFORE React loads
                window.__API_URL__ = "<?php echo \BASE_URL; ?>/apiUsers";
            </script>

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
            /* bv-nav styles for the upMVC navbar (no global reset — keeps Bootstrap intact) */
            .bv-nav {
                background: #0f172a;
                position: sticky;
                top: 0;
                z-index: 200;
                border-bottom: 1px solid #1e293b;
            }
            .bv-nav-inner {
                max-width: 1280px;
                margin: 0 auto;
                padding: 0 24px;
                height: 52px;
                display: flex;
                align-items: center;
                gap: 4px;
            }
            .bv-brand {
                font-size: .95rem;
                font-weight: 700;
                color: #f8fafc;
                text-decoration: none;
                margin-right: 16px;
                white-space: nowrap;
                flex-shrink: 0;
            }
            .bv-brand span { color: #38bdf8; }
            .bv-links {
                display: flex;
                align-items: center;
                list-style: none;
                flex: 1;
                min-width: 0;
                margin: 0;
                padding: 0;
            }
            .bv-links > li { position: relative; }
            .bv-links > li > a {
                display: flex;
                align-items: center;
                padding: 0 13px;
                height: 52px;
                color: #94a3b8;
                text-decoration: none;
                font-size: .82rem;
                white-space: nowrap;
                transition: color .15s, background .15s;
            }
            .bv-links > li > a:hover,
            .bv-links > li:hover > a  { color: #f8fafc; background: #1e293b; }
            .bv-links > li > a.bv-hi  { color: #38bdf8; font-weight: 600; }
            .bv-drop > ul {
                display: none;
                position: absolute;
                top: 52px;
                left: 0;
                background: #1e293b;
                min-width: 190px;
                border-radius: 0 0 8px 8px;
                border: 1px solid #334155;
                border-top: none;
                box-shadow: 0 8px 28px rgba(0,0,0,.35);
                list-style: none;
                z-index: 300;
                margin: 0;
                padding: 0;
            }
            .bv-drop:hover > ul { display: block; }
            .bv-drop > ul a {
                display: block;
                padding: 9px 16px;
                color: #94a3b8;
                text-decoration: none;
                font-size: .82rem;
                white-space: nowrap;
            }
            .bv-drop > ul a:hover { background: #0f172a; color: #e2e8f0; }
            .bv-right {
                margin-left: auto;
                display: flex;
                align-items: center;
                gap: 10px;
                flex-shrink: 0;
            }
            .bv-uname { font-size: .8rem; color: #64748b; white-space: nowrap; }
            .bv-role-badge {
                display: inline-block;
                padding: 2px 9px;
                border-radius: 10px;
                font-size: .72rem;
                font-weight: 600;
            }
            .bv-btn {
                padding: 5px 14px;
                border-radius: 6px;
                font-size: .8rem;
                font-weight: 600;
                text-decoration: none;
                background: #1e293b;
                color: #94a3b8;
                border: 1px solid #334155;
                transition: background .15s, color .15s;
                white-space: nowrap;
            }
            .bv-btn:hover { background: #334155; color: #e2e8f0; }
            .bv-btn-primary { background: #0369a1; color: #e0f2fe; border-color: #0284c7; }
            .bv-btn-primary:hover { background: #0284c7; color: #fff; }
        </style>
<?php
    }
}
?>









