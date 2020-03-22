<?php

use PHPUnit\Framework\TestCase;
use Laminas\ServiceManager\ServiceManager;
use Obullo\Http\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;

class LazyMiddlewareFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = new ServiceManager;
        $this->container->addAbstractFactory(new Obullo\Factory\LazyMiddlewareFactory);
        $this->container->setFactory(ServerRequest::class, 'Obullo\Factory\RequestFactory');
        $this->container->setAlias(Psr\Http\Message\ServerRequestInterface::class, ServerRequest::class);

        $this->container->setFactory(Laminas\Config\Config::class, 'App\Factory\ConfigFactory');
        $this->container->setFactory(Obullo\Router\Router::class, 'Obullo\Factory\RouterFactory');
        $this->container->setFactory(Laminas\View\HelperPluginManager::class, 'App\Factory\PluginManagerFactory');
        $this->container->setFactory(Laminas\View\Renderer\RendererInterface::class, 'App\Factory\RendererFactory');
    }

    public function testFactory()
    {
        $instance = $this->container->build('App\Middleware\HttpMethodMiddleware');

        $this->assertInstanceOf('App\Middleware\HttpMethodMiddleware', $instance);
    }
}
