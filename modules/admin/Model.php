<?php

namespace Admin;

use Common\Bmvc\BaseModel;


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
