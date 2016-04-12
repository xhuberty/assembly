<?php

namespace Assembly;

use Assembly\Container\DefinitionResolver;
use Assembly\ObjectInitializer\MethodCall;
use Assembly\ObjectInitializer\PropertyAssignment;
use Interop\Container\ContainerInterface;
use Interop\Container\Definition\ObjectDefinitionInterface;
use Interop\Container\Definition\ObjectInitializer\MethodCallInterface;
use Interop\Container\Definition\ObjectInitializer\PropertyAssignmentInterface;
use Interop\Container\Definition\ReferenceDefinitionInterface;

class ObjectDefinition implements ObjectDefinitionInterface
{
    /**
     * @var string
     */
    private $className;

    /**
     * @var array
     */
    private $constructorArguments = [];

    /**
     * @var PropertyAssignmentInterface[]
     */
    private $propertyAssignments = [];

    /**
     * @var MethodCallInterface[]
     */
    private $methodCalls = [];

    /**
     * @param string $className
     * @param array  $constructorArguments
     */
    public function __construct($className, array $constructorArguments = [])
    {
        $this->className = $className;
        $this->constructorArguments = $constructorArguments;
    }

    public function __invoke(ContainerInterface $container, callable $previous = null) {
        $reflection = new \ReflectionClass($this->getClassName());

        // Create the instance
        $constructorArguments = $this->getConstructorArguments();
        $constructorArguments = array_map(function($item) use ($container) {
            return DefinitionResolver::resolveSubDefinition($item, $container);
        }, $constructorArguments);
        $service = $reflection->newInstanceArgs($constructorArguments);

        // Set properties
        foreach ($this->getPropertyAssignments() as $propertyAssignment) {
            $propertyName = $propertyAssignment->getPropertyName();
            $service->$propertyName = DefinitionResolver::resolveSubDefinition($propertyAssignment->getValue(),$container);
        }

        // Call methods
        foreach ($this->getMethodCalls() as $methodCall) {
            $methodArguments = $methodCall->getArguments();
            $methodArguments = array_map(function($item) use ($container) {
                return DefinitionResolver::resolveSubDefinition($item, $container);
            }, $methodArguments);
            call_user_func_array([$service, $methodCall->getMethodName()], $methodArguments);
        }

        return $service;
    }

    /**
     * @param string|number|bool|array|ReferenceDefinitionInterface $argument
     *
     * @return $this
     */
    public function addConstructorArgument($argument)
    {
        $this->constructorArguments[] = $argument;

        return $this;
    }

    /**
     * Set constructor arguments. This method take as many parameters as necessary.
     *
     * @param string|number|bool|array|ReferenceDefinitionInterface $argument Can be a scalar value or a reference to another entry.
     * @param string|number|bool|array|ReferenceDefinitionInterface ...
     *
     * @return $this
     */
    public function setConstructorArguments($argument)
    {
        $this->constructorArguments = func_get_args();

        return $this;
    }

    /**
     * Set a value to assign to a property.
     *
     * @param string $propertyName Name of the property to set.
     * @param string|number|bool|array|ReferenceDefinitionInterface $value Can be a scalar value or a reference to another entry.
     *
     * @return $this
     */
    public function addPropertyAssignment($propertyName, $value)
    {
        $this->propertyAssignments[] = new PropertyAssignment($propertyName, $value);

        return $this;
    }

    /**
     * Set a method to be called after instantiating the class.
     *
     * After the $methodName parameter, this method take as many parameters as necessary.
     *
     * @param string $methodName Name of the method to call.
     * @param string|number|bool|array|ReferenceDefinitionInterface... Can be a scalar value, an array of scalar or
     * a reference to another entry. See \Assembly\ObjectInitializer\MethodCall::__construct fore more informations.
     *
     * @return $this
     */
    public function addMethodCall($methodName)
    {
        $arguments = func_get_args();
        array_shift($arguments);

        $this->methodCalls[] = new MethodCall($methodName, $arguments);

        return $this;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @return array
     */
    public function getConstructorArguments()
    {
        return $this->constructorArguments;
    }

    /**
     * @return PropertyAssignmentInterface[]
     */
    public function getPropertyAssignments()
    {
        return $this->propertyAssignments;
    }

    /**
     * @return MethodCallInterface[]
     */
    public function getMethodCalls()
    {
        return $this->methodCalls;
    }

}
