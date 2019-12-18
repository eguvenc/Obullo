<?php

use PHPUnit\Framework\TestCase;
use Zend\ServiceManager\ServiceManager;
use Obullo\Http\ServerRequest;

class PluginManagerFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = new ServiceManager;
        $this->container->setFactory(ServerRequest::class, 'Obullo\Factory\RequestFactory');
        $this->container->setFactory(Zend\View\HelperPluginManager::class, 'App\Factory\PluginManagerFactory');
    }

    public function testFactory()
    {
        $instance = $this->container->get(Zend\View\HelperPluginManager::class);
        
        $this->assertInstanceOf('Obullo\Http\ServerRequest', $instance);
        $this->assertInstanceOf('Zend\View\HelperPluginManager', $instance);
    }
}