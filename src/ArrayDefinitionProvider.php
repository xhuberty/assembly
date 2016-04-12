<?php

namespace Assembly;

use Interop\Container\ContainerInterface;
use Interop\Container\Definition\DefinitionInterface;
use Interop\Container\Definition\DefinitionProviderInterface;

class ArrayDefinitionProvider implements DefinitionProviderInterface
{
    private $arrayDefinitions;

    public function __construct(array $arrayDefinitions = [])
    {
        $this->arrayDefinitions = $arrayDefinitions;
    }

    public function __invoke(ContainerInterface $container, callable $previous = null) {
        $definitions = [];

        foreach ($this->getArrayDefinitions() as $identifier => $definition) {
            if (!$definition instanceof DefinitionInterface) {
                $definition = new ParameterDefinition($definition);
            }

            $definitions[$identifier] = $definition;
        }

        return $definitions;
    }

    /**
     * Implement this method to return the definitions as PHP array.
     *
     * @return array
     */
    protected function getArrayDefinitions()
    {
        return $this->arrayDefinitions;
    }

    public function getDefinitions()
    {
        $definitions = [];

        foreach ($this->getArrayDefinitions() as $identifier => $definition) {
            if (!$definition instanceof DefinitionInterface) {
                $definition = new ParameterDefinition($definition);
            }

            $definitions[$identifier] = $definition;
        }

        return $definitions;
    }
}
