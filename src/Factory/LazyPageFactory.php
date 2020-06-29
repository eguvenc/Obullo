<?php

namespace Obullo\Factory;

use ReflectionClass;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;

class LazyPageFactory extends ReflectionBasedAbstractFactory
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
        $condition2 = (strstr($requestedName, '\Pages\\') !== false);

        return $condition1 && $condition2;
    }
}
