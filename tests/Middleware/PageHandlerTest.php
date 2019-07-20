<?php

use Interop\Container\ContainerInterface;
use Obullo\Router\RouteCollection;
use Obullo\Router\RequestContext;
use Obullo\Router\Builder;
use Obullo\Router\Router;
use Obullo\Router\Types\StrType;
use Obullo\Router\Types\IntType;
use Obullo\Router\Types\TranslationType;
use Zend\Diactoros\Uri;
use Zend\Diactoros\Server;
use Zend\Diactoros\Response;
use Obullo\Http\ServerRequest;
use Zend\Stratigility\MiddlewarePipe;
use Zend\ServiceManager\ServiceManager;

class PageHandlerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $request = new ServerRequest;
        $request = $request->withUri(new Uri('http://example.com/test?a=b'));

        $container = new ServiceManager(
            [
                'factories' => [
                    'router' => function (ContainerInterface $container, $requestedName) use ($request) {
                        $patterns = [
                            new IntType('<int:id>'),
                            new IntType('<int:page>'),
                            new StrType('<str:name>'),
                            new TranslationType('<locale:locale>'),
                        ];
                        $context = new RequestContext;
                        $context->fromRequest($request);

                        $collection = new RouteCollection(['patterns' => $patterns]);
                        $collection->setContext($context);

                        $builder = new Builder($collection);
                        $routes  = [
                        'test' => [
                                'path'   => '/test',
                                'handler'=> 'test.phtml',
                            ],
                        ];
                        $collection = $builder->build($routes);
                        return new Router($collection);
                    },
                ]
            ]
        );
        $this->app = new MiddlewarePipe;
        $this->request = $request;
        $middleware = new Obullo\Middleware\PageHandler($container);
        $this->app->pipe($middleware);
    }

    public function testResponse()
    {
        $callback = [$this->app, 'handle'];
        $response = $callback($this->request, new Response);
        
        $this->assertEquals('test', $response->getBody());
    }
}
