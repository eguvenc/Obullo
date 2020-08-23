<?php

use PHPUnit\Framework\TestCase;
use Obullo\PageEvent;
use Obullo\Http\ServerRequest;
use Laminas\Diactoros\Uri;
use Laminas\Diactoros\Response;
use Laminas\View\Model\ViewModel;
use Laminas\Psr7Bridge\Psr7ServerRequest;
use Laminas\ServiceManager\ServiceManager;

class PageEventTest extends TestCase
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

    public function testGetApplication()
    {
        $event = new PageEvent;
        $event->setApplication($this->container->get('Application'));

        $this->assertInstanceOf('Obullo\Application', $event->getApplication());
    }

    public function testGetRouter()
    {
        $event = new PageEvent;
        $event->setRouter($this->container->get('Router'));

        $this->assertInstanceOf('Laminas\Router\RouteStackInterface', $event->getRouter());
    }

    public function testGetRouteMatch()
    {
        $request = new ServerRequest(
            $serverParams = [],
            $uploadedFiles = [],
            new Uri('http://example.com/test'),
            'GET',
            $body = 'php://input',
            $headers = [],
            $cookies = [],
            $queryParams = [],
            $parsedBody = [],
            $protocol = '1.1'
        );
        $router = $this->container->get('Router');
        $routeMatch = $router->match(Psr7ServerRequest::toLaminas($request, true));
        $event = new PageEvent;
        $event->setRouteMatch($routeMatch);

        $this->assertInstanceOf('Laminas\Router\RouteMatch', $event->getRouteMatch());
    }

    public function testGetController()
    {
        $event = new PageEvent;
        $event->setController('App\Pages\TestModel');

        $this->assertEquals('App\Pages\TestModel', $event->getController());
    }

    public function testGetResolvedModuleName()
    {
        $event = new PageEvent;
        $event->setController('Test\Pages\TestModel');
        $event->setResolvedModuleName();

        $this->assertEquals('Test', $event->getResolvedModuleName());
    }

    public function testGetRequest()
    {
        $event = new PageEvent;
        $event->setRequest($this->container->get('Request'));

        $this->assertInstanceOf('Psr\Http\Message\ServerRequestInterface', $event->getRequest());
    }

    public function testGetResponse()
    {
        $event = new PageEvent;
        $event->setResponse(new Response);

        $this->assertInstanceOf('Psr\Http\Message\ResponseInterface', $event->getResponse());
    }

    public function testGetPageModel()
    {
        $event = new PageEvent;
        $event->setPageModel('Test\Handler', new StdClass);

        $this->assertInstanceOf('stdClass', $event->getPageModel('Test\Handler'));
    }
}
