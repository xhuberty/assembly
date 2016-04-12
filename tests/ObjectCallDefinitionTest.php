<?php

namespace Assembly\Test;

use Assembly\Container\Container;
use Assembly\ObjectDefinition;
use Assembly\Test\Container\FakeDefinitionProvider;

class ObjectCallDefinitionTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function invoke_with_object_definition()
    {
        $serviceName = 'my_object';
        $serviceClass = 'ArrayObject';
        $serviceDefinition = new ObjectDefinition($serviceClass);
        $provider = new FakeDefinitionProvider([
            $serviceName => $serviceDefinition
        ]);
        $container = new Container([], [$provider]);
        $this->assertInstanceOf($serviceClass, $serviceDefinition($container));
    }
    
}
