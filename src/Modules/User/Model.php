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

namespace App\Modules\User;

use App\Common\Bmvc\BaseModel;

class Model extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
    }
    public function getUserById($userId, $table)
    {
        return $this->read($userId, $table);
    }

    public function getAllUsers($table)
    {
        return $this->readAll($table);
    }

    public function getUsersWithPagination($table, $page, $pageSize)
    {
        return $this->readWithPagination($table, $page, $pageSize);
    }

    public function createUser($userData, $table)
    {
        return $this->create($userData, $table);
    }

    public function updateUser($userId, $userData, $table)
    {
        return $this->update($userId, $userData, $table);
    }

    public function deleteUser($userId, $table)
    {
        return $this->delete($userId, $table);
    }
}











