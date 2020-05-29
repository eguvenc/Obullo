<?php

namespace Obullo\Factory;

use ReflectionClass;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;

class LazyDefaultFactory extends ReflectionBasedAbstractFactory
{
    /**
     * Determine if we can create a service with name
     *
     * @param Container $container
     * @param $name
     * @param $requestedName
     *
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        $condition1 = Parent::canCreate($container, $requestedName);
        $condition2 = (strstr($requestedName, 'Pages\\') !== false OR strstr($requestedName, 'Middleware\\') !== false);

        return $condition1 && $condition2;
    }
}