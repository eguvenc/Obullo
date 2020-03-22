<?php

use PHPUnit\Framework\TestCase;
use Laminas\ServiceManager\ServiceManager;
use Obullo\Http\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;

class LazyPageFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = new ServiceManager;
        $this->container->addAbstractFactory(new Obullo\Factory\LazyPageFactory);
        $this->container->setFactory(ServerRequest::class, 'Obullo\Factory\RequestFactory');
        $this->container->setAlias(Psr\Http\Message\ServerRequestInterface::class, ServerRequest::class);
        $this->container->setFactory(Laminas\View\HelperPluginManager::class, 'App\Factory\PluginManagerFactory');
        $this->container->setFactory(Laminas\View\Renderer\RendererInterface::class, 'App\Factory\RendererFactory');
    }

    public function testFactory()
    {
        $instance = $this->container->build('Tests\Pages\TestModel');
    
        $this->assertInstanceOf(ServerRequest::class, $instance->getRequestObject());
        $this->assertInstanceOf('Tests\Pages\TestModel', $instance);
    }
}
