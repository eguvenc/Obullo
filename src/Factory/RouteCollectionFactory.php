<?php

namespace Obullo\Factory;

use Interop\Container\ContainerInterface;
use Obullo\Router\RouteCollection;
use Obullo\Router\TranslatableRouteCollection;
use Laminas\ServiceManager\Factory\FactoryInterface;

class RouteCollectionFactory implements FactoryInterface
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
        $config = $options['config']['router'];
        
        if ($config['translatable_routes']) {
            return new TranslatableRouteCollection($config);
        }
        return new RouteCollection($config);
    }
}
