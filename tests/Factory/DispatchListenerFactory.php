<?php

use PHPUnit\Framework\TestCase;
use Laminas\ServiceManager\ServiceManager;

class DispatchListenerFactoryTest extends TestCase
{
    public function setUp() : void
    {
        $this->container = new ServiceManager;
        $this->container->setFactory('DispatchListener', 'Obullo\Factory\DispatchListenerFactory');
    }

    public function testFactory()
    {
        $instance = $this->container->get('DispatchListener');
        
        $this->assertInstanceOf('Obullo\DispatchListener', $instance);
    }
}
