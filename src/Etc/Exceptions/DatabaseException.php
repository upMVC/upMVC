<?php
/*
 *   Created on October 11, 2025
 *   Copyright (c) 2025 BitsHost
 *   All rights reserved.
 *
 *   Enhanced upMVC - Database Exception
 */

namespace App\Etc\Exceptions;

/**
 * Database Exception
 */
class DatabaseException extends upMVCException
{
    public function getHttpStatusCode(): int
    {
        return 500;
    }

    public function getErrorType(): string
    {
        return 'DATABASE_ERROR';
    }
}




