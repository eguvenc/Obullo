<?php

use PHPUnit\Framework\TestCase;
use Zend\ServiceManager\ServiceManager;
use Obullo\Http\ServerRequest;

class LazyPageFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = new ServiceManager;
        $this->container->addAbstractFactory(new Obullo\Factory\LazyPageFactory);
        $this->container->setFactory(ServerRequest::class, 'Obullo\Factory\RequestFactory');
        $this->container->setAlias('request', ServerRequest::class);
        $this->container->setFactory(Zend\View\HelperPluginManager::class, 'App\Factory\PluginManagerFactory');
        $this->container->setFactory(Zend\View\Renderer\RendererInterface::class, 'App\Factory\RendererFactory');
    }

    public function testFactory()
    {
        $instance = $this->container->build('Tests\Pages\TestModel');
    
        $this->assertInstanceOf(ServerRequest::class, $instance->getTestRequest());
        $this->assertInstanceOf('Tests\Pages\TestModel', $instance);
    }
}
