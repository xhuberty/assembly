<?php

namespace Assembly;

use Interop\Container\ContainerInterface;
use Interop\Container\Definition\ParameterDefinitionInterface;

class ParameterDefinition implements ParameterDefinitionInterface
{
    /**
     * @var string
     */
    private $value;

    /**
     * @param string $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    public function __invoke(ContainerInterface $container, callable $previous = null) {
        return $this->value;
    }
    
    public function getValue()
    {
        return $this->value;
    }
}
