<?php
/*
 *   Created on October 11, 2025
 *   Copyright (c) 2025 BitsHost
 *   All rights reserved.
 *
 *   Enhanced upMVC - Route Not Found Exception
 */

namespace App\Etc\Exceptions;

/**
 * Route Not Found Exception
 */
class RouteNotFoundException extends upMVCException
{
    public function getHttpStatusCode(): int
    {
        return 404;
    }

    public function getErrorType(): string
    {
        return 'ROUTE_NOT_FOUND';
    }
}




