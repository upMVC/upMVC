<?php
/*
 *   Created on October 11, 2025
 *   Copyright (c) 2025 BitsHost
 *   All rights reserved.
 *
 *   Enhanced upMVC - Configuration Exception
 */

namespace upMVC\Exceptions;

/**
 * Configuration Exception
 */
class ConfigurationException extends upMVCException
{
    public function getHttpStatusCode(): int
    {
        return 500;
    }

    public function getErrorType(): string
    {
        return 'CONFIGURATION_ERROR';
    }
}