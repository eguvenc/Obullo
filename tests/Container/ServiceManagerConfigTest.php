<?php

use PHPUnit\Framework\TestCase;
use Laminas\Diactoros\Uri;
use Obullo\Http\ServerRequest;
use Laminas\ServiceManager\ServiceManager;

class ServiceManagerConfigTest extends TestCase
{
    public function setUp() : void
    {
        $appConfig = require __DIR__ . '/../config/application.config.php';
        $smConfig = isset($appConfig['service_manager']) ? $appConfig['service_manager'] : [];
        $this->smConfig = new Obullo\Container\ServiceManagerConfig($smConfig);

        // setup service manager
        //
        $this->container = new ServiceManager;
        $this->smConfig->configureServiceManager($this->container);
        $this->container->setService('appConfig', $appConfig);

        // load app modules
        //
        $this->container->get('ModuleManager')->loadModules();
    }

    public function testConfiguredServicesByDefault()
    {
        $smConfig = $this->smConfig->toArray();

        $aliases = $smConfig['aliases'];
        $factories = $smConfig['factories'];
        $abstract_factories = $smConfig['abstract_factories'];

        /**
         * Aliases
         */
        $this->assertEquals($aliases['EventManagerInterface'], 'Laminas\EventManager\EventManager');
        $this->assertEquals($aliases['Laminas\EventManager\EventManagerInterface'], 'EventManager');
        $this->assertEquals($aliases['Laminas\ModuleManager\ModuleManager'], 'ModuleManager');
        $this->assertEquals($aliases['Laminas\ModuleManager\Listener\ServiceListener'], 'ServiceListener');
        $this->assertEquals($aliases['Laminas\EventManager\SharedEventManager'], 'SharedEventManager');
        $this->assertEquals($aliases['SharedEventManagerInterface'], 'SharedEventManager');
        $this->assertEquals($aliases['Laminas\EventManager\SharedEventManagerInterface'], 'SharedEventManager');
        /**
         * Factories
         */
        $this->assertEquals('Obullo\Factory\ServiceListenerFactory', $factories['ServiceListener']);
        $this->assertEquals('Obullo\Factory\EventManagerFactory', $factories['EventManager']);
        $this->assertEquals('Obullo\Factory\ModuleManagerFactory', $factories['ModuleManager']);
        /**
         * Shared events
         */
        $this->assertFalse($smConfig['shared']['EventManager']);
        /**
         * Abstract factories
         */
        $this->assertEquals('Obullo\Factory\LazyDefaultFactory', $abstract_factories[0]);
    }
}
