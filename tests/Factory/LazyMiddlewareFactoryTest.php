<?php

use PHPUnit\Framework\TestCase;

use Psr\Http\Message\ServerRequestInterface;
use Obullo\Container\ServiceManagerConfig;
use Laminas\ServiceManager\ServiceManager;

class LazyMiddlewareFactoryTest extends TestCase
{
    public function setUp() : void
    {
        $appConfig = require __DIR__.'/../config/application.config.php';

        $smConfig = isset($appConfig['service_manager']) ? $appConfig['service_manager'] : [];
        $smConfig = new Obullo\Container\ServiceManagerConfig($smConfig);
        $this->container = new ServiceManager;
        $smConfig->configureServiceManager($this->container);
        $this->container->setService('appConfig', $appConfig);
        $this->container->addAbstractFactory(new Obullo\Factory\LazyMiddlewareFactory);

        // load modules
        // 
        $this->container->get('ModuleManager')->loadModules();
    }

    public function testFactory()
    {
        $instance = $this->container->build('Obullo\Middleware\SetLocaleMiddleware');

        $this->assertInstanceOf('Obullo\Middleware\SetLocaleMiddleware', $instance);
    }
}
