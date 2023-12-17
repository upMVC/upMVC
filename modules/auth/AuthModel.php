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
use Common\Bmvc\BaseModel;

class AuthModel extends BaseModel
{
    var $username;
    var $password;
    var $email;
    var $token;
    var $state;
    var $name;
    public function __construct()
    {
        parent::__construct();
    }


    ///read user login////////////////
    public function readUserLogin()
    {
        $q = "SELECT id, username, password, state
          FROM users
          WHERE username = :username
          AND password = :password";

        $stmt = $this->conn->prepare($q);
        //sanitize
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->password     = htmlspecialchars(strip_tags($this->password));
        //bind
        $stmt->bindValue(':username', $this->username);
        $stmt->bindValue(':password', $this->password);

        if ($stmt->execute()) {
            return $stmt;
        }
        return false;
    }


    ///end read login//////////////////
    public function createUserSignup()
    {
        $q = "INSERT INTO users (name, username, password, email, token)
              VALUES (:name, :username, :password, :email, :token)";

        $stmt = $this->conn->prepare($q);
        //sanitize
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->password     = htmlspecialchars(strip_tags($this->password));
        $this->email    = htmlspecialchars(strip_tags($this->email));
        $this->token    = htmlspecialchars(strip_tags($this->token));
        
        //bind
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":token", $this->token);
        //$stmt->bindParam(":reg_comert", $this->reg_comert);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }


    //check activation token 
    public function readUserToken()
    {
        $q = "SELECT token
              FROM users
              WHERE token = :token";

        $stmt = $this->conn->prepare($q);
         //sanitize
        $this->token = htmlspecialchars(strip_tags($this->token));
        //bind
        $stmt->bindValue(':token', $this->token);
        $stmt->execute();
        return $stmt;
    }


    //read user token - set activ
    public function setActiveUser()
    {
        $q = "UPDATE users
              SET state = :state
              WHERE token = :token";

        $stmt = $this->conn->prepare($q);
        //sanitize
        $this->state = htmlspecialchars(strip_tags($this->state));
        $this->token = htmlspecialchars(strip_tags($this->token));
        //bind
        $stmt->bindValue(':state', $this->state);
        $stmt->bindValue(':token', $this->token);
      
        $stmt->execute();
    }


    //read user where token value is
    public function readUsernameByToken()
    {
        $q = "SELECT username
          FROM users
          WHERE token = :token";


        $stmt = $this->conn->prepare($q);

        //sanitize
        $this->token = htmlspecialchars(strip_tags($this->token));
        //bind
        $stmt->bindValue(':token', $this->token);


        $stmt->execute();
        return $stmt;
    }
}
