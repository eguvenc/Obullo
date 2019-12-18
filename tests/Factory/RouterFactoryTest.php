<?php

use PHPUnit\Framework\TestCase;
use Zend\ServiceManager\ServiceManager;
use Zend\Config\Config;
use Obullo\Router\Router;
use Obullo\Http\ServerRequest;

class RouterFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = new ServiceManager;
        $this->container->setFactory(ServerRequest::class, 'Obullo\Factory\RequestFactory');
        $this->container->setFactory(Config::class, 'Obullo\Factory\ConfigFactory');
        $this->container->setFactory(Router::class, 'Obullo\Factory\RouterFactory');
    }

    public function testFactory()
    {
        $instance = $this->container->get(Router::class);
        
        $this->assertInstanceOf('Obullo\Router\Router', $instance);
    }
}
