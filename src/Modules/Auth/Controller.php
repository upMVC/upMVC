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

use App\Common\Bmvc\BaseView;
use App\Modules\mail\MailController;
use PDO;

class Controller
{
    public $title = "Authetnication Page";
    public $username;
    public $url = BASE_URL;
    public $html;
    public $name;

    public function display($reqRoute, $reqMet)

    {
        switch ($reqRoute) {
            case "/auth":
                $this->auth();
                break;
            case "/logout":
                $this->logout();
                break;
            case "/signup":
                $this->signUp();
                break;
            case "/activation":
                $this->accountActivation();
                break;
            default:
                $this->login();
                echo $reqMet;
        }
    }

    private function getRoleRedirect(string $role, mixed $tenantId = null): string
    {
        return match($role) {
            'platform_admin' => BASE_URL . '/platform-admin',
            'tenant_owner',
            'tenant_user'    => $tenantId ? BASE_URL . '/app' : BASE_URL,
            default          => BASE_URL,
        };
    }

    private function auth()
    {
        if (isset($_SESSION["logged"]) && $_SESSION["logged"] === true) {
            $intendedUrl = $_SESSION['intended_url'] ?? null;
            unset($_SESSION['intended_url']);
            if ($intendedUrl && str_starts_with($intendedUrl, BASE_URL)) {
                header("Location: $intendedUrl");
                exit;
            }
            header('Location: ' . $this->getRoleRedirect($_SESSION['role'] ?? '', $_SESSION['tenant_id'] ?? null));
            exit;
        } else {
            $this->login();
        }
    }

    private function login()
    {
        $loginError = null;

        // Process POST before any HTML output so session cookie can be set
        if ($_POST) {
            $users           = new Model();
            $users->username = $_POST['username'] ?? '';
            $inputPassword   = $_POST['password'] ?? '';
            $stmt            = $users->readUserLogin();
            $row             = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;

            if ($row && password_verify($inputPassword, $row['password'])) {
                if (intval($row['state']) === 1) {
                    session_regenerate_id(true);
                    $_SESSION['username']      = $row['username'];
                    $_SESSION['iduser']        = $row['id'];
                    $_SESSION['role']          = $row['role'];
                    $_SESSION['tenant_id']     = $row['tenant_id'];
                    $_SESSION['logged']        = true;
                    $_SESSION['authenticated'] = true;

                    $intendedUrl = $_SESSION['intended_url'] ?? null;
                    unset($_SESSION['intended_url']);
                    $redirectUrl = ($intendedUrl && str_starts_with($intendedUrl, BASE_URL))
                        ? $intendedUrl
                        : $this->getRoleRedirect($row['role'], $row['tenant_id']);

                    header('Location: ' . $redirectUrl);
                    exit;
                } else {
                    $loginError = 'Account not activated. Check your email!';
                }
            } else {
                $loginError = 'Invalid username or password.';
            }
        }

        // Render login form (GET request or failed POST)
        $view        = new BaseView();
        $this->html  = new View();
        $this->title = "Login Page";
        $view->startHead($this->title);
        $this->html->cssLogin();
        $view->endHead();
        $view->startBody($this->title);

        if ($loginError !== null) {
            echo '<p style="color:red;text-align:center">' . htmlspecialchars($loginError) . '</p>';
        }

        $this->html->login();
        $this->html->validate();

        $view->endBody();
        $view->startFooter();
        $view->endFooter();
    }

    private function logout()
    {

        session_unset();
        session_destroy();
        //session_write_close()
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

        //header("Refresh: 3; url=$this->url")
        //echo "Bye! You will be redirected to the home page in 3 seconds!"
        header("Location: $this->url");
    }

    private function signUp()
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
            //$stmt           = $user->createUserSignup()
            $user->createUserSignup();
            //confirmation email
            $this->url     = BASE_URL . "/activation?token=" . $token;
            $to      = $_POST['email'];
            $from    = 'office@bitsworld.ro';
            $subject = "Account Activation";
            $message = '<p>Activate your account:
            <br> <a href="' . $this->url . '"> Click on confirmation link.</a></p>';
            $newSent->sendMailByPHPMailer($to, $from, $subject, $message);
            $this->html->welcomeNew();
        } else {
            $this->html->signup();
            $this->html->validateSignUp();
        }

        $view->endBody();
        $view->startFooter();
        $view->endFooter();
    }

    private function tokenGenerator(int $tokenLength): string
    {
        // random_bytes gives cryptographically secure random data; bin2hex doubles the length
        $bytes = (int) ceil($tokenLength / 2);
        return substr(bin2hex(random_bytes($bytes)), 0, $tokenLength);
    }

    private function accountActivation()
    {
        $this->html = new View();
        $user = new Model();
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
    }
}











