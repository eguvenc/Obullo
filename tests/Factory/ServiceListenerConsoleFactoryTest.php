<?php

use PHPUnit\Framework\TestCase;
use Laminas\ServiceManager\ServiceManager;

class ServiceListenerConsoleFactoryTest extends TestCase
{
    public function setUp() : void
    {
        $appConfig = require __DIR__.'/../config/application.config.php';

        $smConfig = isset($appConfig['service_manager']) ? $appConfig['service_manager'] : [];
        $smConfig = new Obullo\Container\ServiceManagerConfig($smConfig);
        $this->container = new ServiceManager;
        $smConfig->configureServiceManager($this->container);
        $this->container->setService('appConfig', $appConfig);
        $this->container->setFactory('ServiceListener', 'Obullo\Factory\ServiceListenerConsoleFactory');

        /**
         * ! important: without loading modules we can't test services with $container->has() method
         */
        $this->container->get('ModuleManager')->loadModules();
    }

    public function testFactory()
    {
        $instance = $this->container->get('ServiceListener');
    
        $this->assertInstanceOf('Laminas\ModuleManager\Listener\ServiceListener', $instance);
    }

    public function testConsoleServices()
    {
        /**
         * Disabled services for console requests
         */
        $this->assertFalse($this->container->has('Router'));
        $this->assertFalse($this->container->has('Request'));
        $this->assertFalse($this->container->has('DispatchListener'));
        $this->assertFalse($this->container->has('RouteListener'));
        $this->assertFalse($this->container->has('Obullo\Router\RouteCollectionInterface'));
    }
}
