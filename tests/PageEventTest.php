<?php

use PHPUnit\Framework\TestCase;
use Obullo\PageEvent;
use Laminas\Diactoros\Response;
use Laminas\View\Model\ViewModel;
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

        $this->assertInstanceOf('Obullo\Router\Router', $event->getRouter());
    }

    public function testGetMatchedRoute()
    {
        $router = $this->container->get('Router');
        $route  = $router->match('/test');

        $event = new PageEvent;
        $event->setMatchedRoute($route);

        $this->assertInstanceOf('Obullo\Router\Route', $event->getMatchedRoute());
    }

    public function testGetHandler()
    {
        $event = new PageEvent;
        $event->setHandler('App\Pages\TestModel');

        $this->assertEquals('App\Pages\TestModel', $event->getHandler());
    }

    public function testGetResolvedModuleName()
    {
        $event = new PageEvent;
        $event->setHandler('Test\Pages\TestModel');
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

    public function testGetViewModel()
    {
        $event = new PageEvent;
        $event->setViewModel(new ViewModel);

        $this->assertInstanceOf('Laminas\View\Model\ModelInterface', $event->getViewModel());
    }

    public function testGetViewModelWithoutSet()
    {
        $event = new PageEvent;

        $this->assertInstanceOf('Laminas\View\Model\ModelInterface', $event->getViewModel());
    }
}
