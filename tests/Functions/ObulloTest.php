<?php

use function Obullo\Functions\parseMiddlewares;
use Obullo\Router\Router;
use Obullo\Router\Pattern;
use Obullo\Router\Builder;
use Obullo\Router\RequestContext;
use Obullo\Router\RouteCollection;
use Obullo\Router\Types\StrType;
use Obullo\Router\Types\IntType;
use Obullo\Router\Types\BoolType;
use Obullo\Router\Types\SlugType;
use Obullo\Router\Types\AnyType;
use Obullo\Router\Types\FourDigitYearType;
use Obullo\Router\Types\TwoDigitMonthType;
use Obullo\Router\Types\TwoDigitDayType;
use Obullo\Router\Types\TranslationType;
use Symfony\Component\Yaml\Yaml;

class BuilderTest extends PHPUnit_Framework_TestCase
{
    public function setup()
    {
        $request = Zend\Diactoros\ServerRequestFactory::fromGlobals();
        $file = ROOT.'/config/routes.yaml';
        $this->routes = Yaml::parseFile($file);

        $pattern = new Pattern([
                new IntType('<int:id>'),  // \d+
                new StrType('<str:name>'),     // \w+
                new StrType('<str:word>'),     // \w+
                new AnyType('<any:any>'),
                new BoolType('<bool:status>'),
                new IntType('<int:page>'),
                new SlugType('<slug:slug>'),
                new TranslationType('<locale:locale>'),
        ]);
        $context = new RequestContext;
        $context->fromRequest($request);

        $collection = new RouteCollection($pattern);
        $collection->setContext($context);

        $this->builder = new Builder($collection);
        $this->collection = $this->builder->build($this->routes);
    }

    public function testParseMiddlewareOnPost()
    {
        $context = $this->collection->getContext();
        $context->setMethod('POST');
        $this->collection->setContext($context);

        $router = new Router($this->collection);
        $route = $router->match('/test');
        $middlewares = parseMiddlewares($router);

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
        $middlewares = parseMiddlewares($router);

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
        $middlewares = parseMiddlewares($router);

        $this->assertEquals($context->getMethod(), 'GET');
        $this->assertEquals($middlewares, ['App\Middleware\AuthMiddleware']);

        $context = $this->collection->getContext();
        $context->setMethod('POST');
        $this->collection->setContext($context);

        $router = new Router($this->collection);
        $route = $router->match('/test_multi');
        $middlewares = parseMiddlewares($router);

        $this->assertEquals($context->getMethod(), 'POST');
        $this->assertEquals($middlewares, ['App\Middleware\AuthMiddleware']);
    }
}