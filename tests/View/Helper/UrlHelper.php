<?php

use PHPUnit\Framework\TestCase;
use Obullo\Router\Router;
use Obullo\Http\ServerRequest;
use Obullo\View\Helper\Url;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\ServiceManager\ServiceManager;

class UrlHelperTest extends TestCase
{
    public function setUp()
    {
        $this->container = new ServiceManager;
        $this->container->setAlias(Psr\Http\Message\ServerRequestInterface::class, ServerRequest::class);
        $this->container->setFactory(ServerRequest::class, 'Obullo\Factory\RequestFactory');
        $this->container->setAlias('request', ServerRequest::class);
        $this->container->addInitializer(function ($container, $instance) {
            if ($instance instanceof Obullo\Container\ContainerAwareInterface) {
                $instance->setContainer($container);
            }
        });
        $this->container->configure([
            'aliases' => [],
            'factories' => [
                Router::class => App\Factory\RouterFactory::class,
            ],
            'abstract_factories' => [],
        ]);
    }

    public function testUrl()
    {
        $this->url = new Url;
        $this->url->setRouter($this->container->get(Router::class));
        $url = $this->url;
        $this->assertEquals('/test', $url('/test'));
    }
}
