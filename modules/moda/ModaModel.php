<?php
/*
 * Created on Tue Oct 31 2023
 *
 * Copyright (c) 2023 BitsHost
 */

namespace Moda;

use Common\Bmvc\BaseModel;

/**
 * ModaModel
 */
class ModaModel extends BaseModel
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
        $this->name  = $name;
        $this->email = $email;
    }
}
