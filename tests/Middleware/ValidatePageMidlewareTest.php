<?php

use Interop\Container\ContainerInterface;
use Obullo\Router\{
    RouteCollection,
    RequestContext,
    Builder,
    Router
};
use Obullo\Router\Types\{
    StrType,
    IntType,
    TranslationType
};
use Zend\Diactoros\{
    Uri,
    Response
};
use Obullo\Http\ServerRequest;
use Zend\Stratigility\MiddlewarePipe;
use Zend\ServiceManager\ServiceManager;

class ValidatePageMiddlewareTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $request = new ServerRequest;
        $request = $request->withUri(new Uri('http://example.com/test?a=b'));

        $container = new ServiceManager(
            [
                'factories' => [
                    Router::class => function (ContainerInterface $container, $requestedName) use ($request) {
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
                                    'handler'=> 'test_.phtml',
                                ],
                        ];
                        $collection = $builder->build($routes);
                        return new Router($collection);
                    },
                ]
            ]
        );
        if ($container->get(Router::class)->matchRequest()) {
            $this->request = $request;
            $this->app = new MiddlewarePipe;
            $this->app->pipe(new Obullo\Middleware\ValidatePageMiddleware($container->get(Router::class)));
            $this->app->pipe(new Obullo\Middleware\PageHandler($container));
        }
    }

    public function testResponse()
    {
        $callback = [$this->app, 'handle'];
        $response = $callback($this->request, new Response);
        
        $this->assertEquals('The page "test_.phtml" does not exists.', $response->getBody());
    }
}
