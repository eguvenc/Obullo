<?php

use PHPUnit\Framework\TestCase;
use Obullo\View\Helper\Model;
use Obullo\Http\ServerRequest;
use Obullo\Container\ServiceManagerConfig;

use Laminas\Diactoros\Uri;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Model\ViewModel;

class ModelHelperTest extends TestCase
{
    public function setUp() : void
    {
        $appConfig = require __DIR__ . '/../../config/application.config.php';
        $smConfig = isset($appConfig['service_manager']) ? $appConfig['service_manager'] : [];
        $smConfig = new Obullo\Container\ServiceManagerConfig($smConfig);

        // setup service manager
        //
        $this->container = new ServiceManager;
        $smConfig->configureServiceManager($this->container);
        $this->container->setService('appConfig', $appConfig);
        $this->container->addAbstractFactory(new Obullo\Factory\LazyMiddlewareFactory);

        // load app modules
        //
        $this->container->get('ModuleManager')->loadModules();
    }

    public function testModel()
    {
        $request = new ServerRequest();
        $request = $request->withUri(new Uri('http://example.com/test_partial_view'));
        $this->container->setService('Request', $request);

        $application = $this->container->get('Application');
        $application->bootstrap();
        $response = $application->runWithoutEmit();
        
        $this->assertEquals('Header', $response->getBody());
    }
}
