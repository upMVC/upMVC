<?php
/*
 *   Created on October 11, 2025
 *   Copyright (c) 2025 BitsHost
 *   All rights reserved.
 *
 *   Enhanced upMVC - Base Exception
 */

namespace upMVC\Exceptions;

use Exception;

/**
 * Base upMVC Exception
 */
abstract class upMVCException extends Exception
{
    /**
     * @var array
     */
    protected array $context = [];

    /**
     * Set context data
     *
     * @param array $context
     * @return self
     */
    public function setContext(array $context): self
    {
        $this->context = $context;
        return $this;
    }

    /**
     * Get context data
     *
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Get HTTP status code
     *
     * @return int
     */
    abstract public function getHttpStatusCode(): int;

    /**
     * Get error type
     *
     * @return string
     */
    abstract public function getErrorType(): string;
}