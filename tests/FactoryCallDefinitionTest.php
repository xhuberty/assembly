<?php

namespace Assembly\Test;

use Assembly\Container\Container;
use Assembly\FactoryCallDefinition;
use Assembly\Reference;
use Assembly\Test\Container\FakeDefinitionProvider;

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

    /**
     * @test
     */
    public function invoke_static_call()
    {
        $definition = new FactoryCallDefinition('Assembly\\Test\\Container\\Fixture\\Factory', 'staticCreate');
        $container = new Container([]);
        $this->assertSame("Hello", $definition($container));
    }

    /**
     * @test
     */
    public function invoke_static_call_with_argument()
    {
        $definition = new FactoryCallDefinition('Assembly\\Test\\Container\\Fixture\\Factory', 'staticCreateWithArgument');
        $definition->setArguments('ShelDon');
        $container = new Container([]);
        $this->assertSame("Hello ShelDon", $definition($container));
    }

    /**
     * @test
     */
    public function invoke_static_call_with_reference()
    {
        $reference = new Reference('ref');
        $definition = new FactoryCallDefinition($reference, 'staticCreate');
        $provider = new FakeDefinitionProvider([
            'foo' => $definition,
        ]);

        $container = new Container([
            'ref' => 'Assembly\\Test\\Container\\Fixture\\Factory',
        ], [$provider]);
        $this->assertSame("Hello", $definition($container));
    }
    
}
