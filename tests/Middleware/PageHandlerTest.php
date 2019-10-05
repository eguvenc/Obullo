<?php

use Interop\Container\ContainerInterface;
use Obullo\Router\RouteCollection;
use Obullo\Router\RequestContext;
use Obullo\Router\Builder;
use Obullo\Router\Pattern;
use Obullo\Router\Router;
use Obullo\Router\Types\StrType;
use Obullo\Router\Types\IntType;
use Obullo\Router\Types\TranslationType;
use Zend\Diactoros\Uri;
use Zend\Diactoros\Response;
use Obullo\Http\ServerRequest;
use Zend\Stratigility\MiddlewarePipe;
use Zend\ServiceManager\ServiceManager;
use Obullo\View\PluginManager;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\ServiceManager\Factory\InvokableFactory;
use Zend\I18n\View\Helper as ZendPlugin;

class PageHandlerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->container = new ServiceManager(
            [
                'aliases' => [
                    'plugin' => PluginManager::class,
                ],
                'factories' => [
                    Router::class => function (ContainerInterface $container, $requestedName) {
                        $pattern = new Pattern([
                            new IntType('<int:id>'),
                            new IntType('<int:page>'),
                            new StrType('<str:name>'),
                            new TranslationType('<locale:locale>'),
                        ]);
                        $context = new RequestContext;
                        $context->fromRequest($container->get('request'));

                        $collection = new RouteCollection($pattern);
                        $collection->setContext($context);

                        $builder = new Builder($collection);
                        $routes  = [
                            '/test' => [
                                'handler'=> 'test.phtml',
                            ],
                            'plugin/test/' => [
                                'handler'=> 'plugin_test.phtml',
                            ],
                        ];
                        $collection = $builder->build($routes);
                        return new Router($collection);
                    },
                    PluginManager::class => function (ContainerInterface $container, $requestedName) {
                        $config = [
                            'aliases' => [
                                'currencyFormat' => ZendPlugin\CurrencyFormat::class,
                            ],
                            'factories' => [
                                ZendPlugin\CurrencyFormat::class => InvokableFactory::class,
                            ],
                        ];
                        $pluginManager = new PluginManager($container);
                        $pluginManager->configure($config);
                        return $pluginManager;
                    },
                ]
            ]
        );
    }

    public function testResponse()
    {
        $request = new ServerRequest;
        $request = $request->withUri(new Uri('http://example.com/test?a=b'));
        $this->container->setService('request', $request);

        $router = $this->container->get(Router::class);
        $router->matchRequest();

        $app = new MiddlewarePipe;
        $middleware = new Obullo\Middleware\PageHandler($this->container);
        $app->pipe($middleware);

        $callback = [$app, 'handle'];
        $response = $callback($request, new Response);
        
        $this->assertEquals('test', $response->getBody());
    }

    public function testPlugin()
    {
        $request = new ServerRequest;
        $request = $request->withUri(new Uri('http://example.com/plugin/test'));
        $this->container->setService('request', $request);

        $router = $this->container->get(Router::class);
        $router->matchRequest();

        $app = new MiddlewarePipe;
        $middleware = new Obullo\Middleware\PageHandler($this->container);
        $app->pipe($middleware);

        $callback = [$app, 'handle'];
        $response = $callback($request, new Response);
        
        $this->assertEquals('$1,234.56', $response->getBody());
    }
}
