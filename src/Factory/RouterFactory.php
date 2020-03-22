<?php

namespace Obullo\Factory;

use Psr\Container\ContainerInterface;
use Laminas\Config\Config;
use Obullo\Http\ServerRequest;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Obullo\Router\{
    Pattern,
    RouteCollection,
    RequestContext,
    Builder,
    Router
};
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
        $config = $container->get(Config::class)->toArray();

        $pattern = new Pattern($config['route_types']);
        $context = new RequestContext;
        $context->fromRequest($container->get(ServerRequest::class));
         
        $collection = new RouteCollection($pattern);
        $collection->setContext($context);

        $builder = new Builder($collection);
        $collection = $builder->build($config['routes']);
        
        return new Router($collection);
    }
}