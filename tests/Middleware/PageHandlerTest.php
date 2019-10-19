<?php

use Interop\Container\ContainerInterface;
use Obullo\Router\RouteCollection;
use Obullo\Router\RequestContext;
use Obullo\Router\Builder;
use Obullo\Router\Pattern;
use Obullo\Router\Router;
use Obullo\Middleware\PageHandler;
use Obullo\Router\Types\StrType;
use Obullo\Router\Types\IntType;
use Obullo\Router\Types\TranslationType;
use Zend\Diactoros\Uri;
use Zend\Diactoros\Response;
use Obullo\Http\ServerRequest;
use Obullo\View\PluginManager;
use Zend\Stratigility\MiddlewarePipe;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\ServiceManager\Factory\InvokableFactory;
use Zend\I18n\View\Helper as ZendPlugin;

class PageHandlerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->container = new ServiceManager;
        $this->container->addInitializer(function ($container, $instance) {
            if ($instance instanceof Obullo\Container\ContainerAwareInterface) {
                $instance->setContainer($container);
            }
        });
        $this->container->configure([
                'aliases' => [
                    'plugin' => PluginManager::class,
                ],
                'factories' => [
                    Router::class => App\Factory\RouterFactory::class,
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
                ],
                'abstract_factories' => [
                    \App\Factory\LazyMiddlewareFactory::class,
                ],
        ]);
    }

    public function testResponse()
    {
        $request = new ServerRequest;
        $request = $request->withUri(new Uri('http://example.com/test?a=b'));
        $this->container->setService('request', $request);

        $router = $this->container->get(Router::class);
        $router->matchRequest();

        $app = new MiddlewarePipe;
        $middleware = $this->container->get(PageHandler::class);
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
        $middleware = $this->container->get(PageHandler::class);
        $app->pipe($middleware);

        $callback = [$app, 'handle'];
        $response = $callback($request, new Response);
        
        $this->assertEquals('$1,234.56', $response->getBody());
    }
}
