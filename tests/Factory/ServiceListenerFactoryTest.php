<?php

use PHPUnit\Framework\TestCase;
use Laminas\ServiceManager\ServiceManager;

class ServiceListenerFactoryTest extends TestCase
{
    public function setUp()
    {
        $appConfig = require __DIR__.'/../config/application.config.php';

        $smConfig = isset($appConfig['service_manager']) ? $appConfig['service_manager'] : [];
        $smConfig = new Obullo\Container\ServiceManagerConfig($smConfig);
        $this->container = new ServiceManager;
        $smConfig->configureServiceManager($this->container);
        $this->container->setService('appConfig', $appConfig);
        $this->container->setFactory('ServiceListener', 'Obullo\Factory\ServiceListenerFactory');
    }

    public function testFactory()
    {
        $instance = $this->container->get('ServiceListener');
        
        $this->assertInstanceOf('Laminas\ModuleManager\Listener\ServiceListener', $instance);
    }
}
