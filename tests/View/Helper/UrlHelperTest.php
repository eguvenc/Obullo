<?php

use PHPUnit\Framework\TestCase;

use Obullo\View\Helper\Url;
use Obullo\Http\ServerRequest;
use Obullo\Container\ServiceManagerConfig;
use Laminas\Diactoros\Uri;
use Laminas\ServiceManager\ServiceManager;

class UrlHelperTest extends TestCase
{
    public function setUp()
    {
        $appConfig = require __DIR__ . '/../../config/application.config.php';
        $smConfig = isset($appConfig['service_manager']) ? $appConfig['service_manager'] : [];
        $smConfig = new Obullo\Container\ServiceManagerConfig($smConfig);

        // setup service manager
        //
        $this->container = new ServiceManager;
        $smConfig->configureServiceManager($this->container);
        $this->container->setService('appConfig', $appConfig);

        // load app modules
        //
        $this->container->get('ModuleManager')->loadModules();
    }

    public function testUrl()
    {
        $url = $this->url = new Url;
        $this->url->setRouter($this->container->get('Router'));
        $this->assertEquals('/test', $url('/test'));
    }
}
