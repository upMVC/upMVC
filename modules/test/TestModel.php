<?php
/*
 * Created on Tue Oct 31 2023
 *
 * Copyright (c) 2023 BitsHost
 */

namespace Test;

use Common\Bmvc\BaseModel;

/**
 * TestModel
 */
class TestModel extends BaseModel
{
    public $name;
    public $email;
    protected $conn;

    /**
     * __construct
     *
     * @param  mixed $name
     * @param  mixed $email
     * @return void
     */
    public function __construct($name, $email)
    {
        parent::__construct();
        //$this->conn; You may now utilize it here.  $this->conn is used to construct queries.
        $this->name  = $name;
        $this->email = $email;
    }
}
