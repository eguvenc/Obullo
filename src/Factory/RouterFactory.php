<?php

namespace Obullo\Factory;

use Obullo\Router\RequestContext;
use Obullo\Router\Builder;
use Obullo\Router\Router;
use Obullo\Router\RouteCollectionInterface;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

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
        $config = $container->get('Config');
        $context = new RequestContext;
        $context->fromRequest($container->get('Request'));

        $collection = $container->build(RouteCollectionInterface::class, ['config' => $config]);
        $collection->setContext($context);

        $builder = new Builder($collection);
        $collection = $builder->build($config['router']['routes']);
        
        return new Router($collection);
    }
}
