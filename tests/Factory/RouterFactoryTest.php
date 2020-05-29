<?php

use PHPUnit\Framework\TestCase;
use Laminas\ServiceManager\ServiceManager;
use Laminas\EventManager\EventManager;

use Obullo\Router\Router;
use Obullo\Application;
use Obullo\Http\ServerRequest;
use Obullo\Container\ServiceManagerConfig;

use Laminas\Config\Config;
use Laminas\ModuleManager\ModuleManager;

class RouterFactoryTest extends TestCase
{
    public function setUp()
    {
        $appConfig = require dirname(__DIR__).'/config/application.config.php';

        $smConfig = isset($appConfig['service_manager']) ? $appConfig['service_manager'] : [];
        $smConfig = new Obullo\Container\ServiceManagerConfig($smConfig);
        $this->container = new ServiceManager;
        $smConfig->configureServiceManager($this->container);
        $this->container->setService('appConfig', $appConfig);
        $this->container->addAbstractFactory(new Obullo\Factory\LazyDefaultFactory);

        $this->container->setFactory(Config::class, 'Obullo\Factory\ConfigFactory');
        $this->container->setFactory('ModuleManager', 'Obullo\Factory\ModuleManagerFactory');
        $this->container->setFactory(ModuleManager::class, 'Obullo\Factory\ModuleManagerFactory');
        $this->container->setFactory(ServerRequest::class, 'Obullo\Factory\RequestFactory');
        $this->container->setAlias(Psr\Http\Message\ServerRequestInterface::class, ServerRequest::class);
        $this->container->setFactory(Laminas\Config\Config::class, 'Obullo\Factory\ConfigFactory');
        $this->container->setFactory(Obullo\Router\Router::class, 'Obullo\Factory\RouterFactory');
    }

    public function testFactory()
    {
        $instance = $this->container->get(Router::class);
        
        $this->assertInstanceOf('Obullo\Router\Router', $instance);
    }
}
