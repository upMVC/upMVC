<?php
/*
 * Created on Tue Oct 31 2023
 *
 * Copyright (c) 2023 BitsHost
 */

namespace Suba;

/**
 * SubaModel
 */
class SubaModel
{
    public $name;
    public $email;
    
    /**
     * __construct
     *
     * @param  mixed $name
     * @param  mixed $email
     * @return void
     */
    public function __construct($name, $email)
    {
        $this->name  = $name;
        $this->email = $email;
    }

}