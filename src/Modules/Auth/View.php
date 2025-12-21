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

namespace App\Modules\Auth;

class View
{


    public function login()
    {
?>
        <div>
            <div class="login-signup" style="text-align: center; font-size: 30px;">
                <a href="<?php echo BASE_URL; ?>/signup">Sign up</a>
            </div>
            <form action="" method="post" id="frmLogin" onSubmit="return validate();">
                <div class="demo-table">

                    <div class="form-head">Login</div>
                    <div class="field-column">
                        <div>
                            <label for="username">Username</label><span id="user_info" class="error-info"></span>
                        </div>
                        <div>
                            <input name="username" id="username" type="text" class="demo-input-box">
                        </div>
                    </div>
                    <div class="field-column">
                        <div>
                            <label for="password">Password</label><span id="password_info" class="error-info"></span>
                        </div>
                        <div>
                            <input name="password" id="password" type="password" class="demo-input-box">
                        </div>
                    </div>
                    <div class=field-column>
                        <div>
                            <input type="submit" name="login" value="Login" class="btnLogin"></span>
                        </div>
                    </div>
                </div>
            </form>
        </div>



    <?php
    }

    public function validate()
    {
    ?>
        <script>
            function validate() {
                var $valid = true;
                document.getElementById("user_info").innerHTML = "";
                document.getElementById("password_info").innerHTML = "";

                var userName = document.getElementById("username").value;
                var password = document.getElementById("password").value;
                if (userName == "") {
                    document.getElementById("user_info").innerHTML = "This field is required and cannot be empty";
                    $valid = false;
                }
                if (password == "") {
                    document.getElementById("password_info").innerHTML = "This field is required and cannot be empty";
                    $valid = false;
                }
                return $valid;
            }
        </script>
    <?php

    }

    public function signup()
    {
    ?>
        <div>
            <div class="login-signup" style="text-align: center; font-size: 30px;">
                <a href="<?php echo BASE_URL; ?>/auth">Login</a>
            </div>
            <form action="" method="post" id="frmLogin" onSubmit="return validate();">
                <div class="demo-table">

                    <div class="form-head">Login</div>
                    <div class="field-column">
                        <div>
                            <label for="name">Name</label><span id="name_info" class="error-info"></span>
                        </div>
                        <div>
                            <input name="name" id="name" type="text" class="demo-input-box">
                        </div>
                    </div>
                    <div class="field-column">
                        <div>
                            <label for="username">Username</label><span id="user_info" class="error-info"></span>
                        </div>
                        <div>
                            <input name="username" id="username" type="text" class="demo-input-box">
                        </div>
                    </div>
                    <div class="field-column">
                        <div>
                            <label for="email">Email</label><span id="email_info" class="error-info"></span>
                        </div>
                        <div>
                            <input name="email" id="email" type="text" class="demo-input-box">
                        </div>
                    </div>
                    <div class="field-column">
                        <div>
                            <label for="password">Password</label><span id="password_info" class="error-info"></span>
                        </div>
                        <div>
                            <input name="password" id="password" type="password" class="demo-input-box">
                        </div>
                    </div>
                    <div class=field-column>
                        <div>
                            <input type="submit" name="signup" value="Signup" class="btnLogin"></span>
                        </div>
                    </div>
                </div>
            </form>
        </div>



    <?php
    }

    public function validateSignUp()
    {
    ?>
        <script>
            function validate() {
                var $valid = true;
                document.getElementById("name_info").innerHTML = "";
                document.getElementById("user_info").innerHTML = "";
                document.getElementById("password_info").innerHTML = "";
                document.getElementById("email_info").innerHTML = "";

                var name = document.getElementById("name").value;
                var userName = document.getElementById("username").value;
                var password = document.getElementById("password").value;
                var email = document.getElementById("email").value;
                if (name == "") {
                    document.getElementById("name_info").innerHTML = "This field is required and cannot be empty";
                    $valid = false;
                }
                if (userName == "") {
                    document.getElementById("user_info").innerHTML = "This field is required and cannot be empty";
                    $valid = false;
                }
                if (password == "") {
                    document.getElementById("password_info").innerHTML = "This field is required and cannot be empty";
                    $valid = false;
                }
                if (email == "") {
                    document.getElementById("email_info").innerHTML = "This field is required and cannot be empty";
                    $valid = false;
                }
                return $valid;
            }
        </script>
    <?php

    }

