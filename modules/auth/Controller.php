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


namespace Auth;

use Auth\Model;
use Auth\View;
use Common\Bmvc\BaseView;
use Mail\MailController;
use PDO;

class Controller
{
    public $title = "Authetnication Page";
    public $username;
    public $url = BASE_URL;
    //public $pass;
    // public $view;
    public $html;
    var $name;

    public function display($request)
    {
        if (isset($_SESSION["logged"])  && $_SESSION["logged"] = true) {
            $this->url = BASE_URL;
            header("Location: $this->url");
        } else {
            $this->Login($request);
        }
    }

    public function Login($request)
    {
        $view        = new BaseView();
        $this->html = new View();
        $this->title = "Login Page";
        $view->startHead($this->title);
        $this->html->cssLogin();
        $view->endHead();
        $view->startBody($this->title);

        $this->html->Login();
        $this->html->validate();

        $view->endBody();
        $view->startFooter();
        $view->endFooter();

        $useri = new Model();
        if ($_POST) {
            $useri->username = $_POST['username'];
            $useri->password     = $_POST['password'];
            //$useri->tokenSession = $token;
            $stmt            = $useri->readUserLogin();
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $activ = intval($row['state']);
                if ($activ === 1) {
                    $_SESSION["username"] = $row['username'];
                    $_SESSION["iduser"]   = $row['id'];
                    $_SESSION["logged"] = true;
                    $this->html->validateToken();
                    header("Location: $this->url");
                } else {
                    echo 'You have not activated your account, check your email!';
                }
            } else {
                echo "Try again!";
            }
        } else {
            echo "Out!";
        }
    }

    function Logout($request)
    {

        session_unset();
        session_destroy();
        //session_write_close();
        \ob_start();
        $view        = new BaseView();
        $this->html = new View();
        $this->title = "GoodBye";
        $view->startHead($this->title);
        $this->html->cssLogin();
        $view->endHead();
        $view->startBody($this->title);
        //do something
        $view->endBody();
        $view->startFooter();
        $view->endFooter();
        \ob_clean();

        //header("Refresh: 3; url=$this->url");
        //echo "Bye! You will be redirected to the home page in 3 seconds!";
        header("Location: $this->url");
    }

    function signUp($request)
    {
        $view        = new BaseView();
        $this->html = new View();
        $user = new Model();
        $newSent = new MailController();
        $this->title = "Signup Page";
        $view->startHead($this->title);
        $this->html->cssLogin();
        $view->endHead();
        $view->startBody($this->title);
        if (isset($_POST["signup"])) {
            $token          = $this->TokenGenerator(31);
            $user->token    = $token;
            $user->name = $_POST["name"];
            $user->username = $_POST['username'];
            $user->email    = $_POST['email'];
            $user->password     = $_POST['password'];
            $stmt           = $user->createUserSignup();
            //confirmation email
            $this->url     = BASE_URL . "/activation?token=" . $token;
            $to      = $_POST['email'];
            $from    = 'office@bitsworld.ro';
            $subject = "Account Activation";
            $message = '<p>Activate your account:
            <br> <a href="' . $this->url . '"> Click on confirmation link.</a></p>';
            $newSent->send_mail_by_PHPMailer($to, $from, $subject, $message);
            $this->html->welcomeNew();
        } else {
            $this->html->Signup();
            $this->html->validateSignUp();
        }

        $view->endBody();
        $view->startFooter();
        $view->endFooter();
    }

    public function TokenGenerator($TokenLength)
    {
        $char = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $token     = '';
        $n         = 0;
        while ($n < $TokenLength) {
            $position = rand(0, strlen($char) - 1);
            $token .= $char[$position];
            $n++;
        }
        return $token;
    }

    public function AccountActivation()
    {
        $this->html = new View();
        $user = new Model();
        if (isset($_GET)) {
            if (!empty($_GET['token'])) {
                $token       = $_GET['token'];
                $user->token = $token;
                $stmt     = $user->readUserToken();
                $result = $stmt->rowCount();
                if ($result === 0) {
                    $this->html->tokenInvalid();
                } else {
                    $user->state = 1;
                    $user->setActiveUser();
                    $this->html->tokenValid();
                }
            } else {
                $this->html->tokenNull();
            }
        } else {
            echo "No value!";
        }
    }
}
