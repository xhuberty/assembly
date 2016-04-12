<?php

namespace Assembly\Container;

use Interop\Container\ContainerInterface;
use Interop\Container\Definition\DefinitionInterface;
use Interop\Container\Definition\FactoryCallDefinitionInterface;
use Interop\Container\Definition\ParameterDefinitionInterface;
use Interop\Container\Definition\ReferenceDefinitionInterface;
use Interop\Container\Definition\ObjectDefinitionInterface;

/**
 * Resolves standard definitions.
 */
class DefinitionResolver
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Resolve a definition and return the resulting value.
     *
     * @param DefinitionInterface $definition
     *
     * @return mixed
     *
     * @throws UnsupportedDefinition
     * @throws InvalidDefinition
     * @throws EntryNotFound A dependency was not found.
     */
    public function resolve(DefinitionInterface $definition, ContainerInterface $container)
    {
        switch (true) {
            case $definition instanceof ReferenceDefinitionInterface:
                return $this->container->get($definition->getTarget());

            case $definition instanceof ParameterDefinitionInterface:
                return $definition->getValue();

            case $definition instanceof ObjectDefinitionInterface:
                $reflection = new \ReflectionClass($definition->getClassName());

                // Create the instance
                $constructorArguments = $definition->getConstructorArguments();
                $constructorArguments =  array_map(function($item) use ($container) {
                    return DefinitionResolver::resolveSubDefinition($item, $container);
                }, $constructorArguments);
                $service = $reflection->newInstanceArgs($constructorArguments);

                // Set properties
                foreach ($definition->getPropertyAssignments() as $propertyAssignment) {
                    $propertyName = $propertyAssignment->getPropertyName();
                    $service->$propertyName = $this->resolveSubDefinition($propertyAssignment->getValue(), $container);
                }

                // Call methods
                foreach ($definition->getMethodCalls() as $methodCall) {
                    $methodArguments = $methodCall->getArguments();
                    $methodArguments = array_map(function($item) use ($container) {
                        return DefinitionResolver::resolveSubDefinition($item, $container);
                    },  $methodArguments);
                    call_user_func_array([$service, $methodCall->getMethodName()], $methodArguments);
                }

                return $service;

            case $definition instanceof FactoryCallDefinitionInterface:
                $factory = $definition->getFactory();
                $methodName = $definition->getMethodName();
                $arguments = (array) $definition->getArguments();
                $arguments = array_map(function($item) use ($container) {
                    return DefinitionResolver::resolveSubDefinition($item, $container);
                },  $arguments);

                if (is_string($factory)) {
                    return call_user_func_array([$factory, $methodName], $arguments);
                } elseif ($factory instanceof ReferenceDefinitionInterface) {
                    $factory = $this->container->get($factory->getTarget());
                    return call_user_func_array([$factory, $methodName], $arguments);
                }
                throw new InvalidDefinition(sprintf('Definition "%s" does not return a valid factory'));

            default:
                throw UnsupportedDefinition::fromDefinition($definition);
        }
    }

    /**
     * Resolve a variable that can be a sub-definition.
     *
     * @param mixed|DefinitionInterface $value
     * @return mixed
     */
    public static function resolveSubDefinition($value, ContainerInterface $container)
    {
        if (is_array($value)) {
            return array_map(function($item) use ($container) {
                return self::resolveSubDefinition($item, $container);
            }, $value);
        } elseif ( is_callable($value) ) {
            return $value($container);
        }

        return $value;
    }
}
