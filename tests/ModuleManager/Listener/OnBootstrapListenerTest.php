<?php

use Laminas\Diactoros\Uri;
use Obullo\Http\ServerRequest;
use Laminas\ServiceManager\ServiceManager;

class OnBootstrapListenerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $appConfig = require dirname(__DIR__) . '/../config/application.config.php';
        $appConfig['modules'][] = 'App';
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

    public function testInit()
    {
        $request = new ServerRequest();
        $request = $request->withUri(new Uri('http://example.com/'));
        $this->container->setService('Request', $request);

        $application = $this->container->get('Application');  
        $events = $application->getEventManager();
        $application->bootstrap();
        $application->runWithoutEmit();

        $result = $events->trigger('test.init', null, $params = array());
        $this->assertEquals($result->last(), 'test.init');
    }

    public function testOnBootstrap()
    {
        $request = new ServerRequest();
        $request = $request->withUri(new Uri('http://example.com/'));
        $this->container->setService('Request', $request);

        $application = $this->container->get('Application');
        $application->bootstrap();
        $application->runWithoutEmit();

        $events = $application->getEventManager();
        $result = $events->trigger('test.onBootstrap', null, $params = array());

        $this->assertEquals($result->last(), 'test.onBootstrap');
    }
}