    //validate token
    public function validateToken($redirectUrl)
    {
    ?>
        <script>
            location.href = "<?php echo $redirectUrl; ?>";
        </script>
    <?php
    }

    /////////////////

    //welcome
    public function welcomeNew()
    {
    ?>
        <p style="text-align:center; font-size: 20px;">Account created! It is necessary to activate it in order to log in.<br><br> Check your email address and activate your account!</p>

        <script type="text/javascript">
            function Redirect() {
                window.location = "<?php echo BASE_URL; ?>/index.php";
            }
            document.write("You will be redirected to the login page in 5 seconds!");
            setTimeout('Redirect()', 5000);
        </script>
    <?php


    }

    //welcome


    //errors


    public function tokenInvalid()
    {
    ?>
        <p style style="text-align:center; font-size: 40px;">Nonexistent token!</p>

        <script type="text/javascript">
            function Redirect() {
                window.location = "<?php echo BASE_URL; ?>/index.php";
            }
            document.write("You will be redirected to the login page in 3 seconds!");
            setTimeout('Redirect()', 3000);
        </script>
    <?php


    }

    public function tokenValid()
    {
    ?>
        <p style style="text-align:center; font-size: 40px;">Token validated! The account has been activated.</p>

        <script type="text/javascript">
            function Redirect() {
                window.location = "<?php echo BASE_URL; ?>/index.php";
            }
            document.write("You will be redirected to the login page in 3 seconds!");
            setTimeout('Redirect()', 3000);
        </script>
    <?php


    }

    public function tokenNull()
    {
    ?>
        <p style style="text-align:center; font-size: 40px;">Nonexistent token!</p>

        <script type="text/javascript">
            function Redirect() {
                window.location = "<?php echo BASE_URL; ?>/index.php";
            }
            document.write("You will be redirected to the login page in 3 seconds!");
            setTimeout('Redirect()', 3000);
        </script>
    <?php


    }
    //////////////////CSS/////////////////////////////////

    public function cssLogin()
    {
    ?>
        <style>
            .form-head {
                margin: 0;
                font-size: 1.8em;
            }

            .login-form-container {
                background: #ffffff;
                margin: 100px auto;
                border-radius: 16px;
                padding: 30px;
                width: 325px;
                border: #e5e6e9 1px solid;
                text-align: center;
            }

            .login-form-container label {
                margin-bottom: 5px;
                display: inline-block;
            }

            .login-form-container .field-column {
                padding: 30px 10px 0px 10px;
                text-align: left;
            }

            .demo-input-box {
                padding: 8px;
                border: #CCC 1px solid;
                border-radius: 16px;
                width: 100%;
            }

            .btnLogin {
                padding: 8px;
                cursor: pointer;
                border-radius: 16px;
                width: 100%;
                border: 0px;
                transition: 0.5s;
                background-size: 200% auto;
                background-image: linear-gradient(to right, #326bfa 0%, #40f5d7 51%, #2f61ff 100%);
                margin: 0px 0px 5px 0px;
            }

            .btnLogin:hover {
                background-position: right center;
            }

            .error-info {
                color: #FF0000;
                margin-left: 10px;
            }

            .error-message {
                padding: 7px 10px;
                background: #fff1f2;
                border: #ffd5da 1px solid;
                color: #d6001c;
                border-radius: 4px;
                margin: 30px 10px 10px 10px;
            }

            .form-link {
                color: #1400f3;
                text-decoration: none;
            }

            .btn.form-link {
                border: #232323 1px solid;
                color: #232323;
                border-radius: 15px;
                padding: 5px 20px;
            }

            .login-row {
                margin: 60px 0px 0px;
            }

            .signup-icon {
                max-width: 30px;
                margin: 5px;
            }

            @media screen and (max-width:440px) {
                .login-form-container {
                    width: auto;
                    padding: 20px;
                }

                .login-form-container .field-column {
                    padding: 20px 10px 0px 10px;
                }
            }
        </style>
<?php
    }
}











