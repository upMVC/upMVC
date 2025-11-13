<?php
/*
 *   Created on October 11, 2025
 *   Copyright (c) 2025 BitsHost
 *   All rights reserved.
 *
 *   Enhanced upMVC - Dependency Injection Container
 */

namespace App\Etc\Container;

use ReflectionClass;
use ReflectionParameter;
use Exception;

/**
 * Container
 * 
 * Simple dependency injection container for upMVC
 */
class Container
{
    /**
     * @var array
     */
    private array $bindings = [];

    /**
     * @var array
     */
    private array $instances = [];

    /**
     * @var array
     */
    private array $singletons = [];

    /**
     * Bind a class or interface to a concrete implementation
     *
     * @param string $abstract
     * @param mixed $concrete
     * @param bool $singleton
     * @return void
     */
    public function bind(string $abstract, $concrete = null, bool $singleton = false): void
    {
        if ($concrete === null) {
            $concrete = $abstract;
        }

        $this->bindings[$abstract] = [
            'concrete' => $concrete,
            'singleton' => $singleton
        ];

        if ($singleton) {
            $this->singletons[$abstract] = null;
        }
    }

    /**
     * Bind as singleton
     *
     * @param string $abstract
     * @param mixed $concrete
     * @return void
     */
    public function singleton(string $abstract, $concrete = null): void
    {
        $this->bind($abstract, $concrete, true);
    }

    /**
     * Register an existing instance
     *
     * @param string $abstract
     * @param mixed $instance
     * @return void
     */
    public function instance(string $abstract, $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    /**
     * Resolve a class from the container
     *
     * @param string $abstract
     * @param array $parameters
     * @return mixed
     * @throws Exception
     */
    public function make(string $abstract, array $parameters = [])
    {
        // Check for existing instance
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // Check for singleton
        if (isset($this->singletons[$abstract]) && $this->singletons[$abstract] !== null) {
            return $this->singletons[$abstract];
        }

        // Get concrete implementation
        $concrete = $this->getConcrete($abstract);

        // Build the object
        $object = $this->build($concrete, $parameters);

        // Store singleton
        if (isset($this->bindings[$abstract]) && $this->bindings[$abstract]['singleton']) {
            $this->singletons[$abstract] = $object;
        }

        return $object;
    }

    /**
     * Get concrete implementation
     *
     * @param string $abstract
     * @return mixed
     */
    private function getConcrete(string $abstract)
    {
        if (isset($this->bindings[$abstract])) {
            return $this->bindings[$abstract]['concrete'];
        }

        return $abstract;
    }

    /**
     * Build a concrete instance
     *
     * @param mixed $concrete
     * @param array $parameters
     * @return mixed
     * @throws Exception
     */
    private function build($concrete, array $parameters = [])
    {
        // If concrete is a closure, call it
        if ($concrete instanceof \Closure) {
            return $concrete($this, $parameters);
        }

        // If it's not a class, return as is
        if (!is_string($concrete)) {
            return $concrete;
        }

        try {
            $reflector = new ReflectionClass($concrete);
        } catch (\ReflectionException $e) {
            throw new Exception("Target class [{$concrete}] does not exist.");
        }

        if (!$reflector->isInstantiable()) {
            throw new Exception("Target [{$concrete}] is not instantiable.");
        }

        $constructor = $reflector->getConstructor();

        if ($constructor === null) {
            return new $concrete();
        }

        $dependencies = $this->resolveDependencies($constructor->getParameters(), $parameters);

        return $reflector->newInstanceArgs($dependencies);
    }

    /**
     * Resolve constructor dependencies
     *
     * @param array $dependencies
     * @param array $parameters
     * @return array
     * @throws Exception
     */
    private function resolveDependencies(array $dependencies, array $parameters = []): array
    {
        $results = [];

        foreach ($dependencies as $dependency) {
            $result = $this->resolveDependency($dependency, $parameters);
            $results[] = $result;
        }

        return $results;
    }

    /**
     * Resolve a single dependency
     *
     * @param ReflectionParameter $parameter
     * @param array $parameters
     * @return mixed
     * @throws Exception
     */
    private function resolveDependency(ReflectionParameter $parameter, array $parameters = [])
    {
        $name = $parameter->getName();

        // Check if parameter was provided
        if (array_key_exists($name, $parameters)) {
            return $parameters[$name];
        }

        // Get parameter type
        $type = $parameter->getType();

        if ($type === null) {
            // No type hint, check for default value
            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }

            throw new Exception("Cannot resolve parameter [{$name}] without type hint or default value.");
        }

        // Handle union types (PHP 8+)
        if ($type instanceof \ReflectionUnionType) {
            foreach ($type->getTypes() as $unionType) {
                if (!$unionType->isBuiltin()) {
                    $typeName = $unionType->getName();
                    break;
                }
            }
            if (!isset($typeName)) {
                throw new Exception("Cannot resolve union type parameter [{$name}] - no concrete type found.");
            }
        } else {
            // Handle ReflectionNamedType
            if ($type instanceof \ReflectionNamedType) {
                $typeName = $type->getName();
            } else {
                throw new Exception("Unsupported type for parameter [{$name}].");
            }
        }

        // Built-in types
        if ($type instanceof \ReflectionNamedType && $type->isBuiltin()) {
            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }

            throw new Exception("Cannot resolve built-in type [{$typeName}] for parameter [{$name}].");
        }

        // Resolve class dependency
        return $this->make($typeName);
    }

    /**
     * Check if abstract is bound
     *
     * @param string $abstract
     * @return bool
     */
    public function bound(string $abstract): bool
    {
        return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]);
    }

    /**
     * Call a method and inject its dependencies
     *
     * @param mixed $callback
     * @param array $parameters
     * @return mixed
     * @throws Exception
     */
    public function call($callback, array $parameters = [])
    {
        if (is_string($callback) && strpos($callback, '@') !== false) {
            list($class, $method) = explode('@', $callback);
            $callback = [$this->make($class), $method];
        }

        if (is_array($callback)) {
            list($class, $method) = $callback;
            
            if (is_string($class)) {
                $class = $this->make($class);
            }

            $reflector = new \ReflectionMethod($class, $method);
            $dependencies = $this->resolveDependencies($reflector->getParameters(), $parameters);

            return $reflector->invokeArgs($class, $dependencies);
        }

        if ($callback instanceof \Closure) {
            $reflector = new \ReflectionFunction($callback);
            $dependencies = $this->resolveDependencies($reflector->getParameters(), $parameters);

            return $reflector->invokeArgs($dependencies);
        }

        return call_user_func_array($callback, $parameters);
    }
}




