<?php

use PHPUnit\Framework\TestCase;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Config\Config;
use Obullo\Router\Router;
use Obullo\Http\ServerRequest;

class RouterFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = new ServiceManager;
        $this->container->setFactory(ServerRequest::class, 'Obullo\Factory\RequestFactory');
        $this->container->setFactory(Config::class, 'App\Factory\ConfigFactory');
        $this->container->setFactory(Router::class, 'Obullo\Factory\RouterFactory');
    }

    public function testFactory()
    {
        $instance = $this->container->get(Router::class);
        
        $this->assertInstanceOf('Obullo\Router\Router', $instance);
    }
}
