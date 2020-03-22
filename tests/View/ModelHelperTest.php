<?php

use PHPUnit\Framework\TestCase;
use Obullo\View\Helper\Model;
use Obullo\Router\Router;
use Obullo\Http\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\ServiceManager\ServiceManager;
use Obullo\Factory\LazyPageFactory;
use Laminas\View\View;
use Laminas\View\HelperPluginManager;
use Laminas\View\Renderer\RendererInterface;

class ModelHelperTest extends TestCase
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
                RendererInterface::class => App\Factory\RendererFactory::class,
                View::class => App\Factory\ViewFactory::class,
                HelperPluginManager::class => App\Factory\PluginManagerFactory::class,
            ],
            'abstract_factories' => [
                LazyPageFactory::class,
            ],
        ]);
        $this->model = new Model;
        $this->model->setContainer($this->container);
    }

    public function testModel()
    {
        $model = $this->model;
        $instance = $model('Tests\Pages\TestModel');
        $this->assertInstanceOf('Tests\Pages\TestModel', $instance);
    }
}
