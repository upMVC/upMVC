<?php
/*
 * Created on Tue Oct 31 2023
 *
 * Copyright (c) 2023 BitsHost
 */

namespace Common\Bmvc;

use MVC\Database;


class BaseModel
{
    protected $conn;

    public function __construct()
    {
        $dbConnection = Database::getInstance();
        $this->conn   = $dbConnection->getConnection();
    }
}
