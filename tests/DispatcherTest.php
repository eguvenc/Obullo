<?php

use PHPUnit\Framework\TestCase;
use Laminas\View\View;
use Obullo\Dispatcher;
use Obullo\Http\ServerRequest;
use Laminas\Diactoros\Uri;
use Laminas\ServiceManager\ServiceManager;

class DispatcherTest extends TestCase
{
    public function setUp() : void
    {
        $appConfig = require __DIR__ . '/config/application.config.php';
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

    public function testGetMethod()
    {
        $dispatcher = new Dispatcher;
        $dispatcher->setMethod('onGet');

        $this->assertEquals('onGet', $dispatcher->getMethod());
    }

    public function testGetReflectionClass()
    {
        $dispatcher = new Dispatcher;
        $dispatcher->setContainer($this->container);
        $dispatcher->setReflectionClass(new ReflectionClass($this->container->build('App\Pages\TestModel')));
        $reflectionClass = $dispatcher->getReflectionClass();

        $this->assertEquals('App\Pages\TestModel', $reflectionClass->getName());
    }

    public function testGetReflectionClassWithoutSet()
    {
        $pageModel = $this->container->build('App\Pages\TestModel');

        $dispatcher = new Dispatcher;
        $dispatcher->setContainer($this->container);
        $dispatcher->setPageModel($pageModel);
        $reflectionClass = $dispatcher->getReflectionClass();

        $this->assertEquals('App\Pages\TestModel', $reflectionClass->getName());
    }

    public function testGetPageModel()
    {
        $pageModel = $this->container->build('App\Pages\TestModel');

        $dispatcher = new Dispatcher;
        $dispatcher->setContainer($this->container);
        $dispatcher->setMethod('onGet');
        $dispatcher->setPageModel($pageModel);

        $this->assertInstanceOf('App\Pages\TestModel', $dispatcher->getPageModel());
    }

    public function testDispatch()
    {
        $pageModel = $this->container->build('App\Pages\TestModel');
        $pageModel->setView($this->container->get(View::class));
        $pageModel->setViewPhpRenderer($this->container->get('ViewPhpRenderer'));

        $reflection = new ReflectionClass($pageModel);
        $dispatcher = new Dispatcher;
        $dispatcher->setContainer($this->container);
        $dispatcher->setMethod('onGet');
        $dispatcher->setPageModel($pageModel);
        $response = $dispatcher->dispatch();

        $this->assertEquals('Test', $response->getBody());
    }

    public function testWithQueryMethod()
    {
        $pageModel = $this->container->build('App\Pages\TestModel');
        $pageModel->setView($this->container->get(View::class));
        $pageModel->setViewPhpRenderer($this->container->get('ViewPhpRenderer'));
        
        $reflection = new ReflectionClass($pageModel);
        $dispatcher = new Dispatcher;
        $dispatcher->setContainer($this->container);
        $dispatcher->setMethod('onQueryMethod');
        $dispatcher->setPageModel($pageModel);
        $response = $dispatcher->dispatch();

        $this->assertEquals('Ok', $response->getBody());
    }

    public function testNullResponse()
    {
        $config = $this->container->get('config');
        $config['view_manager']['display_exceptions'] = true;
        $this->container->setService('config', $config);

        $pageModel = $this->container->build('App\Pages\TestModel');
        $pageModel->setView($this->container->get(View::class));
        $pageModel->setViewPhpRenderer($this->container->get('ViewPhpRenderer'));
        
        $reflection = new ReflectionClass($pageModel);
        $dispatcher = new Dispatcher;
        $dispatcher->setContainer($this->container);
        $dispatcher->setMethod('onUndefinedMethod');
        $dispatcher->setPageModel($pageModel);
        $response = $dispatcher->dispatch();

        $this->assertNull($response);
    }

    public function testPageMethodNotExistsExceptionOnPartialView()
    {
        $pageModel = $this->container->build('App\Pages\TestModel');
        $pageModel->setView($this->container->get(View::class));
        $pageModel->setViewPhpRenderer($this->container->get('ViewPhpRenderer'));
        
        $reflection = new ReflectionClass($pageModel);
        $dispatcher = new Dispatcher(['partial_view' => true]);
        $dispatcher->setContainer($this->container);
        $dispatcher->setMethod('onUndefinedMethod');
        $dispatcher->setPageModel($pageModel);

        $message = '';
        try {
            $dispatcher->dispatch();
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        $this->assertEquals('The method onUndefinedMethod does not exists in App\Pages\TestModel.', $message);
    }

    public function testPageArguments()
    {
        $request = new ServerRequest();
        $request = $request->withUri(new Uri('http://example.com/test_args/1001'));
        $this->container->setService('Request', $request);

        $application = $this->container->get('Application');
        $application->bootstrap();
        $response = $application->runWithoutEmit();

        $this->assertEquals('1001', $response->getBody());
    }

    public function testPageOptionalArgument()
    {
        $request = new ServerRequest();
        $request = $request->withUri(new Uri('http://example.com/test_args/1001/1002'));
        $this->container->setService('Request', $request);

        $application = $this->container->get('Application');
        $application->bootstrap();
        $response = $application->runWithoutEmit();

        $this->assertEquals('10011002', $response->getBody());
    }
}
