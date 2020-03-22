<?php

use PHPUnit\Framework\TestCase;
use Laminas\ServiceManager\ServiceManager;
use Laminas\EventManager\EventManager;

class EventManagerFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = new ServiceManager;
        $this->container->setFactory(EventManager::class, 'Obullo\Factory\EventManagerFactory');
    }

    public function testFactory()
    {
        $instance = $this->container->get(EventManager::class);
        
        $this->assertInstanceOf('Laminas\EventManager\EventManager', $instance);
    }
}
