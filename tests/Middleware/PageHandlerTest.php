<?php

use Obullo\Router\Router;
use Obullo\Middleware\PageHandler;
use Laminas\Diactoros\Uri;
use Laminas\Diactoros\Response;
use Obullo\Http\ServerRequest;
use Laminas\View\View;
use Laminas\View\HelperPluginManager;
use Laminas\Stratigility\MiddlewarePipe;
use Laminas\ServiceManager\ServiceManager;
use Obullo\Factory\LazyMiddlewareFactory;
use Obullo\Factory\LazyPageFactory;
use Laminas\View\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class PageHandlerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->container = new ServiceManager;
        $this->container->setAlias(Psr\Http\Message\ServerRequestInterface::class, ServerRequest::class);
        $this->container->setFactory(ServerRequest::class, 'Obullo\Factory\RequestFactory');
        $this->container->addInitializer(function ($container, $instance) {
            if ($instance instanceof Obullo\Container\ContainerAwareInterface) {
                $instance->setContainer($container);
            }
        });
        $this->container->configure([
                'aliases' => [
                    'plugin' => HelperPluginManager::class,
                ],
                'factories' => [
                    Router::class => App\Factory\RouterFactory::class,
                    RendererInterface::class => App\Factory\RendererFactory::class,
                    View::class => App\Factory\ViewFactory::class,
                    HelperPluginManager::class => App\Factory\PluginManagerFactory::class,
                ],
                'abstract_factories' => [
                    LazyMiddlewareFactory::class,
                    LazyPageFactory::class,
                ],
        ]);
    }

    public function testDependenciesOnGet()
    {
        $request = new ServerRequest;
        $request = $request->withUri(new Uri('http://example.com/test'));
        $this->container->setService('request', $request);

        $router = $this->container->get(Router::class);
        $router->matchRequest();

        $app = new MiddlewarePipe;
        $middleware = $this->container->get(PageHandler::class);
        $app->pipe($middleware);

        $callback = [$app, 'handle'];
        $response = $callback($request, new Response);

        $this->assertEquals('Tests\Pages\TestModel', $response->getBody());
    }

    public function testLaminasCurrencyFormatHelper()
    {
        $request = new ServerRequest;
        $request = $request->withUri(new Uri('http://example.com/plugin'));
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
