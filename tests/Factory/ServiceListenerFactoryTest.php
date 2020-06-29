<?php

use PHPUnit\Framework\TestCase;
use Laminas\ServiceManager\ServiceManager;
use Obullo\Factory\ServiceListenerConsoleFactory;

class ServiceListenerFactoryTest extends TestCase
{
    public function setUp() : void
    {
        $appConfig = require __DIR__.'/../config/application.config.php';
        $appConfig['service_manager']['factories']['ServiceListener'] = ServiceListenerConsoleFactory::class;

        $smConfig = new Obullo\Container\ServiceManagerConfig($appConfig['service_manager']);
        $this->container = new ServiceManager;
        $smConfig->configureServiceManager($this->container);
        $this->container->setService('appConfig', $appConfig);
        // $this->container->setFactory('ServiceListener', 'Obullo\Factory\ServiceListenerFactory');
    }

    public function testFactory()
    {
        $instance = $this->container->get('ServiceListener');
        
        $this->assertInstanceOf('Laminas\ModuleManager\Listener\ServiceListener', $instance);
    }
}
