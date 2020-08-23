<?php

use PHPUnit\Framework\TestCase;
use Laminas\View\View;
use Obullo\Dispatcher;
use Laminas\Router\RouteMatch;
use Laminas\Psr7Bridge\Psr7ServerRequest;
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
        $this->container->addAbstractFactory(new Obullo\Factory\LazyMiddlewareFactory);

        // load app modules
        //
        $this->container->get('ModuleManager')->loadModules();
        $this->container->setAllowOverride(true);
    }

    public function testGetRequest()
    {
        $dispatcher = new Dispatcher;
        $dispatcher->setRequest($this->container->get('Request'));

        $this->assertInstanceOf('Psr\Http\Message\ServerRequestInterface', $dispatcher->getRequest());
    }

    public function testGetPageMethod()
    {
        $dispatcher = new Dispatcher;
        $dispatcher->setRequest($this->container->get('Request'));
        $dispatcher->setPageMethod('onPost');

        $this->assertEquals('onPost', $dispatcher->getPageMethod());
    }

    public function testGetReflectionClass()
    {
        $dispatcher = new Dispatcher;
        $dispatcher->setRequest($this->container->get('Request'));
        $dispatcher->setReflectionClass(new ReflectionClass(new App\Pages\TestModel));
        $reflectionClass = $dispatcher->getReflectionClass();

        $this->assertEquals('App\Pages\TestModel', $reflectionClass->getName());
    }

    public function testGetReflectionClassWithoutSet()
    {
        $pageModel = new App\Pages\TestModel;
        $pageModel->init();

        $dispatcher = new Dispatcher;
        $dispatcher->setRequest($this->container->get('Request'));
        $dispatcher->setPageModel($pageModel);
        $reflectionClass = $dispatcher->getReflectionClass();

        $this->assertEquals('App\Pages\TestModel', $reflectionClass->getName());
    }

    public function testGetPageModel()
    {
        $pageModel = new App\Pages\TestModel;
        $pageModel->init();

        $dispatcher = new Dispatcher;
        $dispatcher->setRequest($this->container->get('Request'));
        $dispatcher->setPageMethod('onGet');
        $dispatcher->setPageModel($pageModel);

        $this->assertInstanceOf('App\Pages\TestModel', $dispatcher->getPageModel());
    }

    public function testDispatch()
    {
        $pageModel = new App\Pages\TestModel;
        $pageModel->setView($this->container->get(View::class));
        $pageModel->setViewPhpRenderer($this->container->get('ViewPhpRenderer'));
        $pageModel->init();

        $router  = $this->container->get('Router');
        $request = $this->container->get('Request');
        $routeMatch = $router->match(Psr7ServerRequest::toLaminas($request, true));

        $reflection = new ReflectionClass($pageModel);
        $dispatcher = new Dispatcher;
        $dispatcher->setRequest($request);
        $dispatcher->setRouteMatch($routeMatch);
        $dispatcher->setPageMethod('onGet');
        $dispatcher->setPageModel($pageModel);
        $response = $dispatcher->dispatch();

        $this->assertEquals('Test', $response->getBody());
    }

    public function testWithQueryMethod()
    {
        $pageModel = new App\Pages\TestModel;
        $pageModel->setView($this->container->get(View::class));
        $pageModel->setViewPhpRenderer($this->container->get('ViewPhpRenderer'));
        $pageModel->init();
        $request = new ServerRequest(
            $serverParams = [],
            $uploadedFiles = [],
            new Uri('http://example.com'),
            'GET',
            $body = 'php://input',
            $headers = [],
            $cookies = [],
            $queryParams = ['test' => 'Ok'],
            $parsedBody = [],
            $protocol = '1.1'
        );
        $reflection = new ReflectionClass($pageModel);

        $router = $this->container->get('Router');
        $routeMatch = $router->match(Psr7ServerRequest::toLaminas($request, true));

        $dispatcher = new Dispatcher;
        $dispatcher->setRequest($request);
        $dispatcher->setRouteMatch($routeMatch);
        $dispatcher->setPageMethod('onQueryMethod');
        $dispatcher->setPageModel($pageModel);
        $response = $dispatcher->dispatch();

        $this->assertEquals('Ok', $response->getBody());
    }

    public function testNullResponse()
    {
        $config = $this->container->get('config');
        $config['view_manager']['display_exceptions'] = true;
        $this->container->setService('config', $config);

        $pageModel = new App\Pages\TestModel;
        $pageModel->setView($this->container->get(View::class));
        $pageModel->setViewPhpRenderer($this->container->get('ViewPhpRenderer'));
        $pageModel->init();
        
        $reflection = new ReflectionClass($pageModel);
        $router = $this->container->get('Router');
        $request = $this->container->get('Request');
        $routeMatch = $router->match(Psr7ServerRequest::toLaminas($request, true));

        $dispatcher = new Dispatcher;
        $dispatcher->setRequest($request);
        $dispatcher->setRouteMatch($routeMatch);
        $dispatcher->setPageMethod('onUndefinedMethod');
        $dispatcher->setPageModel($pageModel);
        $response = $dispatcher->dispatch();

        $this->assertNull($response);
    }

    public function testPageMethodNotExistsExceptionOnPartialView()
    {
        $pageModel = new App\Pages\TestModel;
        $pageModel->setView($this->container->get(View::class));
        $pageModel->setViewPhpRenderer($this->container->get('ViewPhpRenderer'));
        $pageModel->init();

        $reflection = new ReflectionClass($pageModel);
        $router = $this->container->get('Router');
        $request = $this->container->get('Request');
        $routeMatch = $router->match(Psr7ServerRequest::toLaminas($request, true));

        $dispatcher = new Dispatcher(['partial_view' => true]);
        $dispatcher->setRequest($request);
        $dispatcher->setRouteMatch($routeMatch);
        $dispatcher->setPageMethod('onUndefinedMethod');
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

    public function createRequest($method, $queryParams = [], $parsedBody = [])
    {
        $pageModel = new App\Pages\TestHttpModel;
        $pageModel->setView($this->container->get(View::class));
        $pageModel->setViewPhpRenderer($this->container->get('ViewPhpRenderer'));
        // $pageModel->init();
        $request = new ServerRequest(
            $serverParams = [],
            $uploadedFiles = [],
            new Uri('http://example.com/'),
            strtoupper($method),
            $body = 'php://input',
            $headers = [],
            $cookies = [],
            $queryParams,
            $parsedBody,
            $protocol = '1.1'
        );
        $reflection = new ReflectionClass($pageModel);
        $router = $this->container->get('Router');
        $routeMatch = $router->match(Psr7ServerRequest::toLaminas($request, true));

        $dispatcher = new Dispatcher;
        $dispatcher->setRequest($request);
        $dispatcher->setRouteMatch($routeMatch);
        $dispatcher->setPageMethod('on'.ucfirst(strtolower($method)));
        $dispatcher->setPageModel($pageModel);
        return $dispatcher->dispatch();
    }

    public function testHttpMethodOnPostData()
    {
        $response = $this->createRequest('post', $get = [], $post = ['test' => 'onPost']);
        $this->assertEquals('onPost', $response->getBody());
    }

    public function testHttpMethodOnPutData()
    {
        $response = $this->createRequest('put', $get = [], $post = ['test' => 'onPut']);
        $this->assertEquals('onPut', $response->getBody());
    }

    public function testHttpMethodOnPatchData()
    {
        $response = $this->createRequest('patch', $get = [], $post = ['test' => 'onPatch']);
        $this->assertEquals('onPatch', $response->getBody());
    }

    public function testHttpMethodOnOptionsData()
    {
        $response = $this->createRequest('options', $get = [], $post = ['test' => 'onOptions']);
        $this->assertEquals('onOptions', $response->getBody());
    }

    public function testHttpMethodOnHeadData()
    {
        $response = $this->createRequest('head', $get = ['test' => 'onHead'], $post = []);
        $this->assertEquals('onHead', $response->getBody());
    }

    public function testHttpMethodOnGetData()
    {
        $response = $this->createRequest('get', $get = ['test' => 'onGet'], $post = []);
        $this->assertEquals('onGet', $response->getBody());
    }

    public function testHttpMethodOnTraceData()
    {
        $response = $this->createRequest('trace', $get = ['test' => 'onTrace'], $post = []);
        $this->assertEquals('onTrace', $response->getBody());
    }

    public function testHttpMethodOnConnectData()
    {
        $response = $this->createRequest('connect', $get = ['test' => 'onConnect'], $post = []);
        $this->assertEquals('onConnect', $response->getBody());
    }

    public function testHttpMethodOnDeleteData()
    {
        $response = $this->createRequest('delete', $get = ['test' => 'onDelete'], $post = []);
        $this->assertEquals('onDelete', $response->getBody());
    }

    public function testHttpMethodOnPropfindData()
    {
        $response = $this->createRequest('propfind', $get = ['test' => 'onPropfind'], $post = []);
        $this->assertEquals('onPropfind', $response->getBody());
    }
}