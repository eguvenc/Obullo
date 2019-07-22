<?php

use PHPUnit\Framework\TestCase;
use Zend\ServiceManager\ServiceManager;
use Zend\EventManager\EventManager;

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
        
        $this->assertInstanceOf('Zend\EventManager\EventManager', $instance);
    }
}
