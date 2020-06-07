<?php

use Obullo\MiddlewareParser;
use Obullo\Router\Router;
use Obullo\Container\ServiceManagerConfig;
use Laminas\ServiceManager\ServiceManager;

class MiddlewareParserTest extends PHPUnit_Framework_TestCase
{
    public function setup()
    {
        $appConfig = require __DIR__.'/config/application.config.php';
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

        $this->collection = $this->container->get('Router')->getCollection();
    }

    public function testParseMiddlewareOnPost()
    {
        $context = $this->collection->getContext();
        $context->setMethod('POST');
        $this->collection->setContext($context);

        $router = new Router($this->collection);
        $route = $router->match('/test');

        $middlewares = MiddlewareParser::parse($router->getMiddlewares(), $context->getMethod());

        $this->assertEquals($context->getMethod(), 'POST');
        $this->assertEquals($middlewares, ['App\Middleware\AuthMiddleware']);
    }

    public function testParseMiddlewareOnGet()
    {
        $context = $this->collection->getContext();
        $context->setMethod('GET');
        $this->collection->setContext($context);

        $router = new Router($this->collection);
        $route = $router->match('/test');

        $middlewares = MiddlewareParser::parse($router->getMiddlewares(), $context->getMethod());

        $this->assertEquals($context->getMethod(), 'GET');
        $this->assertEquals($middlewares, []);
    }

    public function testParseMiddlewareOnGetOrPost()
    {
        $context = $this->collection->getContext();
        $context->setMethod('GET');
        $this->collection->setContext($context);

        $router = new Router($this->collection);
        $route = $router->match('/test_multi');
        
        $middlewares = MiddlewareParser::parse($router->getMiddlewares(), $context->getMethod());

        $this->assertEquals($context->getMethod(), 'GET');
        $this->assertEquals($middlewares, ['App\Middleware\AuthMiddleware']);

        $context = $this->collection->getContext();
        $context->setMethod('POST');
        $this->collection->setContext($context);

        $router = new Router($this->collection);
        $route = $router->match('/test_multi');
        $middlewares = MiddlewareParser::parse($router->getMiddlewares(), $context->getMethod());

        $this->assertEquals($context->getMethod(), 'POST');
        $this->assertEquals($middlewares, ['App\Middleware\AuthMiddleware']);
    }
}
