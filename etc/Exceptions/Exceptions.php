<?php
/*
 *   Created on October 11, 2025
 *   Copyright (c) 2025 BitsHost
 *   All rights reserved.
 *
 *   Enhanced upMVC - Custom Exceptions
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

/**
 * Authentication Exception
 */
class AuthenticationException extends upMVCException
{
    public function getHttpStatusCode(): int
    {
        return 401;
    }

    public function getErrorType(): string
    {
        return 'AUTHENTICATION_FAILED';
    }
}

/**
 * Authorization Exception
 */
class AuthorizationException extends upMVCException
{
    public function getHttpStatusCode(): int
    {
        return 403;
    }

    public function getErrorType(): string
    {
        return 'AUTHORIZATION_FAILED';
    }
}

/**
 * Validation Exception
 */
class ValidationException extends upMVCException
{
    /**
     * @var array
     */
    private array $errors = [];

    public function __construct(string $message = 'Validation failed', array $errors = [], int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getHttpStatusCode(): int
    {
        return 422;
    }

    public function getErrorType(): string
    {
        return 'VALIDATION_FAILED';
    }
}

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