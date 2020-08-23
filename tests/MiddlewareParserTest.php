<?php

use PHPUnit\Framework\TestCase;
use Obullo\MiddlewareParser;
use Laminas\Router\RouteMatch;
use Laminas\Psr7Bridge\Psr7ServerRequest;
use Obullo\Http\ServerRequest;
use Laminas\Diactoros\Uri;
use Obullo\Container\ServiceManagerConfig;
use Laminas\ServiceManager\ServiceManager;

class MiddlewareParserTest extends TestCase
{
    public function setup() : void
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
    }

    public function testParseMiddlewareOnPost()
    {
        $router  = $this->container->get('Router');
        $request = new ServerRequest(
            $serverParams = [],
            $uploadedFiles = [],
            new Uri('http://example.com/test'),
            'POST',
            $body = 'php://input',
            $headers = [],
            $cookies = [],
            $queryParams = [],
            $parsedBody = [],
            $protocol = '1.1'
        );
        $routeMatch = $router->match(Psr7ServerRequest::toLaminas($request, true));
        $params = $routeMatch->getParams();

        $middlewares = MiddlewareParser::parse((array)$params['middleware'], $request->getMethod());
        $this->assertEquals($middlewares[0], 'App\Middleware\AuthMiddleware');
    }

    public function testParseMiddlewareOnGet()
    {
        $middlewares = MiddlewareParser::parse(['App\Middleware\AuthMiddleware@onGet'], 'GET');
        $this->assertEquals($middlewares[0], 'App\Middleware\AuthMiddleware');
    }

    public function testParseMiddlewareOnGetOrPost()
    {  
        // GET
        $router  = $this->container->get('Router');
        $request = new ServerRequest(
            $serverParams = [],
            $uploadedFiles = [],
            new Uri('http://example.com/test_multi'),
            'POST',
            $body = 'php://input',
            $headers = [],
            $cookies = [],
            $queryParams = [],
            $parsedBody = [],
            $protocol = '1.1'
        );
        $routeMatch = $router->match(Psr7ServerRequest::toLaminas($request, true));
        $params = $routeMatch->getParams();
        $middlewares = MiddlewareParser::parse((array)$params['middleware'], $request->getMethod());
        $this->assertEquals($middlewares[0], 'App\Middleware\AuthMiddleware');

        // POST
        $request = new ServerRequest(
            $serverParams = [],
            $uploadedFiles = [],
            new Uri('http://example.com/test_multi'),
            'GET',
            $body = 'php://input',
            $headers = [],
            $cookies = [],
            $queryParams = [],
            $parsedBody = [],
            $protocol = '1.1'
        );
        $routeMatch = $router->match(Psr7ServerRequest::toLaminas($request, true));
        $params = $routeMatch->getParams();
        $middlewares = MiddlewareParser::parse((array)$params['middleware'], $request->getMethod());

        $this->assertEquals($middlewares[0], 'App\Middleware\AuthMiddleware');
    }
}
