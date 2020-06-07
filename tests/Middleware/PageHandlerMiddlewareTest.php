<?php

use Laminas\Diactoros\Uri;
use Obullo\Http\ServerRequest;
use Laminas\ServiceManager\ServiceManager;

class PageHandlerMiddlewareTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $appConfig = require __DIR__ . '/../config/application.config.php';
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
        $this->container->setAllowOverride(true);
    }

    public function testDependencies()
    {
        $request = new ServerRequest();
        $request = $request->withUri(new Uri('http://example.com/test'));
        $this->container->setService('Request', $request);

        $application = $this->container->get('Application');
        $application->bootstrap();
        $response = $application->runWithoutEmit();
        
        $this->assertEquals('Test', $response->getBody());
    }

    public function testPluginHelper()
    {
        $request = new ServerRequest();
        $request = $request->withUri(new Uri('http://example.com/plugin_test'));
        $this->container->setService('Request', $request);

        $application = $this->container->get('Application');
        $application->bootstrap();
        $response = $application->runWithoutEmit();

        $this->assertEquals('$1,234.56', $response->getBody());
    }
}
