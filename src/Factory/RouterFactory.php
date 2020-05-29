<?php

namespace Obullo\Factory;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\Config\Config;
use Obullo\Http\ServerRequest;
use Obullo\Router\Pattern;
use Obullo\Router\RequestContext;
use Obullo\Router\Builder;
use Obullo\Router\Router;
use Obullo\Router\RouteCollectionInterface;

class RouterFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string             $requestedName
     * @param  null|array         $options
     * @return object
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get(Config::class);

        $pattern = new Pattern($config['router']['types']);
        $context = new RequestContext;
        $context->fromRequest($container->get(ServerRequest::class));

        $collection = $container->build(RouteCollectionInterface::class, ['pattern' => $pattern, 'config' => $config]);
        $collection->setContext($context);

        $builder = new Builder($collection);
        $collection = $builder->build($config['router']['routes']);
        
        return new Router($collection);
    }
}
