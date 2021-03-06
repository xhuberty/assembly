<?php

namespace Assembly\Test;

use Assembly\FactoryCallDefinition;
use Assembly\Reference;

class FactoryCallDefinitionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function supports_service_method_call()
    {
        $definition = new FactoryCallDefinition(new Reference('service'), 'method');

        $this->assertEquals(new Reference('service'), $definition->getFactory());
    }

    /**
     * @test
     */
    public function supports_static_call()
    {
        $definition = new FactoryCallDefinition('SomeClass', 'method');

        $this->assertSame('SomeClass', $definition->getFactory());
    }

    /**
     * @test
     */
    public function accepts_arguments()
    {
        $definition = new FactoryCallDefinition('id', new Reference('service'), 'method');
        $definition->setArguments('param1', 'param2');

        $this->assertSame(['param1', 'param2'], $definition->getArguments());
    }

    /**
     * @test
     */
    public function is_fluent()
    {
        $definition = new FactoryCallDefinition('id', new Reference('service'), 'method');

        $this->assertSame($definition, $definition->setArguments('param1'));
    }
}
