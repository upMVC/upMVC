<?php
/*
 * Created on Tue Oct 31 2023
 *
 * Copyright (c) 2023 BitsHost
 */

namespace MVC;

/**
 * Controller
 */

class Controller
{
    /**
     * render
     *
     * @param  mixed $view
     * @param  mixed $data
     * @return void
     */
    protected function render($view, $data = [])
    {
        extract($data);

        include "./modules/$view.php";
    }
}
