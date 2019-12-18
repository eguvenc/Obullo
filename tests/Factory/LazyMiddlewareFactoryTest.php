<?php

use PHPUnit\Framework\TestCase;
use Zend\ServiceManager\ServiceManager;
use Obullo\Http\ServerRequest;

class LazyMiddlewareFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = new ServiceManager;
        $this->container->addAbstractFactory(new Obullo\Factory\LazyMiddlewareFactory);
        $this->container->setFactory(ServerRequest::class, 'Obullo\Factory\RequestFactory');
        $this->container->setAlias('request', ServerRequest::class);

        $this->container->setFactory(Zend\Config\Config::class, 'App\Factory\ConfigFactory');
        $this->container->setFactory(Obullo\Router\Router::class, 'Obullo\Factory\RouterFactory');
        $this->container->setFactory(Zend\View\HelperPluginManager::class, 'App\Factory\PluginManagerFactory');
        $this->container->setFactory(Zend\View\Renderer\RendererInterface::class, 'App\Factory\RendererFactory');
    }

    public function testFactory()
    {
        $instance = $this->container->build('App\Middleware\HttpMethodMiddleware');

        $this->assertInstanceOf('App\Middleware\HttpMethodMiddleware', $instance);
    }
}
