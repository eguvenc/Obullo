<?php

use PHPUnit\Framework\TestCase;
use Laminas\ServiceManager\ServiceManager;

class ViewPhpRendererFactoryTest extends TestCase
{
    public function setUp() : void
    {
        $appConfig = require __DIR__ . '/../config/application.config.php';
        $smConfig = isset($appConfig['service_manager']) ? $appConfig['service_manager'] : [];
        $smConfig = new Obullo\Container\ServiceManagerConfig($smConfig);

        // setup service manager
        //
        $this->container = new ServiceManager;
        $smConfig->configureServiceManager($this->container);
        $this->container->setService('appConfig', $appConfig);
        $this->container->setFactory('ViewPhpRenderer', 'Obullo\Factory\ViewPhpRendererFactory');
        $this->container->setFactory('ViewHelperManager', 'Obullo\Factory\ViewHelperManagerFactory');
    }

    public function testFactory()
    {
        $instance = $this->container->get('ViewPhpRenderer');
        
        $this->assertInstanceOf('Laminas\View\Renderer\PhpRenderer', $instance);
    }
}
