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
        $config = $options['config'];
    
        if ($config['router']['translatable_routes']) {
            return new TranslatableRouteCollection($options['pattern']);            
        }
        return new RouteCollection($options['pattern']);
    }
}